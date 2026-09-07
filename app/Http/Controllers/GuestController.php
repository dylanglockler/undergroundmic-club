<?php

namespace App\Http\Controllers;

use App\Mail\SignupConfirmationMail;
use App\Models\Guest;
use App\Support\PartyCalendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GuestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'stage_name'    => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:20',
            'method'        => 'required|in:email,calendar,text',
            'contact'       => 'required|string|max:255',
            'reminder_time' => 'required|in:1week,1day,dayof',
        ]);

        $guest = Guest::create($validated);

        $partyDate = PartyCalendar::nextPartyDate()->format('F j, Y');

        if ($guest->method === 'email') {
            try {
                Mail::to($guest->contact)->send(new SignupConfirmationMail($guest));
            } catch (\Throwable $e) {
                Log::error('Signup confirmation email failed', ['guest_id' => $guest->id, 'error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'message' => "The next Underground Mic Karaoke Party will be on {$partyDate}.",
        ]);
    }
}

