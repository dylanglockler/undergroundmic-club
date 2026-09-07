<?php

namespace App\Console\Commands;

use App\Support\PartyCalendar;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reminders:send')]
#[Description('Send party reminder emails to guests based on their reminder preference')]
class SendPartyReminders extends Command
{
    public function handle(): void
    {
        $partyDate = PartyCalendar::nextPartyDate();
        $now = now();

        $daysUntil = (int) $now->diffInDays($partyDate, false);

        $timingMap = [
            '1week' => 7,
            '1day'  => 1,
            'dayof' => 0,
        ];

        $toRemind = collect();
        foreach ($timingMap as $slot => $days) {
            if ($daysUntil === $days) {
                $toRemind = $toRemind->merge(
                    \App\Models\Guest::whereIn('method', ['email', 'calendar'])
                        ->where('reminder_time', $slot)
                        ->whereNull('reminded_at')
                        ->get()
                );
            }
        }

        if ($toRemind->isEmpty()) {
            $this->info('No reminders to send today.');
            return;
        }

        $subject = 'The Underground Mic — Party is ' . ($daysUntil === 0 ? 'TONIGHT' : "in {$daysUntil} day(s)") . '!';
        $body = "Hey {name},\n\nJust a reminder — The Underground Mic is happening on "
            . $partyDate->format('F j, Y')
            . " at 7PM.\n\nSee you there!\n\n— The Underground Mic";

        foreach ($toRemind as $guest) {
            try {
                \Illuminate\Support\Facades\Mail::to($guest->contact)
                    ->send(new \App\Mail\PartyReminderMail($guest, $subject, $body));
                $guest->update(['reminded_at' => now()]);
                $this->line("Sent to {$guest->contact}");
            } catch (\Exception $e) {
                $this->error("Failed for {$guest->contact}: {$e->getMessage()}");
            }
        }

        $this->info("Done. Sent to {$toRemind->count()} guest(s).");
    }
}
