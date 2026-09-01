<x-admin-layout title="Dashboard">
    @php
        $hour     = (int) now()->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    @endphp
    @php
        $dashTourSteps = [
            ['target' => 'dashboard-hero',     'title' => 'Command centre header',   'body' => 'Your personalised greeting and quick actions to add a guest or open the Admin Guide.'],
            ['target' => 'dashboard-overview', 'title' => "Today's overview",        'body' => "A live snapshot of today's check-ins, check-outs, currently-hosting guests, and pending approvals."],
            ['target' => 'dashboard-stats',    'title' => 'Key metrics at a glance', 'body' => "Five live KPIs: total properties, active guests, pending IDs, today's check-ins, and today's check-outs."],
            ['target' => 'priority-today',     'title' => 'Priority today',          'body' => 'Every property, one card each, showing who is currently hosting and who is arriving next.'],
            ['target' => 'today-schedule',     'title' => "Today's schedule",        'body' => "All of today's arrivals and departures in one list."],
            ['target' => 'needs-attention',    'title' => 'Needs attention',         'body' => 'Smart reminders for missing IDs, pending approvals, GPS verification, and parking info.'],
            ['target' => 'lock-status',        'title' => 'Smart lock status',       'body' => 'Locked/unlocked state and battery level for every smart lock across your properties.'],
            ['target' => 'setup-checklist',    'title' => 'Setup checklist',         'body' => 'Track your onboarding progress toward getting GuestHub fully configured.'],
            ['target' => 'recent-guests',      'title' => 'Recent guests',           'body' => 'The most recent guest bookings with property, stay dates, and current status.'],
            ['target' => 'recent-activity',    'title' => 'Recent activity',         'body' => 'A live feed of the most recent audit events across your account.'],
        ];
    @endphp

    {{-- Header row: greeting + actions + today's overview --}}
    <div class="mb-6 grid gap-4 xl:grid-cols-[520px_1fr]">
        <div class="card card-pad flex flex-col justify-center" data-tour="dashboard-hero">
            <h1 class="text-xl font-semibold text-slate-950">{{ $greeting }}, {{ auth()->user()->name }} 👋</h1>
            <p class="mt-1 text-sm text-slate-500">Here's what's happening across your properties today.</p>
            <div class="mt-3 flex flex-wrap gap-3">
                <a href="{{ route('admin.guests.create') }}" class="btn-primary">Add Guest</a>
                <a href="{{ route('admin.guide') }}" class="btn-secondary">Admin Guide</a>
                <button type="button" id="start-dashboard-tour" class="btn-secondary text-sm">✦ Start Dashboard Tour</button>
            </div>
        </div>
        <div class="card card-pad" data-tour="dashboard-overview">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-950">Today's Overview</p>
                <span class="badge border-emerald-200 bg-emerald-50 text-emerald-700">&bull; Live</span>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="flex items-center gap-3">
                    <span class="icon-chip h-9 w-9 shrink-0 border-emerald-200 bg-emerald-50 text-emerald-700"><x-icon name="calendar" class="h-4 w-4" /></span>
                    <span>
                        <span class="block text-lg font-bold leading-tight text-slate-950">{{ $todayCheckins }}</span>
                        <span class="block text-xs text-slate-500">Check-ins</span>
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="icon-chip h-9 w-9 shrink-0 border-amber-200 bg-amber-50 text-amber-700"><x-icon name="checkout-instructions" class="h-4 w-4" /></span>
                    <span>
                        <span class="block text-lg font-bold leading-tight text-slate-950">{{ $todayCheckouts }}</span>
                        <span class="block text-xs text-slate-500">Check-outs</span>
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="icon-chip h-9 w-9 shrink-0 border-blue-200 bg-blue-50 text-blue-700"><x-icon name="guests" class="h-4 w-4" /></span>
                    <span>
                        <span class="block text-lg font-bold leading-tight text-slate-950">{{ $activeGuests }}</span>
                        <span class="block text-xs text-slate-500">Currently Hosting</span>
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="icon-chip h-9 w-9 shrink-0 border-red-200 bg-red-50 text-red-700"><x-icon name="security" class="h-4 w-4" /></span>
                    <span>
                        <span class="block text-lg font-bold leading-tight text-slate-950">{{ $idsPendingApproval }}</span>
                        <span class="block text-xs text-slate-500">Pending Approvals</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat strip --}}
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5" data-tour="dashboard-stats">
        @foreach([
            ['properties',            'Properties',        $totalProperties, 'Total properties'],
            ['guests',                'Active Guests',     $activeGuests,    'Currently hosting'],
            ['upload',                'Pending IDs',       $pendingIds,      'Awaiting upload'],
            ['calendar',              "Today's Check-ins", $todayCheckins,   'Arriving today'],
            ['checkout-instructions', "Today's Check-outs",$todayCheckouts,  'Departing today'],
        ] as [$icon, $label, $value, $copy])
            <div class="card p-3">
                <div class="flex items-center gap-3">
                    <span class="icon-chip h-8 w-8"><x-icon :name="$icon" class="h-4 w-4" /></span>
                    <span class="text-xl font-bold text-slate-950">{{ $value }}</span>
                </div>
                <p class="mt-2 text-sm font-semibold text-slate-950">{{ $label }}</p>
                <p class="text-xs text-slate-500">{{ $copy }}</p>
            </div>
        @endforeach
    </div>

    {{-- Priority Today: full width --}}
    <section class="mt-6 card card-pad" data-tour="priority-today">
        <h2 class="section-title">Priority Today</h2>
        <p class="section-copy">What needs your attention first, by property.</p>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($priorityBookings as $entry)
                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $entry['property']->name }}</p>
                    @forelse($entry['entries'] as $item)
                        @php $booking = $item['booking']; @endphp
                        <a href="{{ route('admin.guests.show', $booking) }}" class="mt-3 flex flex-wrap items-start gap-x-3 gap-y-2 hover:bg-slate-50 {{ ! $loop->first ? 'border-t border-slate-100 pt-3' : '' }}">
                            <img src="{{ $entry['property']->heroImageUrl() }}" alt="" class="h-8 w-12 shrink-0 rounded-lg object-cover">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-slate-950">{{ $booking->guest_name }}</p>
                                <p class="text-xs font-medium text-slate-600">{{ $booking->formatted_phone }}</p>
                                <p class="text-xs {{ $item['is_today'] ? 'font-bold text-slate-950' : 'text-slate-500' }}">
                                    @if($item['kind'] === 'current')
                                        Currently hosting &middot; out {{ $booking->check_out_date->format('M j') }} {{ $booking->nightsLabel() }}
                                    @elseif($item['is_today'])
                                        Arriving today
                                    @else
                                        Arriving {{ now()->diffForHumans($booking->check_in_date, ['parts' => 1]) }}
                                    @endif
                                </p>
                                @if(count($item['requirements']))
                                    <p class="mt-1 truncate text-xs text-amber-700">{{ $item['requirements'][0] }}</p>
                                @elseif($item['kind'] === 'upcoming' && ! $booking->isCheckedIn())
                                    <p class="mt-1 text-xs text-emerald-700">Ready for check-in</p>
                                @endif
                            </div>
                            <span class="badge badge-{{ $booking->status }} shrink-0">{{ $booking->statusLabel() }}</span>
                        </a>
                    @empty
                        <p class="mt-3 text-sm text-slate-500">No upcoming check-ins.</p>
                    @endforelse
                </div>
            @endforeach
        </div>
    </section>

    {{-- Today's Schedule + Needs Attention + Smart Lock Status --}}
    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <section class="card" data-tour="today-schedule">
            <div class="border-b border-slate-200 p-5">
                <h2 class="section-title">Today's Schedule</h2>
                <p class="section-copy">All check-ins and check-outs.</p>
            </div>
            <div class="divide-y divide-slate-100">
                <p class="p-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Check-ins ({{ $todayArrivals->count() }})</p>
                @forelse($todayArrivals as $guest)
                    <a href="{{ route('admin.guests.show', $guest) }}" class="flex items-center justify-between gap-3 p-3 hover:bg-slate-50">
                        <span class="min-w-0">
                            <span class="block truncate font-semibold text-slate-950">{{ $guest->guest_name }}</span>
                            <span class="block truncate text-xs text-slate-500">{{ $guest->property->name }}</span>
                        </span>
                        <span class="badge badge-{{ $guest->status }} shrink-0">{{ $guest->statusLabel() }}</span>
                    </a>
                @empty
                    <p class="p-3 text-sm text-slate-500">None today.</p>
                @endforelse
                <p class="p-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Check-outs ({{ $todayDepartures->count() }})</p>
                @forelse($todayDepartures as $guest)
                    <a href="{{ route('admin.guests.show', $guest) }}" class="flex items-center justify-between gap-3 p-3 hover:bg-slate-50">
                        <span class="min-w-0">
                            <span class="block truncate font-semibold text-slate-950">{{ $guest->guest_name }}</span>
                            <span class="block truncate text-xs text-slate-500">{{ $guest->property->name }}</span>
                        </span>
                        <span class="badge badge-{{ $guest->status }} shrink-0">{{ $guest->statusLabel() }}</span>
                    </a>
                @empty
                    <p class="p-3 text-sm text-slate-500">None today.</p>
                @endforelse
            </div>
        </section>

        <section class="card card-pad" data-tour="needs-attention">
            <div class="flex items-center gap-2">
                <h2 class="section-title">Needs Attention</h2>
                @php $needsTotal = $pendingIds + $idsPendingApproval + $gpsApprovalNeeded + $missingParking; @endphp
                @if($needsTotal > 0)
                    <span class="badge border-red-200 bg-red-50 text-red-700">{{ $needsTotal }}</span>
                @endif
            </div>
            <p class="section-copy">High priority operational reminders.</p>
            <div class="mt-4 flex flex-col gap-2">
                @foreach([
                    ['upload', 'Missing photo IDs', $pendingIds],
                    ['security', 'IDs pending approval', $idsPendingApproval],
                    ['map', 'GPS approvals needed', $gpsApprovalNeeded],
                    ['parking', 'Parking info missing', $missingParking],
                ] as [$icon, $label, $count])
                    <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 p-2.5">
                        <span class="flex items-center gap-2 text-sm text-slate-700"><x-icon :name="$icon" class="h-4 w-4 text-slate-400" />{{ $label }}</span>
                        <span class="text-sm font-semibold text-slate-950">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="card card-pad" data-tour="lock-status">
            <h2 class="section-title">Smart Lock Status</h2>
            <p class="section-copy">All properties.</p>
            <div class="mt-4 flex flex-col gap-3">
                @forelse($propertyLocks as $propertyName => $locks)
                    @foreach($locks as $lock)
                        <div class="flex items-center gap-3 rounded-lg border border-slate-200 p-3">
                            <span class="icon-chip h-8 w-8 shrink-0 {{ $lock->last_known_locked ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                <x-icon name="lock" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-xs text-slate-500">{{ $propertyName }}</p>
                                <p class="truncate font-semibold text-slate-950">{{ $lock->label }}</p>
                                <p class="text-xs text-slate-500">
                                    @if(is_null($lock->last_known_locked))
                                        Status unknown
                                    @else
                                        {{ $lock->last_known_locked ? 'Locked' : 'Unlocked' }}
                                    @endif
                                    &middot;
                                    @if(is_null($lock->battery_level))
                                        Battery unknown
                                    @else
                                        {{ $lock->battery_level }}% Battery
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                @empty
                    <p class="py-6 text-center text-sm text-slate-500">No smart locks configured yet.</p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Setup Checklist + Recent Guests + Recent Activity --}}
    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <section class="card card-pad" data-tour="setup-checklist">
            <h2 class="section-title">Setup Checklist</h2>
            <p class="section-copy">{{ $checklistPercent }}% complete.</p>
            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full bg-blue-700" style="width: {{ $checklistPercent }}%"></div>
            </div>
            <div class="mt-4 flex flex-col gap-2">
                @foreach($checklist as $item)
                    <a href="{{ $item['route'] }}" class="flex items-center gap-2 text-sm hover:underline">
                        <x-icon :name="$item['done'] ? 'check' : $item['icon']" class="h-4 w-4 shrink-0 {{ $item['done'] ? 'text-emerald-600' : 'text-slate-400' }}" />
                        <span class="{{ $item['done'] ? 'text-slate-500 line-through' : 'text-slate-950' }}">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="card" data-tour="recent-guests">
            <div class="flex items-center justify-between border-b border-slate-200 p-5">
                <div>
                    <h2 class="section-title">Recent Guests</h2>
                    <p class="section-copy">Latest guest activity.</p>
                </div>
                <a href="{{ route('admin.guests.index') }}" class="text-sm font-semibold text-teal-800">View all</a>
            </div>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Guest</th>
                            <th>Property</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentGuests as $guest)
                            <tr onclick="window.location='{{ route('admin.guests.show', $guest) }}'" class="cursor-pointer">
                                <td class="font-semibold text-slate-950">{{ $guest->guest_name }}</td>
                                <td>{{ $guest->property->name }}</td>
                                <td><span class="badge badge-{{ $guest->status }}">{{ $guest->statusLabel() }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="card card-pad" data-tour="recent-activity">
            <div class="flex items-center justify-between">
                <h2 class="section-title">Recent Activity</h2>
                <a href="{{ route('admin.logs.index') }}" class="text-sm font-semibold text-teal-800">View all</a>
            </div>
            <p class="section-copy">Live feed of what's happening.</p>
            <div class="mt-4 flex flex-col gap-4">
                @foreach($recentActivity as $log)
                    <div class="flex items-start gap-3">
                        <span class="icon-chip h-8 w-8 shrink-0"><x-icon name="security" class="h-4 w-4" /></span>
                        <div class="min-w-0">
                            <p class="text-sm text-slate-950">{{ $log->description }}</p>
                            <p class="text-xs text-slate-500">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </aside>
    </div>
    <div id="dashboard-tour-data" data-steps="{{ json_encode($dashTourSteps) }}" data-complete-url="{{ route('admin.tour.dashboard.complete') }}" data-csrf="{{ csrf_token() }}" class="hidden"></div>
</x-admin-layout>
