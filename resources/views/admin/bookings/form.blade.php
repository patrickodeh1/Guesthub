<x-admin-layout :title="$booking->exists ? 'Edit Guest' : 'Add Guest'">
    <div class="page-header">
        <div>
            <p class="eyebrow">Guest booking</p>
            <h1 class="page-title">{{ $booking->exists ? 'Edit guest booking' : 'Add guest booking' }}</h1>
            <p class="page-subtitle">Create a secure guest URL from booking details. Guests use it for ID upload, GPS arrival, and the welcome guide.</p>
        </div>
        <a href="{{ route('admin.bookings.index') }}" class="btn-secondary">Back to Guests</a>
    </div>

    <form method="post" action="{{ $booking->exists ? route('admin.bookings.update', $booking) : route('admin.bookings.store') }}" class="grid gap-6 xl:grid-cols-[1fr_360px]">
        @csrf @if($booking->exists) @method('put') @endif
        <section class="card card-pad">
            <h2 class="section-title">Guest and stay details</h2>
            <p class="section-copy">Booking ID may be left blank to generate one automatically.</p>
            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <label class="field-label">Booking ID<input name="booking_id" value="{{ old('booking_id', $booking->booking_id) }}" placeholder="Auto-generated if blank" class="input"></label>
                <label class="field-label">Guest name <span class="text-red-600">*</span><input name="guest_name" value="{{ old('guest_name', $booking->guest_name) }}" required placeholder="Jordan Taylor" class="input"></label>
                <label class="field-label">Phone<input name="phone" value="{{ old('phone', $booking->phone) }}" placeholder="+1 555 555 0199" class="input"></label>
                <label class="field-label">Email<input name="email" value="{{ old('email', $booking->email) }}" placeholder="guest@example.com" class="input"></label>
                <label class="field-label">Check-in <span class="text-red-600">*</span><input type="date" name="check_in_date" value="{{ old('check_in_date', optional($booking->check_in_date)->format('Y-m-d')) }}" required class="input"></label>
                <label class="field-label">Check-out <span class="text-red-600">*</span><input type="date" name="check_out_date" value="{{ old('check_out_date', optional($booking->check_out_date)->format('Y-m-d')) }}" required class="input"></label>
                <label class="field-label md:col-span-2">Property <span class="text-red-600">*</span><select name="property_id" required class="input">@foreach($properties as $property)<option value="{{ $property->id }}" @selected(old('property_id', $booking->property_id)==$property->id)>{{ $property->name }}</option>@endforeach</select></label>
                <label class="field-label">ID type <span class="text-red-600">*</span><select name="id_type" required class="input"><option value="state_id" @selected(old('id_type', $booking->id_type ?: 'state_id')==='state_id')>State-issued ID (US guest)</option><option value="passport" @selected(old('id_type', $booking->id_type ?: 'state_id')==='passport')>Passport (international guest)</option></select></label>
            </div>
        </section>

        <aside class="card card-pad">
            <h2 class="section-title">Status controls</h2>
            <p class="section-copy">Use these to reflect what has happened outside the public guest flow.</p>
            <label class="field-label mt-5">Parking<select name="parking_needed" class="input"><option value="">Unknown</option><option value="1" @selected(old('parking_needed', $booking->parking_needed)==='1' || old('parking_needed', $booking->parking_needed)===true)>Yes, guest needs parking</option><option value="0" @selected(old('parking_needed', $booking->parking_needed)==='0' || old('parking_needed', $booking->parking_needed)===false)>No parking needed</option></select></label>
            <label class="field-label mt-5 flex items-center gap-2">
                <input type="checkbox" name="early_checkin" value="1" @checked(old('early_checkin', $booking->early_checkin))>
                <span>Early Check-in Exception</span>
            </label>
            <p class="field-help">If enabled, the property address is shown to the guest immediately, bypassing the check-in day / 3:00 PM rule.</p>
            <label class="field-label mt-5 flex items-center gap-2">
                <input type="checkbox" name="photo_id_received" value="1" @checked(old('photo_id_received', $booking->photo_id_received))>
                <span>Photo ID Already Received</span>
            </label>
            <p class="field-help">If enabled, the guest will not be asked to upload a photo ID during check-in.</p>
            <label class="field-label mt-5">Status<select name="status" class="input">@foreach(['pending','id_uploaded','waiting_checkin','checked_in','checked_out'] as $status)<option value="{{ $status }}" @selected(old('status', $booking->status ?: 'pending')===$status)>{{ str($status)->replace('_',' ')->title() }}</option>@endforeach</select></label>
            <button class="btn-primary mt-6 w-full">Save guest</button>
            @if($booking->exists)
                <a href="{{ route('admin.bookings.show', $booking) }}" class="btn-secondary mt-3 w-full">View guest URL</a>
            @endif
        </aside>

        @if($instructionSteps->isNotEmpty())
        @endif
        <section class="card card-pad xl:col-span-2">
            <h2 class="section-title">Internal notes</h2>
            <p class="section-copy">Notes are visible to admins only and never shown on the guest page.</p>
            <label class="field-label mt-5">Notes<textarea name="notes" rows="5" placeholder="Arrival requests, internal reminders, owner notes..." class="textarea">{{ old('notes', $booking->notes) }}</textarea></label>
        </section>
    </form>
</x-admin-layout>
