<x-admin-layout title="Guests">
    <div class="page-header">
        <div>
            <p class="eyebrow">Guest management</p>
            <h1 class="page-title">Guests</h1>
            <p class="page-subtitle">Search guests, review guest progress, copy arrival links, and handle manual approvals.</p>
        </div>
        <a href="{{ route('admin.guests.create') }}" class="btn-primary">Add Guest</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-5">
        <div class="card card-pad flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700"><x-icon name="users" class="h-6 w-6" /></div>
            <div><p class="text-sm text-slate-500">Total Guests</p><p class="text-2xl font-bold text-slate-900">{{ number_format($stats['total_guests']) }}</p><p class="text-xs text-slate-400">All time</p></div>
        </div>
        <div class="card card-pad flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700"><x-icon name="calendar" class="h-6 w-6" /></div>
            <div><p class="text-sm text-slate-500">Today's Arrivals</p><p class="text-2xl font-bold text-slate-900">{{ number_format($stats['todays_arrivals']) }}</p><p class="text-xs text-slate-400">Expected check-ins today</p></div>
        </div>
        <div class="card card-pad flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700"><x-icon name="bell" class="h-6 w-6" /></div>
            <div><p class="text-sm text-slate-500">Waiting Approval</p><p class="text-2xl font-bold text-slate-900">{{ number_format($stats['waiting_approval']) }}</p><p class="text-xs text-slate-400">Require your attention</p></div>
        </div>
        <div class="card card-pad flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-violet-100 text-violet-700"><x-icon name="check" class="h-6 w-6" /></div>
            <div><p class="text-sm text-slate-500">Checked In</p><p class="text-2xl font-bold text-slate-900">{{ number_format($stats['checked_in']) }}</p><p class="text-xs text-slate-400">Currently in-house</p></div>
        </div>
    </div>
    <div class="card card-pad mb-5">
        <form id="guest-filter-form" class="grid gap-3 md:grid-cols-[1fr_200px_200px_auto]">
            <div class="relative"><x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input name="search" value="{{ request('search') }}" placeholder="Search guest, booking ID, or email" class="input mt-0 pl-9"></div>
            <select name="status" class="input mt-0"><option value="">All statuses</option>@foreach(['pending','pre_checkin_complete','awaiting_deposit','guest_approved','pending_check_in','currently_hosting','checked_out'] as $status)<option @selected(request('status')===$status) value="{{ $status }}">{{ str($status)->replace('_',' ')->title() }}</option>@endforeach</select>
            <select name="property_id" class="input mt-0"><option value="">All properties</option>@foreach($properties as $property)<option @selected((string) request('property_id')===(string) $property->id) value="{{ $property->id }}">{{ $property->name }}</option>@endforeach</select>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.guests.index') }}" class="btn-secondary gap-2"><x-icon name="refresh" class="h-4 w-4" />Reset</a>
                <button class="btn-primary gap-2"><x-icon name="filter" class="h-4 w-4" />Filter</button>
            </div>
        </form>
        <label class="mt-3 flex w-fit items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="archived" value="1" form="guest-filter-form" @checked(request()->boolean('archived')) onchange="this.form.requestSubmit ? document.getElementById('guest-filter-form').requestSubmit() : document.getElementById('guest-filter-form').submit()"> Show archived</label>
    </div>

    <div class="table-wrap">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Guest</th><th>Property</th><th>Stay</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td><a class="font-semibold text-slate-950 hover:text-teal-800" href="{{ route('admin.guests.show', $booking) }}">{{ $booking->guest_name }}</a><div class="text-slate-500">{{ $booking->booking_id }}</div><div class="text-slate-400 text-xs">RID: {{ $booking->reservation_id ?: '—' }}</div></td>
                            <td>{{ $booking->property->name }}</td>
                            <td>{{ $booking->stayRangeLabel() }}</td>
                            <td><span class="badge badge-{{ $booking->effectiveStatus() }}">{{ $booking->statusLabel() }}</span></td>
                            <td><div class="flex items-center gap-2">
                                <a class="btn-secondary gap-2" href="{{ route('admin.guests.show', $booking) }}"><x-icon name="eye" class="h-4 w-4" />View Details</a>
                                <div class="relative" data-row-menu>
                                    <button type="button" class="btn-ghost !px-2" onclick="const p=this.nextElementSibling; document.querySelectorAll('[data-row-menu-panel]').forEach(el=>el!==p&&el.classList.add('hidden')); p.classList.toggle('hidden')"><x-icon name="more-vertical" class="h-4 w-4" /></button>
                                    <div data-row-menu-panel class="hidden absolute right-0 z-10 mt-1 w-44 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
                                        <a href="{{ route('admin.guests.edit', $booking) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"><x-icon name="edit" class="h-4 w-4" />Edit</a>
                                        @if($booking->archived_at)
                                            <form method="post" action="{{ route('admin.guests.unarchive', $booking) }}">@csrf<button class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"><x-icon name="refresh" class="h-4 w-4" />Restore</button></form>
                                        @else
                                            <form method="post" action="{{ route('admin.guests.archive', $booking) }}">@csrf<button class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"><x-icon name="folder" class="h-4 w-4" />Archive</button></form>
                                        @endif
                                        <form method="post" action="{{ route('admin.guests.destroy', $booking) }}" onsubmit="return confirm('Delete this guest? This cannot be undone.')">@csrf @method('delete')<button class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50"><x-icon name="delete" class="h-4 w-4" />Delete</button></form>
                                    </div>
                                </div>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-slate-500">No guests found. Add a guest to generate the first secure URL.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-5">{{ $bookings->links() }}</div>
</x-admin-layout>
