<?php

use App\Http\Controllers\GuestController;
use App\Support\PartyCalendar;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $start = PartyCalendar::nextPartyDate();
    $end   = $start->copy()->setTime(23, 59, 0);
    $gcalDates = $start->format('Ymd\THis') . '/' . $end->format('Ymd\THis');
    $gcalUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
        . '&text=' . rawurlencode('The Underground Mic — Speakeasy Karaoke')
        . '&dates=' . $gcalDates
        . '&ctz=America/Los_Angeles'
        . '&recur=' . rawurlencode('RRULE:FREQ=MONTHLY;BYDAY=-1SA')
        . '&details=' . rawurlencode('Monthly basement speakeasy karaoke party. Members only. Last Saturday of every month, 7PM.');

    return view('home', [
        'nextParty'      => $start->format('F j, Y'),
        'gcalUrl'        => $gcalUrl,
        'partyTimestamp' => $start->timestamp,
    ]);
})->name('home');

Route::post('/guests', [GuestController::class, 'store'])->name('guests.store');

Route::get('/calendar.ics', function () {
    $tz = 'America/Los_Angeles';
    $now = now()->setTimezone($tz);

    $anchor = PartyCalendar::lastSaturdayOf($now->year, $now->month)->setTime(19, 0, 0);

    $dtstart = $anchor->format('Ymd\THis');
    $dtstamp = now()->utc()->format('Ymd\THis\Z');

    $exdates = \App\Models\PartyCancellation::whereDate('date', '>=', $now->toDateString())
        ->orderBy('date')
        ->pluck('date')
        ->map(fn ($date) => 'EXDATE;TZID=' . $tz . ':' . $date->copy()->setTime(19, 0, 0)->format('Ymd\THis') . "\r\n")
        ->implode('');

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
         . $exdates
         . "SUMMARY:The Underground Mic — Speakeasy Karaoke\r\n"
         . "DESCRIPTION:Monthly basement speakeasy karaoke party. Members only. Last Saturday of every month\\, 7PM.\r\n"
         . "END:VEVENT\r\n"
         . "END:VCALENDAR";

    return response($ics, 200, [
        'Content-Type'        => 'text/calendar; charset=utf-8',
        'Content-Disposition' => 'attachment; filename="underground-mic.ics"',
    ]);
})->name('calendar.ics');

