<?php

namespace App\Filament\Pages;

use App\Mail\PartyReminderMail;
use App\Models\Guest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class Blast extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.blast';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $navigationLabel = 'Send Blast';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Send a Blast';

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'type'    => 'email',
            'subject' => '',
            'body'    => '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Select::make('type')
                    ->label('Blast Type')
                    ->options(['email' => 'Email', 'text' => 'Text / SMS'])
                    ->default('email')
                    ->live()
                    ->required(),

                TextInput::make('subject')
                    ->label('Subject')
                    ->placeholder('The Underground Mic — Party Reminder')
                    ->visible(fn () => ($this->data['type'] ?? 'email') === 'email')
                    ->required(fn () => ($this->data['type'] ?? 'email') === 'email'),

                Textarea::make('body')
                    ->label('Message')
                    ->rows(fn () => ($this->data['type'] ?? 'email') === 'text' ? 3 : 8)
                    ->maxLength(fn () => ($this->data['type'] ?? 'email') === 'text' ? 160 : null)
                    ->hint(fn () => ($this->data['type'] ?? 'email') === 'text' ? 'Max 160 characters for SMS' : null)
                    ->required(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('draftWithAi')
                ->label('Draft with AI')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->action(function () {
                    $type   = $this->data['type'] ?? 'email';
                    $result = $this->callClaude($type);

                    if (isset($result['error'])) {
                        Notification::make()->title($result['error'])->danger()->send();
                        return;
                    }

                    $this->data['body'] = $result['body'];
                    if ($type === 'email' && ! empty($result['subject'])) {
                        $this->data['subject'] = $result['subject'];
                    }

                    Notification::make()->title('Draft ready!')->success()->send();
                }),

            Action::make('send')
                ->label('Send')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading(fn () => 'Send ' . (($this->data['type'] ?? 'email') === 'text' ? 'SMS reminder' : 'email blast') . '?')
                ->modalDescription(fn () => $this->recipientCount() . ' recipient(s) will receive this message.')
                ->action(function () {
                    $type = $this->data['type'] ?? 'email';
                    if ($type === 'email') {
                        $this->sendEmails();
                    } else {
                        $this->openSmsApp();
                    }
                }),
        ];
    }

    private function recipientCount(): int
    {
        return ($this->data['type'] ?? 'email') === 'email'
            ? Guest::whereIn('method', ['email', 'calendar'])->count()
            : Guest::whereNotNull('phone')->where('phone', '!=', '')->count();
    }

    private function sendEmails(): void
    {
        $guests = Guest::whereIn('method', ['email', 'calendar'])->get();

        if ($guests->isEmpty()) {
            Notification::make()->title('No email recipients found.')->warning()->send();
            return;
        }

        $sent = 0; $failed = 0;
        foreach ($guests as $guest) {
            try {
                Mail::to($guest->contact)->send(
                    new PartyReminderMail($guest, $this->data['subject'] ?? '', $this->data['body'] ?? '')
                );
                $sent++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        $msg = "Sent {$sent} email(s)" . ($failed ? ", {$failed} failed." : '.');
        Notification::make()->title($msg)->success()->send();
    }

    private function openSmsApp(): void
    {
        $numbers = Guest::whereNotNull('phone')->where('phone', '!=', '')->pluck('phone')->join(',');
        $body    = rawurlencode($this->data['body'] ?? '');
        $this->redirect("sms:{$numbers}?&body={$body}");
    }

    private function callClaude(string $type): array
    {
        $apiKey = config('services.anthropic.key');
        if (! $apiKey) {
            return ['error' => 'ANTHROPIC_API_KEY not set.'];
        }

        $partyDate = $this->nextPartyDate()->format('F j, Y');

        $prompt = $type === 'text'
            ? "Write a short, fun SMS reminder (under 160 characters) for \"The Underground Mic\" — a monthly basement speakeasy karaoke party. Party is on {$partyDate} at 7PM. Speakeasy voice, playful. No emojis."
            : "Write a fun, witty email reminder for \"The Underground Mic\" — a monthly basement speakeasy karaoke party.\n\n"
                . "Next party: {$partyDate} at 7PM.\n\n"
                . "Short (4-6 sentences), speakeasy voice — playful, warm, a little cheeky. Encourage people to come, "
                . "mention bringing friends. One or two emojis max. Sign off as \"The Underground Mic\".\n\n"
                . "Format exactly:\n<subject>subject line here</subject>\n<email body>\nbody here\n</email body>";

        $response = Http::withHeaders([
            'x-api-key'         => $apiKey,
            'anthropic-version' => '2023-06-01',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => 'claude-sonnet-4-6',
            'max_tokens' => 600,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]);

        $text = $response->json('content.0.text') ?? '';

        if ($type === 'email') {
            preg_match('/<subject>(.*?)<\/subject>/s', $text, $sub);
            preg_match('/<email body>(.*?)<\/email body>/s', $text, $bod);
            return [
                'subject' => trim($sub[1] ?? ''),
                'body'    => trim($bod[1] ?? $text),
            ];
        }

        return ['body' => trim($text)];
    }

    private function nextPartyDate(): \Carbon\Carbon
    {
        $now   = now()->setTimezone('America/Chicago');
        $year  = $now->year;
        $month = $now->month;

        $lastSat = function (int $y, int $m) {
            $d = \Carbon\Carbon::create($y, $m)->endOfMonth()->setTimezone('America/Chicago');
            while ($d->dayOfWeek !== 6) $d->subDay();
            return $d;
        };

        $candidate = $lastSat($year, $month);
        if ($candidate->lte($now)) {
            $month++;
            if ($month > 12) { $month = 1; $year++; }
            $candidate = $lastSat($year, $month);
        }

        return $candidate;
    }
}
