<?php

use App\Http\Controllers\GuestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $now = now()->setTimezone('America/Chicago');
    $year = $now->year; $month = $now->month;
    $lastSat = function(int $y, int $m) {
        $d = \Carbon\Carbon::create($y, $m)->endOfMonth()->setTimezone('America/Chicago');
        while ($d->dayOfWeek !== 6) $d->subDay();
        return $d;
    };
    $next = $lastSat($year, $month);
    if ($next->lte($now)) {
        $month++; if ($month > 12) { $month = 1; $year++; }
        $next = $lastSat($year, $month);
    }
    $start = $next->copy()->setTime(19, 0, 0);
    $end   = $next->copy()->setTime(23, 59, 0);
    $gcalDates = $start->format('Ymd\THis') . '/' . $end->format('Ymd\THis');
    $gcalUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
        . '&text=' . rawurlencode('The Underground Mic — Speakeasy Karaoke')
        . '&dates=' . $gcalDates
        . '&ctz=America/Chicago'
        . '&recur=' . rawurlencode('RRULE:FREQ=MONTHLY;BYDAY=-1SA')
        . '&details=' . rawurlencode('Monthly basement speakeasy karaoke party. Members only. Last Saturday of every month, 7PM.');

    return view('home', [
        'nextParty'      => $next->format('F j, Y'),
        'gcalUrl'        => $gcalUrl,
        'partyTimestamp' => $start->timestamp,
    ]);
})->name('home');

Route::post('/guests', [GuestController::class, 'store'])->name('guests.store');

Route::get('/calendar.ics', function () {
    $tz = 'America/Chicago';
    $now = now()->setTimezone($tz);

    $anchor = \Carbon\Carbon::create($now->year, $now->month)->endOfMonth()->setTimezone($tz);
    while ($anchor->dayOfWeek !== 6) $anchor->subDay();
    $anchor->setTime(19, 0, 0);

    $dtstart = $anchor->format('Ymd\THis');
    $dtstamp = now()->utc()->format('Ymd\THis\Z');

    $ics = "BEGIN:VCALENDAR\r\n"
         . "VERSION:2.0\r\n"
         . "PRODID:-//The Underground Mic//EN\r\n"
         . "CALSCALE:GREGORIAN\r\n"
         . "METHOD:PUBLISH\r\n"
         . "X-WR-CALNAME:The Underground Mic\r\n"
         . "X-WR-TIMEZONE:{$tz}\r\n"
         . "BEGIN:VEVENT\r\n"
         . "UID:underground-mic-monthly@undergroundmic.club\r\n"
         . "DTSTAMP:{$dtstamp}\r\n"
         . "DTSTART;TZID={$tz}:{$dtstart}\r\n"
         . "DURATION:PT5H\r\n"
         . "RRULE:FREQ=MONTHLY;BYDAY=-1SA\r\n"
         . "SUMMARY:The Underground Mic — Speakeasy Karaoke\r\n"
         . "DESCRIPTION:Monthly basement speakeasy karaoke party. Members only. Last Saturday of every month\\, 7PM.\r\n"
         . "END:VEVENT\r\n"
         . "END:VCALENDAR";

    return response($ics, 200, [
        'Content-Type'        => 'text/calendar; charset=utf-8',
        'Content-Disposition' => 'attachment; filename="underground-mic.ics"',
    ]);
})->name('calendar.ics');

