<tr>
    <td><a class="font-semibold text-slate-950 hover:text-teal-800" href="{{ route('admin.guests.show', $booking) }}">{{ $booking->guest_name }}</a></td>
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
