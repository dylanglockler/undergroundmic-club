<?php

namespace App\Http\Controllers;

use App\Mail\PartyReminderMail;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    private function authorized(): bool
    {
        return Session::get('admin_authed') === true;
    }

    public function login(Request $request)
    {
        $password = $request->input('password');
        if ($password === config('app.admin_password')) {
            Session::put('admin_authed', true);
            return redirect()->route('admin.dashboard');
        }
        return back()->withErrors(['password' => 'Wrong password.']);
    }

    public function logout()
    {
        Session::forget('admin_authed');
        return redirect('/');
    }

    public function dashboard()
    {
        if (! $this->authorized()) {
            return view('admin.login');
        }

        $guests = Guest::orderByDesc('created_at')->get();
        return view('admin.dashboard', compact('guests'));
    }

    public function deleteGuest(Guest $guest)
    {
        if (! $this->authorized()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $guest->delete();
        return response()->json(['ok' => true]);
    }

    public function draftMessage(Request $request)
    {
        if (! $this->authorized()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $type = $request->input('type', 'email'); // email | text
        $partyDate = $this->nextPartyDate()->format('F j, Y');

        $prompt = $type === 'text'
            ? "Write a short, fun SMS text reminder (under 160 characters) for \"The Underground Mic\" — a monthly basement speakeasy karaoke party. Party is on {$partyDate} at 7PM. Speakeasy voice, playful. No emojis."
            : "You are writing a fun, witty email reminder for \"The Underground Mic\" — a monthly basement speakeasy karaoke party.\n\n"
                . "The next party is on {$partyDate} at 7PM.\n\n"
                . "Write a SHORT email reminder (4-6 sentences) with a fun, speakeasy-tinged voice — playful, warm, a little cheeky. "
                . "Encourage people to come, tease about songs they should dust off, mention bringing friends. "
                . "Don't overuse emojis (one or two max). Sign off as \"The Underground Mic\" or similar.\n\n"
                . "Also generate a catchy email subject line (under 50 chars).\n\n"
                . "Format your response exactly like this:\n"
                . "<subject>subject line here</subject>\n"
                . "<email body>\nemail body here\n</email body>";

        $apiKey = config('services.anthropic.key');
        if (! $apiKey) {
            return response()->json(['error' => 'Anthropic API key not configured.'], 500);
        }

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
            preg_match('/<subject>(.*?)<\/subject>/s', $text, $subjectMatch);
            preg_match('/<email body>(.*?)<\/email body>/s', $text, $bodyMatch);
            return response()->json([
                'subject' => trim($subjectMatch[1] ?? ''),
                'body'    => trim($bodyMatch[1] ?? $text),
            ]);
        }

        return response()->json(['body' => trim($text)]);
    }

    public function sendEmails(Request $request)
    {
        if (! $this->authorized()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $subject = $request->input('subject', 'The Underground Mic — Party Reminder');
        $body    = $request->input('body', '');

        $emailGuests = Guest::whereIn('method', ['email', 'calendar'])->get();
        if ($emailGuests->isEmpty()) {
            return response()->json(['error' => 'No email recipients.'], 422);
        }

        $sent = 0;
        $failed = 0;
        $lastError = '';

        foreach ($emailGuests as $guest) {
            try {
                Mail::to($guest->contact)->send(new PartyReminderMail($guest, $subject, $body));
                $sent++;
            } catch (\Exception $e) {
                $failed++;
                $lastError = $e->getMessage();
            }
        }

        return response()->json([
            'sent'   => $sent,
            'failed' => $failed,
            'error'  => $lastError,
        ]);
    }

    private function nextPartyDate(): \DateTime
    {
        $now = new \DateTime('now', new \DateTimeZone('America/Chicago'));
        $year  = (int) $now->format('Y');
        $month = (int) $now->format('n');

        $candidate = $this->lastSaturday($year, $month);
        if ($candidate <= $now) {
            $month++;
            if ($month > 12) { $month = 1; $year++; }
            $candidate = $this->lastSaturday($year, $month);
        }

        return $candidate;
    }

    private function lastSaturday(int $year, int $month): \DateTime
    {
        $lastDay = new \DateTime("last day of {$year}-{$month}", new \DateTimeZone('America/Chicago'));
        $dow = (int) $lastDay->format('w');
        $offset = ($dow >= 6) ? 0 : $dow + 1;
        $lastDay->modify("-{$offset} days");
        return $lastDay;
    }
}

