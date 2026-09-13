<x-admin-layout title="Dashboard">
    @php
        $hour     = (int) now()->setTimezone(config('app.display_timezone'))->format('G');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    @endphp
    @php
        $dashTourSteps = [
            ['target' => 'dashboard-hero',     'title' => 'Your dashboard',    'body' => 'A quick greeting, one-tap Add Guest, and smart lock status at a glance.'],
            ['target' => 'dashboard-overview', 'title' => "Today's overview",  'body' => "Tap any figure to jump to today's guests below."],
            ['target' => 'guests-today',       'title' => 'Guests by property','body' => 'Every guest arriving or checking out today, plus upcoming arrivals, grouped by property.'],
            ['target' => 'recent-activity',    'title' => 'Recent activity',   'body' => 'A live feed of the latest events across your account.'],
        ];
    @endphp

    {{-- Greeting + quick add + smart lock status --}}
    <div class="card card-pad mb-5" data-tour="dashboard-hero">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-lg font-semibold text-slate-950">{{ $greeting }}, {{ auth()->user()->name }} 👋</h1>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.guests.create') }}" class="btn-primary gap-2"><x-icon name="plus" class="h-4 w-4" />Add Guest</a>
                <button type="button" id="start-dashboard-tour" class="btn-secondary text-sm">✦ Tour</button>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3">
            <span class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-slate-500"><x-icon name="lock" class="h-3.5 w-3.5 text-slate-400" />Smart Locks</span>
            @forelse($propertyLocks as $propertyName => $locks)
                @foreach($locks as $lock)
                    <span class="flex items-center gap-2 rounded-lg border border-slate-200 px-2.5 py-1 text-xs">
                        <span class="h-2 w-2 shrink-0 rounded-full {{ is_null($lock->last_known_locked) ? 'bg-slate-300' : ($lock->last_known_locked ? 'bg-emerald-500' : 'bg-red-500') }}"></span>
                        <span class="font-semibold text-slate-950">{{ $lock->label }}</span>
                        <span class="text-slate-500">
                            {{ $propertyName }} &middot;
                            {{ is_null($lock->last_known_locked) ? 'Unknown' : ($lock->last_known_locked ? 'Locked' : 'Unlocked') }}
                            @if(! is_null($lock->battery_level)) &middot; <span class="{{ $lock->battery_level <= 20 ? 'font-semibold text-red-600' : ($lock->battery_level <= 50 ? 'font-medium text-amber-600' : 'text-slate-500') }}">Battery {{ $lock->battery_level }}%</span>@endif
                        </span>
                    </span>
                @endforeach
            @empty
                <span class="text-xs text-slate-500">No smart locks configured.</span>
            @endforelse
        </div>
    </div>

    {{-- Today's overview (clickable) --}}
    <div class="card card-pad mb-5" data-tour="dashboard-overview">
        <p class="text-sm font-semibold text-slate-950">Today's Overview</p>
        <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <a href="#guests-today" class="flex items-center gap-3 rounded-lg transition hover:bg-slate-50">
                <span class="icon-chip h-9 w-9 shrink-0 border-emerald-200 bg-emerald-50 text-emerald-700"><x-icon name="calendar" class="h-4 w-4" /></span>
                <span>
                    <span class="block text-lg font-bold leading-tight text-slate-950">{{ $overview['checkins'] }}</span>
                    <span class="block text-xs text-slate-500">Check-ins</span>
                </span>
            </a>
            <a href="#guests-today" class="flex items-center gap-3 rounded-lg transition hover:bg-slate-50">
                <span class="icon-chip h-9 w-9 shrink-0 border-amber-200 bg-amber-50 text-amber-700"><x-icon name="checkout-instructions" class="h-4 w-4" /></span>
                <span>
                    <span class="block text-lg font-bold leading-tight text-slate-950">{{ $overview['checkouts'] }}</span>
                    <span class="block text-xs text-slate-500">Check-outs</span>
                </span>
            </a>
            <a href="#guests-today" class="flex items-center gap-3 rounded-lg transition hover:bg-slate-50">
                <span class="icon-chip h-9 w-9 shrink-0 border-blue-200 bg-blue-50 text-blue-700"><x-icon name="guests" class="h-4 w-4" /></span>
                <span>
                    <span class="block text-lg font-bold leading-tight text-slate-950">{{ $overview['hosting'] }}</span>
                    <span class="block text-xs text-slate-500">Currently Hosting</span>
                </span>
            </a>
            <a href="#guests-today" class="flex items-center gap-3 rounded-lg transition hover:bg-slate-50">
                <span class="icon-chip h-9 w-9 shrink-0 border-red-200 bg-red-50 text-red-700"><x-icon name="security" class="h-4 w-4" /></span>
                <span>
                    <span class="block text-lg font-bold leading-tight text-slate-950">{{ $overview['pending_approvals'] }}</span>
                    <span class="block text-xs text-slate-500">Pending Approvals</span>
                </span>
            </a>
        </div>
    </div>

    {{-- Today + Upcoming, priority-ordered (pending check-ins first, then
         approved/checked-in guests, then check-outs) --}}
    <div class="flex flex-col gap-4">
        <section id="guests-today" class="card scroll-mt-24 overflow-hidden" data-tour="guests-today">
            <div class="flex items-center justify-between border-b border-slate-100 p-4">
                <h2 class="font-bold text-slate-950">Today</h2>
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $todayGuests->count() }} guest{{ $todayGuests->count() === 1 ? '' : 's' }}</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($todayGuests as $booking)
                    <a href="{{ route('admin.guests.show', $booking) }}" class="block px-4 py-3 transition hover:bg-slate-50">
                        <div class="flex items-center justify-between gap-3">
                            <span class="flex min-w-0 items-center gap-2">
                                <img src="{{ $booking->property->heroImageUrl() }}" alt="" class="h-6 w-9 shrink-0 rounded object-cover">
                                <span class="truncate text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $booking->property->name }}</span>
                            </span>
                            <span class="badge badge-{{ $booking->effectiveStatus() }} shrink-0">{{ $booking->statusLabel() }}</span>
                        </div>
                        <p class="mt-1 truncate font-semibold text-slate-950">{{ $booking->guest_name }}</p>
                        <p class="truncate text-sm text-slate-600">{!! $booking->dashboardArrivalLine($today) !!}</p>
                    </a>
                @empty
                    <p class="p-4 text-sm text-slate-500">No guests arriving, staying, or checking out today.</p>
                @endforelse
            </div>
        </section>

        <section class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 p-4">
                <h2 class="font-bold text-slate-950">Upcoming</h2>
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $upcomingGuests->count() }} guest{{ $upcomingGuests->count() === 1 ? '' : 's' }}</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($upcomingGuests as $booking)
                    <a href="{{ route('admin.guests.show', $booking) }}" class="block px-4 py-3 transition hover:bg-slate-50">
                        <div class="flex items-center justify-between gap-3">
                            <span class="flex min-w-0 items-center gap-2">
                                <img src="{{ $booking->property->heroImageUrl() }}" alt="" class="h-6 w-9 shrink-0 rounded object-cover">
                                <span class="truncate text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $booking->property->name }}</span>
                            </span>
                            <span class="badge badge-{{ $booking->effectiveStatus() }} shrink-0">{{ $booking->statusLabel() }}</span>
                        </div>
                        <p class="mt-1 truncate font-semibold text-slate-950">{{ $booking->guest_name }}</p>
                        <p class="truncate text-sm text-slate-600">{!! $booking->dashboardArrivalLine($today) !!}</p>
                    </a>
                @empty
                    <p class="p-4 text-sm text-slate-500">No upcoming arrivals yet.</p>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Recent activity --}}
    <section class="card card-pad mt-6" data-tour="recent-activity">
        <div class="flex items-center justify-between">
            <h2 class="section-title">Recent Activity</h2>
            <a href="{{ route('admin.logs.index') }}" class="text-sm font-semibold text-teal-800">View all</a>
        </div>
        <p class="section-copy">Live feed of what's happening.</p>
        <div class="mt-4 flex flex-col gap-4">
            @forelse($recentActivity as $log)
                <div class="flex items-start gap-3">
                    <span class="icon-chip h-8 w-8 shrink-0"><x-icon name="security" class="h-4 w-4" /></span>
                    <div class="min-w-0">
                        <p class="text-sm text-slate-950">{{ $log->description }}</p>
                        <p class="text-xs text-slate-500">{{ $log->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">No activity yet.</p>
            @endforelse
        </div>
    </section>

    <div id="dashboard-tour-data" data-steps="{{ json_encode($dashTourSteps) }}" data-complete-url="{{ route('admin.tour.dashboard.complete') }}" data-csrf="{{ csrf_token() }}" class="hidden"></div>
</x-admin-layout>
