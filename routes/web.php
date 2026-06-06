<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\GuestController;
use Illuminate\Support\Facades\Route;

// Public
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

// Admin
Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
Route::delete('/admin/guests/{guest}', [AdminController::class, 'deleteGuest'])->name('admin.guests.delete');
Route::post('/admin/draft', [AdminController::class, 'draftMessage'])->name('admin.draft');
Route::post('/admin/send-emails', [AdminController::class, 'sendEmails'])->name('admin.send-emails');

