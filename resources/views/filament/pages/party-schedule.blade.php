<x-filament-panels::page>
    <x-filament::section>
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem;">
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <x-filament::icon-button
                    icon="heroicon-o-chevron-left"
                    label="Previous month"
                    wire:click="previousMonth"
                />
                <h2 style="font-size:1.125rem; font-weight:600;">
                    {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                </h2>
                <x-filament::icon-button
                    icon="heroicon-o-chevron-right"
                    label="Next month"
                    wire:click="nextMonth"
                />
            </div>

            <x-filament::button color="gray" size="sm" wire:click="goToToday">
                Today
            </x-filament::button>
        </div>

        <div style="display:grid; grid-template-columns:repeat(7, minmax(0, 1fr)); gap:4px; text-align:center; font-size:0.75rem; font-weight:500; opacity:0.6; margin-bottom:4px;">
            <div>Sun</div>
            <div>Mon</div>
            <div>Tue</div>
            <div>Wed</div>
            <div>Thu</div>
            <div>Fri</div>
            <div>Sat</div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(7, minmax(0, 1fr)); gap:4px;">
            @foreach ($this->calendarWeeks as $week)
                @foreach ($week as $day)
                    @php
                        $isLastSaturday = $day && $day->isSameDay($this->lastSaturday);
                        $isCancelled = $isLastSaturday && $this->cancellation;

                        $cellStyle = 'aspect-ratio:1; display:flex; align-items:center; justify-content:center; border-radius:0.5rem; font-size:0.875rem;';

                        if (! $day) {
                            $cellStyle .= ' opacity:0.25;';
                        } elseif ($isCancelled) {
                            $cellStyle .= ' font-weight:700; box-shadow: inset 0 0 0 2px rgb(239 68 68); background-color: rgb(239 68 68 / 0.1); color: rgb(220 38 38);';
                        } elseif ($isLastSaturday) {
                            $cellStyle .= ' font-weight:700; box-shadow: inset 0 0 0 2px rgb(34 197 94); background-color: rgb(34 197 94 / 0.1); color: rgb(21 128 61);';
                        }
                    @endphp
                    <div style="{{ $cellStyle }}">
                        {{ $day?->day }}
                    </div>
                @endforeach
            @endforeach
        </div>
    </x-filament::section>

    <x-filament::section>
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
            <div>
                <p style="font-size:0.875rem; opacity:0.6;">
                    Party night for {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                </p>
                <p style="font-size:1.25rem; font-weight:600;">
                    {{ $this->lastSaturday->format('l, F j, Y') }}
                </p>

                <div style="margin-top:0.5rem;">
                    @if ($this->cancellation)
                        <x-filament::badge color="danger">
                            No party this month
                        </x-filament::badge>
                    @else
                        <x-filament::badge color="success">
                            Party happening 🎤
                        </x-filament::badge>
                    @endif
                </div>
            </div>

            <x-filament::button
                :color="$this->cancellation ? 'success' : 'danger'"
                wire:click="toggleCancellation"
            >
                {{ $this->cancellation ? 'Mark party back on' : 'Cancel this party' }}
            </x-filament::button>
        </div>

        <div style="margin-top:1rem;">
            <label style="font-size:0.875rem; font-weight:500;">
                Reason <span style="opacity:0.5; font-weight:400;">(optional, for hosts only)</span>
            </label>
            <div style="display:flex; gap:0.5rem; margin-top:0.25rem;">
                <input
                    type="text"
                    wire:model="reason"
                    placeholder="e.g. venue unavailable"
                    class="fi-input"
                    style="flex:1; border-radius:0.5rem; padding:0.375rem 0.75rem; font-size:0.875rem;"
                />
                <x-filament::button size="sm" color="gray" wire:click="saveReason">
                    Save
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    @if ($this->upcomingCancellations->isNotEmpty())
        <x-filament::section heading="Upcoming cancelled dates">
            <div style="display:flex; flex-direction:column; gap:0.5rem;">
                @foreach ($this->upcomingCancellations as $cancellation)
                    <div style="display:flex; align-items:center; justify-content:space-between; padding:0.5rem 0; border-bottom:1px solid rgba(127,127,127,0.15);">
                        <div>
                            <p style="font-weight:500;">
                                {{ $cancellation->date->format('l, F j, Y') }}
                            </p>
                            @if ($cancellation->reason)
                                <p style="font-size:0.75rem; opacity:0.6;">
                                    {{ $cancellation->reason }}
                                </p>
                            @endif
                        </div>
                        <x-filament::button size="sm" color="gray" wire:click="restore({{ $cancellation->id }})">
                            Restore
                        </x-filament::button>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
