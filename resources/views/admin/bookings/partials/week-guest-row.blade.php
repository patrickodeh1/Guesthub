<div class="flex items-center justify-between gap-4 border-b border-slate-100 px-4 py-3 last:border-0">
    <div class="min-w-0 flex-1">
        <a class="block truncate text-[15px] font-bold text-slate-950 hover:text-teal-800" href="{{ route('admin.guests.show', $booking) }}">{{ $booking->guest_name }}</a>
        <p class="truncate text-sm font-normal italic text-slate-500">{{ $booking->property->name }}</p>
        <p class="text-sm text-slate-600">{{ $booking->stayRangeLabel() }} &middot; {{ $booking->weekCardDynamicLabel() }}</p>
    </div>

    <div class="shrink-0 text-right">
        @if($context === 'next-week')
            <span class="badge badge-arrival-countdown whitespace-nowrap">{{ $booking->arrivalCountdownLabel() }}</span>
        @else
            <span class="badge badge-{{ $booking->effectiveStatus() }} whitespace-nowrap">{{ $booking->statusLabel() }}</span>
        @endif
    </div>

    <div class="relative shrink-0" data-row-menu>
        <button type="button" class="btn-secondary !px-2" onclick="toggleRowMenu(this)" aria-label="Actions"><x-icon name="more-vertical" class="h-4 w-4" /></button>
        <div data-row-menu-panel class="hidden absolute right-0 z-10 mt-1 w-56 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
            <a href="{{ route('admin.guests.show', $booking) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"><x-icon name="eye" class="h-4 w-4" />View Details</a>
            <a href="{{ route('admin.guests.edit', $booking) }}" class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"><x-icon name="edit" class="h-4 w-4" />Edit</a>
            <button type="button" onclick="copyGuestUrl(this, '{{ $booking->publicUrl() }}')" class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"><x-icon name="copy" class="h-4 w-4" /><span data-copy-label>Copy Guest URL</span></button>

            <div class="my-1 border-t border-slate-100"></div>

            @if(($booking->photo_id_path || $booking->photo_id_back_path) && ! $booking->isApproved())
                <form method="post" action="{{ route('admin.guests.approve', $booking) }}">@csrf<button class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"><x-icon name="check" class="h-4 w-4" />Approve for Check-In</button></form>
            @endif
            @if(! $booking->isCheckedIn())
                <form method="post" action="{{ route('admin.guests.override', $booking) }}">@csrf<button class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"><x-icon name="contact-guest-services" class="h-4 w-4" />Manually Mark Checked In</button></form>
            @endif
            @if($booking->isCheckedIn() && ! $booking->checked_out_at)
                <form method="post" action="{{ route('admin.guests.override-checkout', $booking) }}">@csrf<button class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"><x-icon name="contact-guest-services" class="h-4 w-4" />Manually Mark Checked Out</button></form>
            @endif
        </div>
    </div>
</div>
