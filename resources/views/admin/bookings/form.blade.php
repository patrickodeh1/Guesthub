<x-admin-layout :title="$booking->exists ? 'Edit Guest' : 'Add Guest'">
    <div class="page-header">
        <div>
            <p class="eyebrow">Guest details</p>
            <h1 class="page-title">{{ $booking->exists ? 'Edit guest' : 'Add guest' }}</h1>
            <p class="page-subtitle">Create a secure guest URL from guest details. Guests use it for ID upload, GPS arrival, and the welcome guide.</p>
        </div>
        <a href="{{ route('admin.guests.index') }}" class="btn-secondary">Back to Guests</a>
    </div>

    <form method="post" action="{{ $booking->exists ? route('admin.guests.update', $booking) : route('admin.guests.store') }}" class="grid gap-6 xl:grid-cols-[1fr_360px]">
        @csrf @if($booking->exists) @method('put') @endif
        <section class="card card-pad">
            <h2 class="section-title">Guest and stay details</h2>
            <p class="section-copy">Booking ID may be left blank to generate one automatically.</p>
            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <label class="field-label">Booking ID<input name="booking_id" value="{{ old('booking_id', $booking->booking_id) }}" placeholder="Auto-generated if blank" class="input"></label>
                <label class="field-label">Reservation ID (Airbnb/VRBO) <span class="text-red-600">*</span><input name="reservation_id" value="{{ old('reservation_id', $booking->reservation_id) }}" placeholder="Required, from Airbnb/VRBO" required class="input"></label>
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
            @if($booking->exists && $booking->parking_needed)
            <div class="field-label mt-5">
                <span>Parking charge (admin only)</span>
                <p class="field-help mt-1">Auto-calculated: ${{ number_format($booking->parking_charge ?? 0, 2) }} from the property's weekday rates across the stay.</p>
                <div class="flex items-center gap-1 mt-1">
                    <span class="text-slate-500">$</span>
                    <input type="number" step="0.01" min="0" name="parking_charge_override" value="{{ old('parking_charge_override', $booking->parking_charge_override) }}" placeholder="Override amount" class="input">
                </div>
                <span class="field-help">Leave blank to use the auto-calculated amount. Set a value to override it.</span>
            </div>
            @endif
            <label class="field-label mt-5">
                Incidentals charge (admin only)
                <div class="flex items-center gap-1 mt-1">
                    <span class="text-slate-500">$</span>
                    <input type="number" step="0.01" min="0" name="incidentals_charge" value="{{ old('incidentals_charge', $booking->incidentals_charge) }}" placeholder="0.00" class="input">
                </div>
                <span class="field-help">Any incidentals charge for this guest. Not visible to the guest.</span>
            </label>
            <label class="field-label mt-5 flex items-center gap-2">
                <input type="checkbox" name="early_checkin" value="1" @checked(old('early_checkin', $booking->early_checkin))>
                <span>Early Check-in Exception</span>
            </label>
            <p class="field-help">If enabled, the property address is shown to the guest immediately, bypassing the check-in day / 3:00 PM rule.</p>
            <label class="field-label mt-5">
                Early check-in billing tier (admin only)
                <select name="early_checkin_tier" class="input">
                    <option value="">None</option>
                    <option value="8am" @selected(old('early_checkin_tier', $booking->early_checkin_tier)==='8am')>8:00 AM tier</option>
                    <option value="12pm" @selected(old('early_checkin_tier', $booking->early_checkin_tier)==='12pm')>12:00 PM tier</option>
                </select>
                @if($booking->exists && $booking->early_checkin_tier)
                    <span class="field-help">Charge: ${{ number_format($booking->earlyCheckinCharge() ?? 0, 2) }} (from the property's rate for this tier).</span>
                @else
                    <span class="field-help">Independent of the exception checkbox above; set this if the early check-in should be billed.</span>
                @endif
            </label>
            <div class="field-label mt-5">
                <span>Late checkout billing (admin only)</span>
                <select name="late_checkout_type" class="input mt-1">
                    <option value="">Not applicable</option>
                    <option value="authorized" @selected(old('late_checkout_type', $booking->late_checkout_type)==='authorized')>Authorized</option>
                    <option value="unauthorized" @selected(old('late_checkout_type', $booking->late_checkout_type)==='unauthorized')>Unauthorized</option>
                </select>
                <label class="field-label mt-3">Hours late (authorized only)<input type="number" step="0.25" min="0" name="late_checkout_hours" value="{{ old('late_checkout_hours', $booking->late_checkout_hours) }}" placeholder="e.g. 2" class="input"></label>
                <label class="field-label mt-3">Actual checkout time (unauthorized only)<input type="datetime-local" name="late_checkout_actual_time" value="{{ old('late_checkout_actual_time', optional($booking->late_checkout_actual_time)->format('Y-m-d\TH:i')) }}" class="input"></label>
                <span class="field-help">Separate from the system's automatic checkout timestamp; enter what time the guest actually left for an unauthorized late checkout, so hours can be calculated.</span>
                @if($booking->exists && $booking->late_checkout_type)
                    <span class="field-help font-semibold">Charge: ${{ number_format($booking->lateCheckoutCharge() ?? 0, 2) }}</span>
                @endif
            </div>
            <label class="field-label mt-5 flex items-center gap-2">
                <input type="checkbox" name="photo_id_received" value="1" @checked(old('photo_id_received', $booking->photo_id_received))>
                <span>Photo ID Already Received</span>
            </label>
            <p class="field-help">If enabled, the guest will not be asked to upload a photo ID during check-in.</p>
            <label class="field-label mt-5">Check-in Time<input type="time" name="checkin_time_preference" value="{{ old('checkin_time_preference', $booking->checkin_time_preference) }}" class="input"></label>
            <label class="field-label mt-5">Check-out Time<input type="time" name="checkout_time_preference" value="{{ old('checkout_time_preference', $booking->checkout_time_preference) }}" class="input"></label>
            <label class="field-label mt-5">Status<select name="status" class="input">@foreach(['pending','pre_checkin_complete','awaiting_deposit','guest_approved','currently_hosting','checked_out'] as $status)<option value="{{ $status }}" @selected(old('status', $booking->status ?: 'pending')===$status)>{{ str($status)->replace('_',' ')->title() }}</option>@endforeach</select></label>
            <button class="btn-primary mt-6 w-full">Save guest</button>
            @if($booking->exists)
                <a href="{{ route('admin.guests.show', $booking) }}" class="btn-secondary mt-3 w-full">View guest URL</a>
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
