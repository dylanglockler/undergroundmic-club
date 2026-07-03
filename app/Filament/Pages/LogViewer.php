<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class LogViewer extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.log-viewer';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Logs';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Application Logs';

    public array $data = [];

    /** Only read the last chunk of the file so a huge log never blows memory. */
    protected int $maxBytes = 512_000;

    public function mount(): void
    {
        $this->data = [
            'file'  => $this->availableFiles()->keys()->first(),
            'level' => 'all',
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Select::make('file')
                    ->label('Log file')
                    ->options($this->availableFiles())
                    ->native(false)
                    ->live(),

                Select::make('level')
                    ->label('Level')
                    ->options([
                        'all'       => 'All levels',
                        'emergency' => 'Emergency',
                        'alert'     => 'Alert',
                        'critical'  => 'Critical',
                        'error'     => 'Error',
                        'warning'   => 'Warning',
                        'notice'    => 'Notice',
                        'info'      => 'Info',
                        'debug'     => 'Debug',
                    ])
                    ->default('all')
                    ->native(false)
                    ->live(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            // Livewire re-renders after any action, so a no-op re-reads the log.
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => null),

            Action::make('clear')
                ->label('Clear file')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('This empties the selected log file on the server. It cannot be undone.')
                ->action(function () {
                    $file = $this->data['file'] ?? null;
                    $path = $file ? storage_path('logs/' . basename($file)) : null;

                    if ($path && is_file($path)) {
                        File::put($path, '');
                        Notification::make()->title("Cleared {$file}")->success()->send();
                    }
                }),
        ];
    }

    /**
     * Parsed log entries, newest first, filtered by the selected level.
     *
     * @return array<int,array{level:string,timestamp:string,message:string}>
     */
    public function getEntries(): array
    {
        $file = $this->data['file'] ?? null;
        if (! $file) {
            return [];
        }

        $path = storage_path('logs/' . basename($file));
        if (! is_file($path)) {
            return [];
        }

        $content = $this->tail($path, $this->maxBytes);

        // Matches Laravel's line format: [2024-01-01 12:00:00] production.ERROR:
        $pattern = '/^\[(\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}[^\]]*)\]\s+[\w.-]+\.(\w+):/m';

        preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        $entries = [];
        $count   = count($matches);

        for ($i = 0; $i < $count; $i++) {
            $start = $matches[$i][0][1];
            $end   = $i + 1 < $count ? $matches[$i + 1][0][1] : strlen($content);
            $raw   = substr($content, $start, $end - $start);

            $entries[] = [
                'timestamp' => $matches[$i][1][0],
                'level'     => strtolower($matches[$i][2][0]),
                // Drop the "[date] env.LEVEL:" prefix, keep the message + trace.
                'message'   => trim(preg_replace($pattern, '', $raw, 1)),
            ];
        }

        $entries = array_reverse($entries);

        $filter = $this->data['level'] ?? 'all';
        if ($filter !== 'all') {
            $entries = array_values(array_filter($entries, fn ($e) => $e['level'] === $filter));
        }

        return $entries;
    }

    /** filename => human label, newest file first. */
    protected function availableFiles(): Collection
    {
        $dir = storage_path('logs');
        if (! is_dir($dir)) {
            return collect();
        }

        return collect(File::files($dir))
            ->filter(fn ($f) => $f->getExtension() === 'log')
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->mapWithKeys(fn ($f) => [
                $f->getFilename() => $f->getFilename() . ' (' . $this->humanBytes($f->getSize()) . ')',
            ]);
    }

    /** Read at most $bytes from the end of the file, dropping a partial first line. */
    protected function tail(string $path, int $bytes): string
    {
        $size   = filesize($path);
        $handle = fopen($path, 'rb');

        if ($size > $bytes) {
            fseek($handle, -$bytes, SEEK_END);
            fgets($handle); // discard the partial line we landed in the middle of
        }

        $data = stream_get_contents($handle);
        fclose($handle);

        return $data ?: '';
    }

    protected function humanBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i     = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }
}
