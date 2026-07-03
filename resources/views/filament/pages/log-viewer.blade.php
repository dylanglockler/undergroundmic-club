<x-filament-panels::page>
    {{ $this->content }}

    @php($entries = $this->getEntries())
    @php($colors = [
        'emergency' => 'danger', 'alert' => 'danger', 'critical' => 'danger', 'error' => 'danger',
        'warning'   => 'warning', 'notice' => 'info', 'info' => 'info', 'debug' => 'gray',
    ])

    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        @if (empty($entries))
            <div class="p-6 text-sm text-gray-500 dark:text-gray-400">
                No log entries found for this file and level.
            </div>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($entries as $entry)
                    <li class="p-4">
                        <div class="flex items-center gap-3">
                            <x-filament::badge :color="$colors[$entry['level']] ?? 'gray'">
                                {{ strtoupper($entry['level']) }}
                            </x-filament::badge>
                            <span class="font-mono text-xs text-gray-500 dark:text-gray-400">
                                {{ $entry['timestamp'] }}
                            </span>
                        </div>
                        <pre class="mt-2 overflow-x-auto whitespace-pre-wrap break-words font-mono text-xs leading-relaxed text-gray-700 dark:text-gray-300">{{ $entry['message'] }}</pre>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-filament-panels::page>
