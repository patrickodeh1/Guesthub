<x-admin-layout title="Guests">
    <div class="page-header">
        <div>
            <p class="eyebrow">Guest management</p>
            <h1 class="page-title">Guests and bookings</h1>
            <p class="page-subtitle">Search bookings, review guest progress, copy arrival links, and handle manual approvals.</p>
        </div>
        <a href="{{ route('admin.bookings.create') }}" class="btn-primary">Add Guest</a>
    </div>

    <div class="card card-pad mb-5">
        <form class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
            <input name="search" value="{{ request('search') }}" placeholder="Search guest, booking ID, or email" class="input mt-0">
            <select name="status" class="input mt-0"><option value="">All statuses</option>@foreach(['pending','id_uploaded','waiting_checkin','checked_in','checked_out'] as $status)<option @selected(request('status')===$status) value="{{ $status }}">{{ str($status)->replace('_',' ')->title() }}</option>@endforeach</select>
            <button class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="table-wrap">
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Guest</th><th>Property</th><th>Stay</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td><a class="font-semibold text-slate-950 hover:text-teal-800" href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->guest_name }}</a><div class="text-slate-500">{{ $booking->booking_id }}</div></td>
                            <td>{{ $booking->property->name }}</td>
                            <td>{{ $booking->check_in_date->format('M j, Y') }} - {{ $booking->check_out_date->format('M j, Y') }}</td>
                            <td><span class="badge badge-{{ $booking->status }}">{{ $booking->statusLabel() }}</span></td>
                            <td><div class="flex flex-wrap gap-2"><a class="btn-secondary gap-2" href="{{ route('admin.bookings.show', $booking) }}"><x-icon name="search" class="h-4 w-4" />View</a><a class="btn-ghost gap-2" href="{{ route('admin.bookings.edit', $booking) }}"><x-icon name="edit" class="h-4 w-4" />Edit</a></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-slate-500">No guests found. Add a guest booking to generate the first secure URL.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-5">{{ $bookings->links() }}</div>
</x-admin-layout>
