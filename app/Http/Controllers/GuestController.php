<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;

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

        Guest::create($validated);

        $partyDate = $this->nextPartyDate()->format('F j, Y');

        return response()->json([
            'message' => "The next Underground Mic Karaoke Party will be on {$partyDate}.",
        ]);
    }

    private function nextPartyDate(): \DateTime
    {
        $now = new \DateTime('now', new \DateTimeZone('America/Los_Angeles'));
        $year = (int) $now->format('Y');
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
        $lastDay = new \DateTime("last day of {$year}-{$month}", new \DateTimeZone('America/Los_Angeles'));
        $dow = (int) $lastDay->format('w'); // 0=Sun 6=Sat
        $offset = ($dow >= 6) ? 0 : $dow + 1;
        $lastDay->modify("-{$offset} days");
        return $lastDay;
    }
}

