<?php

namespace Database\Seeders;

use App\Models\Guest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class KvImportSeeder extends Seeder
{
    public function run(): void
    {
        $response = Http::get('https://underground-mic-api.dylan-73a.workers.dev/api/guests');

        if (! $response->successful()) {
            $this->command->error('Failed to fetch guests from Cloudflare KV.');
            return;
        }

        $kvGuests = $response->json();
        $imported = 0;
        $skipped  = 0;

        foreach ($kvGuests as $g) {
            // Skip if already imported (match on contact + method)
            if (Guest::where('contact', $g['contact'])->where('method', $g['method'])->exists()) {
                $skipped++;
                continue;
            }

            Guest::create([
                'name'          => $g['name']         ?? '',
                'stage_name'    => $g['stageName']     ?? null,
                'phone'         => $g['phone']         ?? null,
                'method'        => $g['method']        ?? 'email',
                'contact'       => $g['contact']       ?? '',
                'reminder_time' => $g['reminderTime']  ?? '1day',
                'created_at'    => isset($g['signedUpAt']) ? \Carbon\Carbon::parse($g['signedUpAt']) : now(),
                'updated_at'    => isset($g['signedUpAt']) ? \Carbon\Carbon::parse($g['signedUpAt']) : now(),
            ]);

            $imported++;
        }

        $this->command->info("Imported {$imported} guest(s), skipped {$skipped} duplicate(s).");
    }
}

