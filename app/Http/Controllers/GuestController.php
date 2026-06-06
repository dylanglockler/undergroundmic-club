<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

        $confirmationMessage = $this->draftConfirmation(
            $validated['name'],
            $validated['method'],
            $validated['reminder_time']
        );

        return response()->json(['message' => $confirmationMessage]);
    }

    private function draftConfirmation(string $name, string $method, string $reminderTime): string
    {
        $apiKey = config('services.anthropic.key');
        if (! $apiKey) {
            return "You're on the list! See you at The Underground Mic. 🎤";
        }

        $partyDate = $this->nextPartyDate()->format('F j, Y');
        $reminderLabel = match ($reminderTime) {
            '1week' => '1 week before',
            '1day'  => '1 day before',
            'dayof' => 'the day of the party',
            default => $reminderTime,
        };

        $prompt = "You are a fun, witty assistant for \"The Underground Mic\" — a monthly basement speakeasy karaoke party.\n\n"
            . "A guest named {$name} has just signed up for a {$method} reminder for the next party on {$partyDate} at 7PM.\n"
            . "Their reminder preference: {$reminderLabel}.\n\n"
            . "Write them a short, warm, playful confirmation message (3-4 sentences) in the speakeasy/dive-bar-karaoke spirit. "
            . "Be fun, a little cheeky, encouraging. Reference the party name and date. End with a hype line about karaoke. "
            . "Don't use emojis excessively. Keep it tight and charming.";

        $response = Http::withHeaders([
            'x-api-key'         => $apiKey,
            'anthropic-version' => '2023-06-01',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => 'claude-sonnet-4-6',
            'max_tokens' => 300,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]);

        return $response->json('content.0.text')
            ?? "You're on the list! See you at The Underground Mic on {$partyDate}. 🎤";
    }

    private function nextPartyDate(): \DateTime
    {
        $now = new \DateTime('now', new \DateTimeZone('America/Chicago'));
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
        $lastDay = new \DateTime("last day of {$year}-{$month}", new \DateTimeZone('America/Chicago'));
        $dow = (int) $lastDay->format('w'); // 0=Sun 6=Sat
        $offset = ($dow >= 6) ? 0 : $dow + 1;
        $lastDay->modify("-{$offset} days");
        return $lastDay;
    }
}

