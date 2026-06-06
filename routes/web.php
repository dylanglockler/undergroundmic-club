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
    return view('home', ['nextParty' => $next->format('F j, Y')]);
})->name('home');

Route::post('/guests', [GuestController::class, 'store'])->name('guests.store');

