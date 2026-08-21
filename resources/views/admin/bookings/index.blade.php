<x-admin-layout title="Guests">
    <div class="page-header">
        <div>
            <p class="eyebrow">Guest management</p>
            <h1 class="page-title">Guests</h1>
            <p class="page-subtitle">Search guests, review guest progress, copy arrival links, and handle manual approvals.</p>
        </div>
        <a href="{{ route('admin.guests.create') }}" class="btn-primary">Add Guest</a>
    </div>

    <div class="card card-pad mb-5">
        <form id="guest-filter-form" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[240px]"><x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input name="search" value="{{ request('search') }}" placeholder="Search by guest name, booking ID, or reservation ID" class="input mt-0 pl-9"></div>
            <button class="btn-primary gap-2"><x-icon name="search" class="h-4 w-4" />Search</button>
            @if(request('search'))
                <a href="{{ route('admin.guests.index') }}" class="btn-secondary gap-2"><x-icon name="refresh" class="h-4 w-4" />Clear</a>
            @endif
        </form>
        <label class="mt-3 flex w-fit items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="archived" value="1" form="guest-filter-form" @checked(request()->boolean('archived')) onchange="this.form.requestSubmit ? document.getElementById('guest-filter-form').requestSubmit() : document.getElementById('guest-filter-form').submit()"> Show archived</label>
    </div>

    @if($needsAttention->isNotEmpty())
    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-slate-950 flex items-center gap-2"><x-icon name="bell" class="h-5 w-5 text-amber-600" />Needs Attention</h2>
        <span class="text-sm text-slate-500">{{ $needsAttention->count() }} action item{{ $needsAttention->count() === 1 ? '' : 's' }}</span>
    </div>
    <div class="table-wrap mb-8">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Guest</th><th>Property</th><th>Stay</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach($needsAttention as $booking)
                        <tr>
                            <td><a class="font-semibold text-slate-950 hover:text-teal-800" href="{{ route('admin.guests.show', $booking) }}">{{ $booking->guest_name }}</a></td>
                            <td>{{ $booking->property->name }}</td>
                            <td>{{ $booking->stayRangeLabel() }}</td>
                            <td><span class="badge badge-{{ $booking->effectiveStatus() }}">{{ $booking->statusLabel() }}</span></td>
                            <td class="text-right"><a href="{{ route('admin.guests.show', $booking) }}" class="btn-primary gap-2"><x-icon name="security" class="h-4 w-4" />Review ID</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($currentlyHosting->isNotEmpty())
    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-slate-950">Currently Hosting</h2>
        <span class="text-sm text-slate-500">{{ $currentlyHosting->count() }} guest{{ $currentlyHosting->count() === 1 ? '' : 's' }}</span>
    </div>
    <div class="table-wrap mb-8">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Guest</th><th>Property</th><th>Stay</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach($currentlyHosting as $booking)
                        @include('admin.bookings.partials.guest-row', ['booking' => $booking])
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="mb-3 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-slate-950">All Guests</h2>
    </div>
    <div class="table-wrap">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Guest</th><th>Property</th><th>Stay</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($bookings as $booking)
                        @include('admin.bookings.partials.guest-row', ['booking' => $booking])
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-slate-500">No guests found. Add a guest to generate the first secure URL.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-5">{{ $bookings->links() }}</div>
</x-admin-layout>
