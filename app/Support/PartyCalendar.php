<?php

namespace App\Support;

use App\Models\PartyCancellation;
use Carbon\Carbon;

class PartyCalendar
{
    const TIMEZONE = 'America/Los_Angeles';

    /**
     * The last Saturday of the given month, at 23:59:59, in the party timezone.
     */
    public static function lastSaturdayOf(int $year, int $month): Carbon
    {
        $date = Carbon::create($year, $month)->endOfMonth()->setTimezone(self::TIMEZONE);

        while ($date->dayOfWeek !== Carbon::SATURDAY) {
            $date->subDay();
        }

        return $date;
    }

    /**
     * Whether the given last-Saturday date has been marked as "no party" by a host.
     */
    public static function isCancelled(Carbon $date): bool
    {
        return PartyCancellation::whereDate('date', $date->toDateString())->exists();
    }

    /**
     * The next upcoming party date/time (7PM on the next non-cancelled last Saturday).
     */
    public static function nextPartyDate(?Carbon $from = null): Carbon
    {
        $now = ($from ?? now())->clone()->setTimezone(self::TIMEZONE);
        $year = $now->year;
        $month = $now->month;

        // Safety cap: look ahead at most 3 years so a run of cancelled months can't loop forever.
        for ($i = 0; $i < 36; $i++) {
            $candidate = self::lastSaturdayOf($year, $month);

            if ($candidate->gt($now) && ! self::isCancelled($candidate)) {
                return $candidate->copy()->setTime(19, 0, 0);
            }

            $month++;
            if ($month > 12) {
                $month = 1;
                $year++;
            }
        }

        throw new \RuntimeException('No upcoming (non-cancelled) party date found in the next 3 years.');
    }
}
