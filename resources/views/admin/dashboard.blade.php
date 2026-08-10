<x-admin-layout title="Dashboard">
    @php
        $hour     = (int) now()->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $dashTourSteps = [
            ['target' => 'dashboard-hero',  'title' => 'Command centre header',    'body' => 'Your personalised greeting shows the current date. Quick-action buttons here let you add guests or jump to the Admin Guide in one click.'],
            ['target' => 'dashboard-stats', 'title' => 'Key metrics at a glance',  'body' => 'Five live KPIs: total properties, active guests, pending IDs, today\'s check-ins, and today\'s check-outs — always up to date.'],
            ['target' => 'setup-checklist', 'title' => 'Setup checklist',          'body' => 'Track your onboarding progress. The bar fills as each task is completed. Green badges confirm readiness.'],
            ['target' => 'needs-attention', 'title' => 'Needs attention panel',    'body' => 'Smart reminders for the highest-friction operations — missing IDs, GPS approvals, and today\'s movements.'],
            ['target' => 'today-checkins',  'title' => "Today's check-ins",        'body' => 'All guests arriving today with their current status. Click any guest to view their full profile and take action.'],
            ['target' => 'today-checkouts', 'title' => "Today's check-outs",       'body' => 'Guests departing today. Track their checkout status and access their booking details in one click.'],
            ['target' => 'recent-guests',   'title' => 'Recent guests table',    'body' => 'The eight most recent guest bookings with property, stay dates, and current status. Click View All to see everything.'],
            ['target' => 'recent-activity', 'title' => 'Activity log widget',      'body' => 'A live feed of the most recent audit events. Every admin and guest action is captured. Click All Logs for the full audit trail.'],
        ];
    @endphp

    {{-- Hero command centre --}}
    <section class="mb-6 overflow-hidden rounded-lg border border-slate-200 bg-white p-6 shadow-xs sm:p-8" data-tour="dashboard-hero">
        <div class="grid gap-6 lg:grid-cols-[1fr_320px] lg:items-end">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">{{ now()->format('l, F j, Y') }}</p>
                <h1 class="mt-3 text-3xl font-semibold leading-tight text-slate-950 sm:text-4xl">{{ $greeting }}, {{ auth()->user()->name }}.</h1>
                <p class="mt-4 max-w-2xl text-slate-600">Review arrivals, pending approvals, setup progress, and recent activity from one clean workspace.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                <a href="{{ route('admin.guests.create') }}" class="btn-primary">Add Guest</a>
                <a href="{{ route('admin.guide') }}" class="btn-secondary">Open Admin Guide</a>
                <button type="button" id="start-dashboard-tour" class="btn-secondary text-sm">
                    ✦ Start Dashboard Tour
                </button>
            </div>
        </div>
    </section>

    {{-- Priority: next check-in per property --}}
    <section class="mb-6" data-tour="priority-section">
        <h2 class="mb-3 text-lg font-semibold text-slate-950">Priority</h2>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($priorityBookings as $entry)
                @php $booking = $entry['booking']; @endphp
                <div class="card card-pad">
                    <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ $entry['property']->name }}</p>
                    @if(! $booking)
                        <p class="mt-3 text-sm text-slate-500">No upcoming check-ins.</p>
                    @else
                        <p class="mt-2 {{ $entry['is_today'] ? 'font-bold text-slate-950' : 'text-slate-600' }}">
                            @if($booking->status === 'currently_hosting')
                                Currently hosting
                            @elseif($entry['is_today'])
                                Checking in today
                            @else
                                Checking in {{ now()->diffForHumans($booking->check_in_date, ['parts' => 1]) }}
                            @endif
                        </p>
                        <p class="mt-3 font-semibold text-slate-950">{{ $booking->guest_name }}</p>
                        <p class="text-sm text-slate-500">{{ $booking->phone }}</p>
                        <span class="badge badge-{{ $booking->status }} mt-2 inline-block">
                            @if($booking->status === 'currently_hosting')
                                Checkout: {{ $booking->check_out_date->format('M j, Y') }}
                            @else
                                {{ $booking->statusLabel() }}
                            @endif
                        </span>
                        @if(! $booking->isCheckedIn())
                            <div class="mt-3 text-sm text-slate-600">
                                @if(count($entry['requirements']))
                                    <ul class="list-disc space-y-1 pl-4">
                                        @foreach($entry['requirements'] as $req)
                                            <li>{{ $req }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>Guest is ready for check-in.</p>
                                @endif
                            </div>
                        @endif
                        <a href="{{ route('admin.guests.show', $booking) }}" class="mt-4 inline-block text-sm font-semibold text-teal-800">View details</a>
                    @endif
                </div>
            @empty
                <div class="card card-pad">
                    <p class="text-sm text-slate-500">No properties configured yet.</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- Stats cards --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5" data-tour="dashboard-stats">
        @foreach([
            ['properties',            'Properties',        $totalProperties, 'Total units configured'],
            ['guests',                'Active guests',     $activeGuests,    'In the arrival window'],
            ['upload',                'Pending IDs',       $pendingIds,      'Awaiting document upload'],
            ['calendar',              "Today's check-ins", $todayCheckins,   'Arrivals today'],
            ['checkout-instructions', "Today's check-outs",$todayCheckouts,  'Departures today'],
        ] as [$icon, $label, $value, $copy])
            <div class="card card-pad" data-animate>
                <div class="flex items-start justify-between gap-4">
                    <span class="icon-chip"><x-icon :name="$icon" /></span>
                    <span class="text-3xl font-bold text-slate-950">{{ $value }}</span>
                </div>
                <p class="mt-4 font-semibold text-slate-950">{{ $label }}</p>
                <p class="mt-1 text-sm leading-6 text-slate-500">{{ $copy }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_380px]">
        {{-- Setup checklist --}}
        <section class="card card-pad" data-tour="setup-checklist">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="section-title">Setup checklist</h2>
                    <p class="section-copy">Complete these steps before presenting the portal to guests.</p>
                </div>
                <span class="text-2xl font-bold text-slate-950">{{ $checklistPercent }}%</span>
            </div>
            <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                <div class="h-2 rounded-full bg-blue-600 transition-all duration-300"
                     style="width: {{ $checklistPercent }}%"></div>
            </div>
            @if($checklistPercent === 100)
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-800">
                    <x-icon name="check" class="mr-2 inline h-4 w-4" />Your guest portal is fully configured and ready.
                </div>
            @endif
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                @foreach($checklist as $item)
                    <a href="{{ $item['route'] }}"
                       class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 transition hover:bg-slate-50 hover:shadow-sm">
                        <span class="icon-chip h-9 w-9 {{ $item['done'] ? 'border-emerald-200 bg-emerald-50 text-emerald-600' : '' }}">
                            <x-icon :name="$item['icon']" class="h-4 w-4" />
                        </span>
                        <span class="min-w-0 flex-1 text-sm font-semibold text-slate-700">{{ $item['label'] }}</span>
                        <span class="badge {{ $item['done'] ? 'badge-active' : 'badge-pending' }}">
                            {{ $item['done'] ? 'Done' : 'Open' }}
                        </span>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Needs attention --}}
        <aside class="card card-pad" data-tour="needs-attention">
            <h2 class="section-title">Needs attention</h2>
            <p class="section-copy">Highest-friction operational reminders.</p>
            <div class="mt-5 grid gap-3">
                @foreach([
                    ['upload',                'Missing photo ID',       $pendingIds,         route('admin.guests.index', ['status' => 'pending'])],
                    ['calendar',              'Checking in today',      $todayCheckins,       route('admin.guests.index')],
                    ['map',                   'GPS approval needed',    $gpsApprovalNeeded,   route('admin.guests.index')],
                    ['parking',               'Parking info missing',   $missingParking,      route('admin.guests.index')],
                    ['checkout-instructions', 'Checking out today',     $todayCheckouts,      route('admin.guests.index')],
                ] as [$icon, $label, $count, $url])
                    <a href="{{ $url }}"
                       class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 transition hover:bg-slate-50 hover:shadow-sm">
                        <span class="icon-chip h-10 w-10 {{ $count > 0 ? 'border-amber-200 bg-amber-50 text-amber-700' : '' }}">
                            <x-icon :name="$icon" class="h-4 w-4" />
                        </span>
                        <span class="flex-1 text-sm font-semibold text-slate-700">{{ $label }}</span>
                        <span class="badge {{ $count ? 'badge-pending' : 'badge-active' }}">{{ $count }}</span>
                    </a>
                @endforeach
            </div>
        </aside>
    </div>

    {{-- Today's check-ins / check-outs --}}
    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="card" data-tour="today-checkins">
            <div class="border-b border-slate-200 p-5">
                <h2 class="section-title">Today's check-ins</h2>
                <p class="section-copy">Guests scheduled to arrive today.</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($todayArrivals as $guest)
                    <a href="{{ route('admin.guests.show', $guest) }}"
                       class="flex items-center gap-4 p-4 hover:bg-slate-50">
                        <span class="icon-chip"><x-icon name="guests" /></span>
                        <span class="flex-1">
                            <span class="font-semibold text-slate-950">{{ $guest->guest_name }}</span>
                            <span class="block text-sm text-slate-500">{{ $guest->property->name }}</span>
                        </span>
                        <span class="badge badge-{{ $guest->status }}">{{ $guest->statusLabel() }}</span>
                    </a>
                @empty
                    <div class="py-10 text-center">
                        <x-icon name="calendar" class="mx-auto mb-3 h-8 w-8 text-[#b08a45]" />
                        <p class="text-sm text-slate-500">No arrivals scheduled today.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="card" data-tour="today-checkouts">
            <div class="border-b border-slate-200 p-5">
                <h2 class="section-title">Today's check-outs</h2>
                <p class="section-copy">Guests scheduled to depart today.</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($todayDepartures as $guest)
                    <a href="{{ route('admin.guests.show', $guest) }}"
                       class="flex items-center gap-4 p-4 hover:bg-slate-50">
                        <span class="icon-chip"><x-icon name="checkout-instructions" /></span>
                        <span class="flex-1">
                            <span class="font-semibold text-slate-950">{{ $guest->guest_name }}</span>
                            <span class="block text-sm text-slate-500">{{ $guest->property->name }}</span>
                        </span>
                        <span class="badge badge-{{ $guest->status }}">{{ $guest->statusLabel() }}</span>
                    </a>
                @empty
                    <div class="py-10 text-center">
                        <x-icon name="checkout-instructions" class="mx-auto mb-3 h-8 w-8 text-[#b08a45]" />
                        <p class="text-sm text-slate-500">No departures scheduled today.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
    <section class="mt-6 card" data-tour="lock-status">
        <div class="border-b border-slate-200 p-5">
            <h2 class="section-title">Smart lock status</h2>
            <p class="section-copy">Lock state and battery level for each property.</p>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($propertyLocks as $propertyName => $locks)
                <div class="p-4">
                    <p class="mb-3 text-sm font-semibold text-slate-950">{{ $propertyName }}</p>
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($locks as $lock)
                            <div class="flex items-center gap-3 rounded-lg border border-slate-200 p-3">
                                <span class="icon-chip"><x-icon name="lock" /></span>
                                <span class="flex-1">
                                    <span class="block font-semibold text-slate-950">{{ $lock->label }}</span>
                                    <span class="block text-sm text-slate-500">
                                        @if(is_null($lock->last_known_locked))
                                            Status unknown
                                        @else
                                            {{ $lock->last_known_locked ? 'Locked' : 'Unlocked' }}
                                        @endif
                                        &middot;
                                        @if(is_null($lock->battery_level))
                                            Battery unknown
                                        @else
                                            {{ $lock->battery_level }}% battery
                                        @endif
                                    </span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="py-10 text-center">
                    <x-icon name="lock" class="mx-auto mb-3 h-8 w-8 text-[#b08a45]" />
                    <p class="text-sm text-slate-500">No smart locks configured yet.</p>
                </div>
            @endforelse
        </div>
    </section>


    {{-- Recent guests + activity --}}
    <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_360px]">
        <section class="card" data-tour="recent-guests">
            <div class="flex items-center justify-between border-b border-slate-200 p-5">
                <div>
                    <h2 class="section-title">Recent guests</h2>
                    <p class="section-copy">Latest guest activity.</p>
                </div>
                <a href="{{ route('admin.guests.index') }}" class="btn-secondary">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Guest</th><th>Property</th><th>Stay</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($recentGuests as $guest)
                            <tr>
                                <td>
                                    <a class="font-semibold text-slate-950 hover:text-[#102338]"
                                       href="{{ route('admin.guests.show', $guest) }}">{{ $guest->guest_name }}</a>
                                    <div class="text-xs text-slate-500">{{ $guest->booking_id }}</div>
                                </td>
                                <td class="text-sm">{{ $guest->property->name }}</td>
                                <td class="text-sm text-slate-600">{{ $guest->stayRangeLabel() }}</td>
                                <td><span class="badge badge-{{ $guest->status }}">{{ $guest->statusLabel() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-12 text-center text-slate-500">No guests yet. Add the first booking to generate a guest URL.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="card card-pad" data-tour="recent-activity">
            <div class="flex items-center justify-between">
                <h2 class="section-title">Recent activity</h2>
                <a href="{{ route('admin.logs.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900">All logs →</a>
            </div>
            <div class="mt-5 grid gap-4">
                @forelse($recentActivity as $activity)
                    <div class="flex gap-3">
                        <span class="icon-chip h-9 w-9 shrink-0">
                            <x-icon :name="$activity->icon ?: 'logs'" class="h-4 w-4" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-950">{{ \Illuminate\Support\Str::limit($activity->description, 60) }}</p>
                            <div class="mt-0.5 flex items-center gap-2">
                                <span class="text-xs text-slate-500">{{ $activity->created_at->diffForHumans() }}</span>
                                <span class="badge {{ $activity->severityClass() }} py-0">{{ $activity->severity }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500">
                        <x-icon name="logs" class="mx-auto mb-3 h-8 w-8 text-[#b08a45]" />
                        Activity appears here as admins and guests use the portal.
                    </div>
                @endforelse
            </div>
        </aside>
    </div>

    {{-- Dashboard tour data (steps defined at top of file) --}}
    <div id="dashboard-tour-data"
         data-steps='@json($dashTourSteps)'
         data-complete-url="{{ route('admin.tour.dashboard.complete') }}"
         data-csrf="{{ csrf_token() }}"
         class="hidden">
    </div>
</x-admin-layout>
