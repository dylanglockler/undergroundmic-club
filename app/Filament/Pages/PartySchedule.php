<?php

namespace App\Filament\Pages;

use App\Models\PartyCancellation;
use App\Support\PartyCalendar;
use BackedEnum;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PartySchedule extends Page
{
    protected string $view = 'filament.pages.party-schedule';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Party Calendar';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = 'Party Calendar';

    public int $year;

    public int $month;

    public string $reason = '';

    public function mount(): void
    {
        $now = now()->setTimezone(PartyCalendar::TIMEZONE);
        $this->year = $now->year;
        $this->month = $now->month;
        $this->loadReason();
    }

    public function previousMonth(): void
    {
        $this->month--;
        if ($this->month < 1) {
            $this->month = 12;
            $this->year--;
        }
        $this->loadReason();
    }

    public function nextMonth(): void
    {
        $this->month++;
        if ($this->month > 12) {
            $this->month = 1;
            $this->year++;
        }
        $this->loadReason();
    }

    public function goToToday(): void
    {
        $now = now()->setTimezone(PartyCalendar::TIMEZONE);
        $this->year = $now->year;
        $this->month = $now->month;
        $this->loadReason();
    }

    public function toggleCancellation(): void
    {
        $date = $this->lastSaturday();

        $existing = PartyCancellation::whereDate('date', $date->toDateString())->first();

        if ($existing) {
            $existing->delete();
            $this->reason = '';
            Notification::make()
                ->title($date->format('F j, Y') . ' is back on — party is happening.')
                ->success()
                ->send();
        } else {
            PartyCancellation::create([
                'date'   => $date->toDateString(),
                'reason' => $this->reason ?: null,
            ]);
            Notification::make()
                ->title($date->format('F j, Y') . ' marked as no party.')
                ->warning()
                ->send();
        }
    }

    public function saveReason(): void
    {
        $date = $this->lastSaturday();

        $existing = PartyCancellation::whereDate('date', $date->toDateString())->first();

        if ($existing) {
            $existing->update(['reason' => $this->reason ?: null]);
            Notification::make()->title('Reason saved.')->success()->send();
        }
    }

    public function restore(int $cancellationId): void
    {
        $cancellation = PartyCancellation::find($cancellationId);

        if ($cancellation) {
            $label = $cancellation->date->format('F j, Y');
            $cancellation->delete();
            $this->loadReason();
            Notification::make()->title("{$label} is back on — party is happening.")->success()->send();
        }
    }

    private function loadReason(): void
    {
        $date = $this->lastSaturday();
        $existing = PartyCancellation::whereDate('date', $date->toDateString())->first();
        $this->reason = $existing?->reason ?? '';
    }

    public function getLastSaturdayProperty(): Carbon
    {
        return $this->lastSaturday();
    }

    public function getCancellationProperty(): ?PartyCancellation
    {
        return PartyCancellation::whereDate('date', $this->lastSaturday()->toDateString())->first();
    }

    public function getCalendarWeeksProperty(): array
    {
        $first = Carbon::create($this->year, $this->month, 1);
        $daysInMonth = $first->daysInMonth;
        $startOffset = $first->dayOfWeek; // 0 (Sun) .. 6 (Sat)

        $cells = [];
        for ($i = 0; $i < $startOffset; $i++) {
            $cells[] = null;
        }
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $cells[] = $first->copy()->day($d);
        }
        while (count($cells) % 7 !== 0) {
            $cells[] = null;
        }

        return array_chunk($cells, 7);
    }

    public function getUpcomingCancellationsProperty()
    {
        return PartyCancellation::whereDate('date', '>=', now()->setTimezone(PartyCalendar::TIMEZONE)->toDateString())
            ->orderBy('date')
            ->get();
    }

    private function lastSaturday(): Carbon
    {
        return PartyCalendar::lastSaturdayOf($this->year, $this->month)->startOfDay();
    }
}
