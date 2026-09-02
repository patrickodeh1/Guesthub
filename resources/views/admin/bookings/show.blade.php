<x-admin-layout title="Guest Details">
    <a href="{{ route('admin.guests.index') }}" class="mb-4 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-800">
        <x-icon name="arrow-left" class="h-4 w-4" />
        Back to Guests
    </a>

    <div class="grid grid-cols-1 gap-3 lg:grid-cols-4 lg:items-start">
    <div class="lg:col-span-3">
    <section class="card card-pad mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="eyebrow">Booking {{ $booking->booking_id }} &middot; RID: {{ $booking->reservation_id ?: 'Not set' }}</p>
                <h1 class="page-title !mt-0.5">{{ $booking->guest_name }}</h1>
                <p class="page-subtitle !mt-1">{{ $booking->property->name }} · {{ $booking->stayRangeLabel() }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="text-right">
                    <p class="text-xs text-slate-500">Current Status</p>
                    <span class="badge badge-{{ $booking->effectiveStatus() }} mt-1 px-3 py-1 text-sm">{{ $booking->statusLabel() }}</span>
                </div>
                <a href="{{ route('admin.guests.edit', $booking) }}" class="btn-primary">Edit Guest</a>
            </div>
        </div>
    </section>
    </div>

        <div class="mt-3 grid content-start gap-3 lg:col-span-3 order-3 lg:order-none">
            {{-- Guest Details --}}
            <section class="card card-pad">
                <div class="flex items-center justify-between">
                    <h2 class="section-title">Guest Details</h2>
                    <a href="{{ route('admin.guests.edit', $booking) }}" class="text-sm font-semibold text-teal-800">Edit Details</a>
                </div>
                <dl class="mt-4 columns-1 gap-x-10 text-sm sm:columns-2 sm:[column-rule:1px_solid_theme(colors.slate.200)]">
                    @foreach([
                        ['calendar', 'Check-in Date', $booking->check_in_date ? $booking->check_in_date->format('M j, Y') : 'Not set'],
                        ['calendar', 'Check-out Date', $booking->check_out_date ? $booking->check_out_date->format('M j, Y').' '.$booking->nightsLabel() : 'Not set'],
                        ['mail', 'Email', $booking->email ?: 'No email yet'],
                        ['contact-guest-services', 'Phone', $booking->formatted_phone ?: 'No phone on file'],
                        ['security', 'ID Type', $booking->id_type === 'passport' ? 'Passport' : 'State-issued ID'],
                        ['parking', 'Parking', is_null($booking->parking_needed) ? 'Unknown' : ($booking->parking_needed ? 'Needed' : 'Not needed')],
                        ...($booking->parking_needed ? [['parking', 'Parking Charge', '$'.number_format($booking->effectiveParkingCharge() ?? 0, 2).($booking->parking_charge_override !== null ? ' (manual override)' : ' (auto-calculated)')]] : []),
                        ...($booking->parking_needed ? [['parking', 'Vehicle', $booking->vehicle_make_model ?: 'Not provided']] : []),
                        ['info', 'Incidentals Charge', $booking->incidentals_charge !== null ? '$'.number_format($booking->incidentals_charge, 2) : 'Not set'],
                        ...($booking->early_checkin_tier ? [['calendar', 'Early Check-in Charge', '$'.number_format($booking->earlyCheckinCharge() ?? 0, 2).' ('.(match($booking->early_checkin_tier) { '8am_12pm', '8am' => '8:00 AM - 12:00 PM', '12pm_2pm', '12pm' => '12:00 PM - 2:00 PM', '2pm_4pm' => '2:00 PM - 4:00 PM', default => $booking->early_checkin_tier }).' window)']] : []),
                        ...($booking->late_checkout_type ? [['clock', 'Late Checkout Charge', '$'.number_format($booking->lateCheckoutCharge() ?? 0, 2).' ('.ucfirst($booking->late_checkout_type).', billed per half-hour)']] : []),
                        ['info', 'Total Pre-checkin Charge:', '$'.number_format($booking->calculatePreCheckinChargeCents() / 100, 2).' ('.$booking->preCheckinChargeBreakdown().')'],
                        ['clock', 'Requested Check-in Time', ($booking->checkinTimePreferenceFormatted() ?: 'Not specified').($booking->checkin_time_status ? ' ('.ucfirst($booking->checkin_time_status).')' : '')],
                        ['clock', 'Requested Check-out Time', ($booking->checkoutTimePreferenceFormatted() ?: 'Not specified').($booking->checkout_time_status ? ' ('.ucfirst($booking->checkout_time_status).')' : '')],
                        ['upload', 'Photo ID Already Received', $booking->photo_id_received ? 'Yes' : 'No'],
                        ['map', 'GPS', $booking->gps_verified ? 'Verified' : 'Not verified'],
                        ['contact-guest-services', 'Checked In At', $booking->checked_in_at ? $booking->checked_in_at->format('M j, Y g:i A') : 'Not yet'],
                        ['contact-guest-services', 'Checked Out At', $booking->checked_out_at ? $booking->checked_out_at->format('M j, Y g:i A') : 'Not yet'],
                    ] as [$icon, $label, $value])
                        <div class="flex items-start justify-between gap-4 border-b border-slate-100 py-3 break-inside-avoid last:border-0">
                            <span class="flex items-center gap-2.5 text-slate-500"><x-icon :name="$icon" class="h-4 w-4 shrink-0 text-slate-400" />{{ $label }}</span>
                            <span class="font-semibold text-slate-950 text-right">{{ $value }}</span>
                        </div>
                    @endforeach
                </dl>
                @if($booking->photo_id_front_declined_reason)
                    <div class="mt-4 rounded-lg bg-amber-50 border border-amber-200 p-3 text-sm text-amber-800"><span class="font-semibold">Front ID decline reason:</span> {{ $booking->photo_id_front_declined_reason }}</div>
                @endif
                @if($booking->photo_id_back_declined_reason)
                    <div class="mt-4 rounded-lg bg-amber-50 border border-amber-200 p-3 text-sm text-amber-800"><span class="font-semibold">Back ID decline reason:</span> {{ $booking->photo_id_back_declined_reason }}</div>
                @endif
                @if($booking->notes)
                    <div class="mt-4 rounded-lg bg-slate-50 border border-slate-200 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Internal notes</p>
                        <p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $booking->notes }}</p>
                    </div>
                @endif
            </section>

            <div class="grid gap-6 lg:grid-cols-5">
                {{-- Photo ID --}}
                <section class="card card-pad {{ $booking->parking_needed ? 'lg:col-span-3' : 'lg:col-span-5' }}">
                    <div class="flex items-center justify-between">
                        <h2 class="section-title">Photo ID</h2>
                        <span class="badge badge-active">{{ $booking->id_type === 'passport' ? 'Passport' : 'State-issued ID' }}</span>
                    </div>
                    @if($booking->photo_id_path || $booking->photo_id_back_path)
                        <div class="mt-4">
                            <div class="flex gap-4 border-b border-slate-200 text-sm font-semibold text-slate-500">
                                @if($booking->photo_id_path)
                                    <button type="button" id="photo-id-tab-front" onclick="switchPhotoIdTab('front')" class="-mb-px border-b-2 border-teal-700 pb-2 text-teal-800">Front</button>
                                @endif
                                @if($booking->photo_id_back_path)
                                    <button type="button" id="photo-id-tab-back" onclick="switchPhotoIdTab('back')" class="-mb-px pb-2 {{ $booking->photo_id_path ? '' : 'border-b-2 border-teal-700 text-teal-800' }}">Back</button>
                                @endif
                            </div>
                            @if($booking->photo_id_path)
                                <div id="photo-id-panel-front" class="mt-4">
                                    <button type="button" onclick="openPhotoIdModal('{{ route('admin.guests.photo-id-view', $booking) }}', 'Photo ID front')" class="block w-full text-left">
                                        <img src="{{ route('admin.guests.photo-id-view', $booking) }}" alt="Photo ID front" class="w-full max-h-64 rounded-lg border border-slate-200 object-contain bg-slate-50">
                                    </button>
                                    <div class="mt-3 flex flex-wrap gap-3">
                                        <button type="button" onclick="openPhotoIdModal('{{ route('admin.guests.photo-id-view', $booking) }}', 'Photo ID front')" class="text-sm font-semibold text-teal-800">View full size</button>
                                        <a class="text-sm font-semibold text-teal-800" href="{{ route('admin.guests.photo-id', $booking) }}">Download original</a>
                                    </div>
                                    <div class="mt-4 border-t border-slate-100 pt-4">
                                        @if($booking->isFrontIdApproved())
                                            <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-sm text-emerald-800 font-semibold">Front approved {{ $booking->photo_id_front_approved_at->format('M j, Y g:i A') }}</div>
                                        @else
                                            <div class="flex gap-2">
                                                <form method="post" action="{{ route('admin.guests.id.approve', [$booking, 'front']) }}" class="flex-1">@csrf<button class="btn-primary w-full gap-2"><x-icon name="check" class="h-4 w-4" />Approve Front</button></form>
                                                <button type="button" class="btn-danger flex-1 gap-2" onclick="document.getElementById('decline-form-front-{{ $booking->id }}').classList.toggle('hidden')"><x-icon name="x" class="h-4 w-4" />Decline</button>
                                            </div>
                                            <form id="decline-form-front-{{ $booking->id }}" method="post" action="{{ route('admin.guests.id.decline', [$booking, 'front']) }}" class="hidden mt-2 grid gap-2">
                                                @csrf
                                                <textarea name="decline_reason" class="input" rows="3" placeholder="Reason for declining the front (shown to guest)" required>{{ old('decline_reason') }}</textarea>
                                                <button class="btn-secondary w-full">Submit Decline</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if($booking->photo_id_back_path)
                                <div id="photo-id-panel-back" class="mt-4 {{ $booking->photo_id_path ? 'hidden' : '' }}">
                                    <button type="button" onclick="openPhotoIdModal('{{ route('admin.guests.photo-id-back-view', $booking) }}', 'Photo ID back')" class="block w-full text-left">
                                        <img src="{{ route('admin.guests.photo-id-back-view', $booking) }}" alt="Photo ID back" class="w-full max-h-64 rounded-lg border border-slate-200 object-contain bg-slate-50">
                                    </button>
                                    <div class="mt-3 flex flex-wrap gap-3">
                                        <button type="button" onclick="openPhotoIdModal('{{ route('admin.guests.photo-id-back-view', $booking) }}', 'Photo ID back')" class="text-sm font-semibold text-teal-800">View full size</button>
                                        <a class="text-sm font-semibold text-teal-800" href="{{ route('admin.guests.photo-id-back', $booking) }}">Download original</a>
                                    </div>
                                    <div class="mt-4 border-t border-slate-100 pt-4">
                                        @if($booking->isBackIdApproved())
                                            <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-sm text-emerald-800 font-semibold">Back approved {{ $booking->photo_id_back_approved_at->format('M j, Y g:i A') }}</div>
                                        @else
                                            <div class="flex gap-2">
                                                <form method="post" action="{{ route('admin.guests.id.approve', [$booking, 'back']) }}" class="flex-1">@csrf<button class="btn-primary w-full gap-2"><x-icon name="check" class="h-4 w-4" />Approve Back</button></form>
                                                <button type="button" class="btn-danger flex-1 gap-2" onclick="document.getElementById('decline-form-back-{{ $booking->id }}').classList.toggle('hidden')"><x-icon name="x" class="h-4 w-4" />Decline</button>
                                            </div>
                                            <form id="decline-form-back-{{ $booking->id }}" method="post" action="{{ route('admin.guests.id.decline', [$booking, 'back']) }}" class="hidden mt-2 grid gap-2">
                                                @csrf
                                                <textarea name="decline_reason" class="input" rows="3" placeholder="Reason for declining the back (shown to guest)" required>{{ old('decline_reason') }}</textarea>
                                                <button class="btn-secondary w-full">Submit Decline</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="mt-4 font-semibold text-slate-950">Not uploaded</p>
                    @endif

                    @if($booking->isIdFullyApproved())
                        <div class="mt-5 rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-sm text-emerald-800 font-semibold">All ID photos approved{{ $booking->approved_at ? ' on '.$booking->approved_at->format('M j, Y g:i A') : '' }}</div>
                    @endif
                </section>

                @if($booking->checkin_time_status === 'pending' || $booking->checkout_time_status === 'pending')
                {{-- Task 0: guest-requested non-standard time needs admin review before it applies; may carry a charge (see task 26 billing). Manual review only, no auto-notification. --}}
                <section class="card card-pad lg:col-span-2">
                    <h2 class="section-title">Time Preference Review</h2>
                    <p class="section-copy">The guest requested a non-standard time below. It will not take effect until approved — the system will keep using the property's standard time in the meantime. Approving does not automatically apply a charge; set the early check-in / late checkout billing fields separately if one applies. No automatic notification is sent to the guest.</p>

                    @if($booking->checkin_time_status === 'pending')
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                        <div>
                            <p class="text-sm text-slate-500">Requested Check-in Time</p>
                            <p class="font-semibold text-slate-950">{{ $booking->checkinTimePreferenceFormatted() }} <span class="text-slate-500 font-normal">(standard: {{ $booking->standardCheckinTimeFormatted() }})</span></p>
                        </div>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('admin.guests.time-preference.update', [$booking, 'checkin']) }}">
                                @csrf
                                <input type="hidden" name="decision" value="approved">
                                <button type="submit" class="btn-primary">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.guests.time-preference.update', [$booking, 'checkin']) }}">
                                @csrf
                                <input type="hidden" name="decision" value="denied">
                                <button type="submit" class="btn-secondary">Deny</button>
                            </form>
                        </div>
                    </div>
                    @endif

                    @if($booking->checkout_time_status === 'pending')
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                        <div>
                            <p class="text-sm text-slate-500">Requested Check-out Time</p>
                            <p class="font-semibold text-slate-950">{{ $booking->checkoutTimePreferenceFormatted() }} <span class="text-slate-500 font-normal">(standard: {{ $booking->standardCheckoutTimeFormatted() }})</span></p>
                        </div>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('admin.guests.time-preference.update', [$booking, 'checkout']) }}">
                                @csrf
                                <input type="hidden" name="decision" value="approved">
                                <button type="submit" class="btn-primary">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.guests.time-preference.update', [$booking, 'checkout']) }}">
                                @csrf
                                <input type="hidden" name="decision" value="denied">
                                <button type="submit" class="btn-secondary">Deny</button>
                            </form>
                        </div>
                    </div>
                    @endif
                </section>
                @endif

                @if($booking->parking_needed)
                {{-- Vehicle / license plate photo, task 34 --}}
                <section class="card card-pad lg:col-span-2">
                    <h2 class="section-title">Vehicle</h2>
                    <p class="section-copy">Make/model and license plate photo, collected when the guest opted into parking.</p>
                    @if($booking->license_plate_photo_path)
                        <button type="button" onclick="openPhotoIdModal('{{ route('admin.guests.license-plate-view', $booking) }}', 'License plate')" class="mt-4 block w-full text-left">
                            <img src="{{ route('admin.guests.license-plate-view', $booking) }}" alt="License plate" class="w-full max-h-64 rounded-lg border border-slate-200 object-contain bg-slate-50">
                        </button>
                        <div class="mt-3 flex flex-wrap gap-3">
                            <button type="button" onclick="openPhotoIdModal('{{ route('admin.guests.license-plate-view', $booking) }}', 'License plate')" class="text-sm font-semibold text-teal-800">View full size</button>
                            <a class="text-sm font-semibold text-teal-800" href="{{ route('admin.guests.license-plate', $booking) }}">Download original</a>
                        </div>
                    @else
                        <p class="mt-4 font-semibold text-slate-950">Not uploaded</p>
                    @endif
                </section>
                @endif
            </div>

            {{-- Communication (collapsible) --}}
            <section class="card card-pad">
                <button type="button" onclick="toggleCommunicationSection()" class="flex w-full items-center justify-between text-left">
                    <div>
                        <h2 class="section-title">Communication</h2>
                        <p class="section-copy">Share secure link, send messages and customize welcome message.</p>
                    </div>
                    <span id="communication-chevron" class="transition-transform duration-150">
                        <x-icon name="chevron-right" class="h-5 w-5 text-slate-400" />
                    </span>
                </button>

                <div id="communication-body" class="mt-5 hidden">
                    <div class="grid gap-6">
                    <div id="guest-link-card" class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm font-semibold text-slate-700">Secure guest URL</p>
                        <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                            <input id="guest-url" readonly value="{{ $booking->publicUrl() }}" class="input mt-0 min-w-0 flex-1">
                            <button type="button" data-copy="#guest-url" class="btn-primary gap-2"><x-icon name="copy" class="h-4 w-4" />Copy URL</button>
                        </div>
                    </div>

                    <div class="rounded-xl border border-[#eadfc8] bg-[#fffaf1] p-4">
                        <p class="text-sm font-semibold text-slate-800">Guest message templates</p>
                        <textarea id="guest-message" readonly class="textarea min-h-24">Hi {{ (explode(' ', trim($booking->guest_name))[0]) }}, your secure check-in page is ready: {{ $booking->publicUrl() }}</textarea>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" data-copy="#guest-message" class="btn-secondary gap-2"><x-icon name="copy" class="h-4 w-4" />Copy Full Message</button>
                            <a class="btn-secondary gap-2" href="https://wa.me/?text={{ urlencode('Hi '.(explode(' ', trim($booking->guest_name))[0]).', your secure check-in page is ready: '.$booking->publicUrl()) }}" target="_blank"><x-icon name="contact-guest-services" class="h-4 w-4" />WhatsApp</a>
                        </div>

                        <div class="mt-5 border-t border-[#eadfc8] pt-4">
                            <p class="text-sm font-semibold text-slate-800">Custom welcome message for this guest</p>
                            <p class="mt-1 text-xs text-slate-500">Optional. If left blank, the global default intro from Settings is used instead.</p>
                            <form method="post" action="{{ route('admin.guests.welcome-message', $booking) }}" class="mt-3">
                                @csrf
                                @method('put')
                                <textarea id="welcome-message-editor" name="welcome_message" rows="5" class="textarea">{{ old('welcome_message', $booking->welcome_message) }}</textarea>
                                <button class="btn-primary mt-3">Save Welcome Message</button>
                            </form>
                        </div>
                    </div>
                    </div>
                </div>
            </section>

        </div>

        {{-- Sidebar --}}
        <aside class="contents lg:grid lg:content-start lg:gap-3 lg:col-start-4 lg:row-start-1 lg:row-span-3 lg:sticky lg:top-20">
            <section class="card card-pad order-2 lg:order-none">
                <h2 class="section-title">Quick Actions</h2>
                <p class="section-copy">Take action on this booking.</p>


                <div class="mt-5 grid gap-2.5">
                    @if($booking->isApproved())
                        @if($booking->isBackgroundCheckComplete())
                            <div class="rounded-lg bg-indigo-50 border border-indigo-200 p-3 text-sm text-indigo-800 font-semibold">{{ \App\Models\Setting::getValue('background_check_step_name', 'Background Check') }} completed {{ $booking->background_check_completed_at->format('M j, Y g:i A') }}</div>
                        @else
                            <form method="post" action="{{ route('admin.guests.background-check', $booking) }}">@csrf<button class="btn-secondary w-full gap-2"><x-icon name="shield-alert" class="h-4 w-4" />Mark {{ \App\Models\Setting::getValue('background_check_step_name', 'Background Check') }} Complete</button></form>
                        @endif
                    @endif
                    @if($booking->isBackgroundCheckComplete())
                        @if($booking->isDepositVerified())
                            <div class="rounded-lg bg-teal-50 border border-teal-200 p-3 text-sm text-teal-800 font-semibold">Deposit verified {{ $booking->deposit_verified_at->format('M j, Y g:i A') }}</div>
                        @else
                            <form method="post" action="{{ route('admin.guests.deposit-verified', $booking) }}" data-confirm-title="Mark Deposit Verified?" data-confirm="This will fully approve the guest. Before continuing, verify the guest has actually paid everything owed on / outside the platform: incidentals, parking, and early check-in (if applicable). This action does not check or record any of those payments itself.">@csrf<button class="btn-secondary w-full gap-2"><x-icon name="lock" class="h-4 w-4" />Mark Deposit Verified</button></form>
                        @endif
                    @endif
                    <form method="post" action="{{ route('admin.guests.override-gps', $booking) }}">@csrf<button class="btn-secondary w-full gap-2"><x-icon name="map" class="h-4 w-4" />Override GPS Verification</button></form>
                    <form method="post" action="{{ route('admin.guests.override', $booking) }}">@csrf<button class="btn-secondary w-full gap-2"><x-icon name="contact-guest-services" class="h-4 w-4" />Manually Mark Checked In</button></form>
                    <form method="post" action="{{ route('admin.guests.override-checkout', $booking) }}">@csrf<button class="btn-secondary w-full gap-2"><x-icon name="contact-guest-services" class="h-4 w-4" />Manually Mark Checked Out</button></form>
                    <form method="post" action="{{ route('admin.guests.mark-id', $booking) }}">@csrf<button class="btn-secondary w-full gap-2"><x-icon name="upload" class="h-4 w-4" />Mark Photo ID Received</button></form>
                    <form method="post" action="{{ route('admin.guests.bypass-vehicle-info', $booking) }}">@csrf<button class="btn-secondary w-full gap-2"><x-icon name="car" class="h-4 w-4" />Bypass Vehicle Info</button></form>

                    @if($booking->access_blocked_at)
                        <div class="rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-800">
                            <span class="font-semibold">Access blocked</span> since {{ $booking->access_blocked_at->format('M j, Y g:i A') }}
                            <p class="mt-1">{{ $booking->access_blocked_reason }}</p>
                        </div>
                        <form method="post" action="{{ route('admin.guests.unblock-access', $booking) }}">@csrf<button class="btn-secondary w-full gap-2"><x-icon name="unlock" class="h-4 w-4" />Restore Access</button></form>
                    @else
                        <button type="button" class="btn-danger w-full gap-2" onclick="document.getElementById('block-form-{{ $booking->id }}').classList.toggle('hidden')">
                            <x-icon name="alert-triangle" class="h-4 w-4" />Block Access
                        </button>
                        <form id="block-form-{{ $booking->id }}" method="post" action="{{ route('admin.guests.block-access', $booking) }}" class="hidden grid gap-2">
                            @csrf
                            <textarea name="access_blocked_reason" class="input" rows="3" placeholder="Reason (shown to guest)" required>{{ old('access_blocked_reason') }}</textarea>
                            <button class="btn-secondary w-full">Submit Block</button>
                        </form>
                    @endif
                </div>
            </section>

            <section class="card card-pad order-4 lg:order-none">
                <h2 class="section-title">Status Overview</h2>
                @php
                    $steps = [
                        'Email Received' => filled($booking->email),
                        'Photo ID Uploaded' => filled($booking->photo_id_path),
                        'Photo ID Approval' => $booking->isApproved(),
                        \App\Models\Setting::getValue('background_check_step_name', 'Background Check') => $booking->isBackgroundCheckComplete(),
                        'Deposit Verified' => $booking->isDepositVerified(),
                        'GPS Verified' => $booking->gps_verified,
                        'Currently Hosting' => $booking->isCheckedIn(),
                        'Checked Out' => filled($booking->checked_out_at),
                    ];
                    $progress = round((count(array_filter($steps)) / count($steps)) * 100);
                @endphp
                <p class="section-copy">Overall progress</p>
                <div class="mt-2 flex items-center gap-3">
                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-500" style="width: {{ $progress }}%"></div></div>
                    <span class="text-sm font-semibold text-slate-700">{{ $progress }}%</span>
                </div>
                <div class="mt-5 grid gap-1 text-sm">
                    @foreach($steps as $label => $done)
                        <div class="flex items-center justify-between border-b border-slate-100 py-2.5 last:border-0"><span class="text-slate-700">{{ $label }}</span><span class="badge {{ $done ? 'badge-active' : 'badge-pending' }}">{{ $done ? 'Done' : 'Pending' }}</span></div>
                    @endforeach
                </div>
            </section>

            <section class="card card-pad order-5 lg:order-none">
                <h2 class="section-title">Preview Guest Flow</h2>
                <p class="section-copy">Open any guest state without changing the real status.</p>
                <div class="mt-4 grid gap-2">
                    @foreach(['identity' => 'Pre Check-In', 'waiting' => 'Waiting', 'arrival' => 'Check-In Day', 'guide' => 'Welcome Guide', 'checkout' => 'Checkout Day'] as $state => $label)
                        <a class="btn-secondary justify-start" href="{{ route('admin.guests.preview', [$booking, $state]) }}" target="_blank">{{ $label }}</a>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>

    {{-- Guest progress timeline (always last, full width) --}}
    <section class="mt-6 card card-pad">
        <h2 class="section-title">Guest Progress Timeline</h2>
        @php
            $timelineSteps = [
                ['properties', 'Guest Created', $booking->created_at, true],
                ['mail', 'Email Submitted', $booking->updated_at, filled($booking->email)],
                ['upload', 'Photo ID Uploaded', $booking->updated_at, filled($booking->photo_id_path)],
                ['security', 'Photo ID Approval', $booking->approved_at, $booking->isApproved()],
                ['shield-alert', \App\Models\Setting::getValue('background_check_step_name', 'Background Check'), $booking->background_check_completed_at, $booking->isBackgroundCheckComplete()],
                ['lock', 'Deposit Verified', $booking->deposit_verified_at, $booking->isDepositVerified()],
                ['map', 'GPS Verified', $booking->checked_in_at, $booking->gps_verified],
                ['contact-guest-services', 'Currently Hosting', $booking->checked_in_at, $booking->isCheckedIn()],
                ['contact-guest-services', 'Checked Out', $booking->checked_out_at, filled($booking->checked_out_at)],
            ];
        @endphp
        <div class="mt-8 flex items-start overflow-x-auto pb-2">
            @foreach($timelineSteps as $i => [$icon, $label, $time, $done])
                @if($i > 0)
                    <div class="mt-7 h-px w-10 flex-shrink-0 sm:w-16 {{ $done ? 'bg-emerald-400' : 'border-t-2 border-dashed border-amber-300 bg-transparent' }}"></div>
                @endif
                <div class="flex w-28 flex-shrink-0 flex-col items-center text-center sm:w-32">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full border-2 {{ $done ? 'border-emerald-400 bg-emerald-50 text-emerald-600' : 'border-amber-300 bg-amber-50 text-amber-500' }}">
                        <x-icon :name="$icon" class="h-5 w-5" />
                    </span>
                    <p class="mt-3 text-sm font-semibold text-slate-950">{{ $label }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $done ? ($time ? $time->format('M j, Y g:i A') : 'Completed') : 'Pending' }}</p>
                    <span class="mt-2 badge {{ $done ? 'badge-active' : 'badge-pending' }}">{{ $done ? 'Done' : 'Open' }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Media Picker Modal (for editor image insert) --}}
    <div id="media-picker-modal" class="fixed inset-0 hidden items-center justify-center bg-slate-950/40 p-4" style="z-index:2147483000;">
        <div class="w-full max-w-2xl rounded-xl bg-white p-5 shadow-xl">
            <div class="mb-3 flex items-center justify-between gap-3">
                <p id="media-picker-breadcrumb" class="text-sm font-bold text-slate-700">Library</p>
                <div class="flex items-center gap-2">
                    <label class="btn-secondary cursor-pointer text-xs">
                        Upload
                        <input type="file" id="media-picker-upload-input" accept="image/*" class="sr-only">
                    </label>
                    <button type="button" onclick="closeMediaPickerForEditor()" class="text-slate-400 hover:text-slate-700">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>
            </div>
            <div id="media-picker-body" class="grid max-h-96 grid-cols-3 gap-3 overflow-y-auto sm:grid-cols-4"></div>
        </div>
    </div>

    {{-- Photo ID Viewer Modal --}}
    <div id="photo-id-modal" tabindex="-1" class="fixed inset-0 hidden items-center justify-center bg-slate-950/40 p-4" style="z-index:2147483000;">
        <div class="w-full max-w-2xl rounded-xl bg-white p-5 shadow-xl">
            <div class="mb-3 flex items-center justify-between gap-3">
                <p id="photo-id-modal-title" class="text-sm font-bold text-slate-700">Photo ID</p>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="photoIdZoomOut()" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-500 hover:bg-slate-50">&minus;</button>
                    <span id="photo-id-modal-zoom-level" class="w-10 text-center text-xs font-semibold text-slate-500">100%</span>
                    <button type="button" onclick="photoIdZoomIn()" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-500 hover:bg-slate-50">+</button>
                    <button type="button" onclick="photoIdZoomReset()" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-500 hover:bg-slate-50">Reset</button>
                    <button type="button" onclick="closePhotoIdModal()" class="text-slate-400 hover:text-slate-700">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>
            </div>
            <div id="photo-id-modal-viewport" class="max-h-[75vh] w-full overflow-hidden rounded-lg bg-slate-100" style="cursor: grab;">
                <img id="photo-id-modal-img" src="" alt="" class="h-full w-full select-none object-contain" style="transform-origin: center center; transition: transform 0.08s ease-out; user-select:none; -webkit-user-drag:none;" draggable="false">
            </div>

            @if(($booking->photo_id_path || $booking->photo_id_back_path) && !$booking->isApproved())
                <div class="mt-4 flex gap-2 border-t border-slate-100 pt-4">
                    <form method="post" action="{{ route('admin.guests.approve', $booking) }}" class="flex-1">@csrf<button class="btn-primary w-full gap-2"><x-icon name="check" class="h-4 w-4" />Approve</button></form>
                    <button type="button" class="btn-danger flex-1 gap-2" onclick="document.getElementById('decline-form-{{ $booking->id }}').classList.toggle('hidden'); closePhotoIdModal();"><x-icon name="x" class="h-4 w-4" />Decline</button>
                </div>
            @endif
        </div>
    </div>

    {{-- Force TinyMCE's floating menus/dropdowns/overflow drawer below our modal --}}
    <style>
    .tox-tinymce-aux,
    .tox.tox-silver-sink,
    .tox-dialog-wrap {
        z-index: 1000 !important;
    }
    .tox-menu.tox-collection.tox-collection--list {
        max-height: 320px !important;
        overflow-y: auto !important;
    }
    .tox.tox-tinymce.tox-fullscreen,
    body.tox-fullscreen-body .tox.tox-tinymce.tox-fullscreen {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 2147483001 !important;
    }
    </style>

    {{-- Google Fonts loaded on the PAGE so toolbar dropdown labels render correctly --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;700&family=Montserrat:wght@400;700&family=Merriweather:wght@400;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">

    <script>
    let __mediaPickerCurrentFolder = null;

    let __photoIdZoom = 1;
    let __photoIdPanX = 0;
    let __photoIdPanY = 0;
    let __photoIdDragging = false;
    let __photoIdDragStartX = 0;
    let __photoIdDragStartY = 0;
    const PHOTO_ID_ZOOM_MIN = 1;
    const PHOTO_ID_ZOOM_MAX = 4;
    const PHOTO_ID_ZOOM_STEP = 0.25;

    function __photoIdApplyTransform() {
        const img = document.getElementById('photo-id-modal-img');
        img.style.transform = `translate(${__photoIdPanX}px, ${__photoIdPanY}px) scale(${__photoIdZoom})`;
        document.getElementById('photo-id-modal-zoom-level').textContent = Math.round(__photoIdZoom * 100) + '%';
        const viewport = document.getElementById('photo-id-modal-viewport');
        viewport.style.cursor = __photoIdZoom > 1 ? 'grab' : 'default';
    }

    function __photoIdClampPan() {
        if (__photoIdZoom <= 1) {
            __photoIdPanX = 0;
            __photoIdPanY = 0;
            return;
        }
        const viewport = document.getElementById('photo-id-modal-viewport');
        const maxPanX = (viewport.clientWidth * (__photoIdZoom - 1)) / 2;
        const maxPanY = (viewport.clientHeight * (__photoIdZoom - 1)) / 2;
        __photoIdPanX = Math.max(-maxPanX, Math.min(maxPanX, __photoIdPanX));
        __photoIdPanY = Math.max(-maxPanY, Math.min(maxPanY, __photoIdPanY));
    }

    function photoIdZoomIn() {
        __photoIdZoom = Math.min(PHOTO_ID_ZOOM_MAX, __photoIdZoom + PHOTO_ID_ZOOM_STEP);
        __photoIdClampPan();
        __photoIdApplyTransform();
    }

    function photoIdZoomOut() {
        __photoIdZoom = Math.max(PHOTO_ID_ZOOM_MIN, __photoIdZoom - PHOTO_ID_ZOOM_STEP);
        __photoIdClampPan();
        __photoIdApplyTransform();
    }

    function photoIdZoomReset() {
        __photoIdZoom = 1;
        __photoIdPanX = 0;
        __photoIdPanY = 0;
        __photoIdApplyTransform();
    }

    function openPhotoIdModal(url, title) {
        document.getElementById('photo-id-modal-img').src = url;
        document.getElementById('photo-id-modal-title').textContent = title;
        const modal = document.getElementById('photo-id-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        modal.focus();
        modal.scrollIntoView({ behavior: 'instant', block: 'center' });
        photoIdZoomReset();
    }

    function closePhotoIdModal() {
        const modal = document.getElementById('photo-id-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('photo-id-modal-img').src = '';
        document.body.style.overflow = '';
        photoIdZoomReset();
    }

    (function initPhotoIdZoomInteractions() {
        const viewport = document.getElementById('photo-id-modal-viewport');
        const img = document.getElementById('photo-id-modal-img');

        viewport.addEventListener('wheel', function (e) {
            if (document.getElementById('photo-id-modal').classList.contains('hidden')) return;
            e.preventDefault();
            if (e.deltaY < 0) {
                __photoIdZoom = Math.min(PHOTO_ID_ZOOM_MAX, __photoIdZoom + PHOTO_ID_ZOOM_STEP);
            } else {
                __photoIdZoom = Math.max(PHOTO_ID_ZOOM_MIN, __photoIdZoom - PHOTO_ID_ZOOM_STEP);
            }
            __photoIdClampPan();
            __photoIdApplyTransform();
        }, { passive: false });

        viewport.addEventListener('mousedown', function (e) {
            if (__photoIdZoom <= 1) return;
            __photoIdDragging = true;
            __photoIdDragStartX = e.clientX - __photoIdPanX;
            __photoIdDragStartY = e.clientY - __photoIdPanY;
            viewport.style.cursor = 'grabbing';
        });

        window.addEventListener('mousemove', function (e) {
            if (!__photoIdDragging) return;
            __photoIdPanX = e.clientX - __photoIdDragStartX;
            __photoIdPanY = e.clientY - __photoIdDragStartY;
            __photoIdClampPan();
            __photoIdApplyTransform();
        });

        window.addEventListener('mouseup', function () {
            if (!__photoIdDragging) return;
            __photoIdDragging = false;
            viewport.style.cursor = __photoIdZoom > 1 ? 'grab' : 'default';
        });

        img.addEventListener('dblclick', function () {
            if (__photoIdZoom > 1) {
                photoIdZoomReset();
            } else {
                __photoIdZoom = 2;
                __photoIdApplyTransform();
            }
        });
    })();

    
    function switchPhotoIdTab(side) {
        const frontPanel = document.getElementById('photo-id-panel-front');
        const backPanel = document.getElementById('photo-id-panel-back');
        const frontTab = document.getElementById('photo-id-tab-front');
        const backTab = document.getElementById('photo-id-tab-back');
        const activeClasses = ['border-b-2', 'border-teal-700', 'text-teal-800'];

        if (side === 'front') {
            if (frontPanel) frontPanel.classList.remove('hidden');
            if (backPanel) backPanel.classList.add('hidden');
            if (frontTab) frontTab.classList.add(...activeClasses);
            if (backTab) backTab.classList.remove(...activeClasses);
        } else {
            if (backPanel) backPanel.classList.remove('hidden');
            if (frontPanel) frontPanel.classList.add('hidden');
            if (backTab) backTab.classList.add(...activeClasses);
            if (frontTab) frontTab.classList.remove(...activeClasses);
        }
    }

    function setCommunicationDefaultState() {
        const body = document.getElementById('communication-body');
        const chevron = document.getElementById('communication-chevron');
        if (!body || !chevron) return;
        const isDesktop = window.matchMedia('(min-width: 1024px)').matches;
        if (isDesktop) {
            body.classList.remove('hidden');
            chevron.classList.add('rotate-90');
        } else {
            body.classList.add('hidden');
            chevron.classList.remove('rotate-90');
        }
    }
    document.addEventListener('DOMContentLoaded', setCommunicationDefaultState);

    function toggleCommunicationSection() {
        const body = document.getElementById('communication-body');
        const chevron = document.getElementById('communication-chevron');
        body.classList.toggle('hidden');
        chevron.classList.toggle('rotate-90');
    }

    function closeMediaPickerForEditor() {
        const modal = document.getElementById('media-picker-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openMediaPickerForEditor() {
        const modal = document.getElementById('media-picker-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        loadMediaPickerForEditor(null);
    }

    function loadMediaPickerForEditor(folderId) {
        __mediaPickerCurrentFolder = folderId;
        const url = '{{ route("admin.media.picker") }}' + (folderId ? '?folder_id=' + folderId : '');
        fetch(url).then(r => r.json()).then(data => {
            const body = document.getElementById('media-picker-body');
            const crumbText = data.breadcrumb.length ? data.breadcrumb.map(c => c.name).join(' / ') : 'Library';
            document.getElementById('media-picker-breadcrumb').textContent = crumbText;
            body.innerHTML = '';
            if (folderId !== null) {
                const up = document.createElement('button');
                up.type = 'button';
                up.className = 'col-span-full text-left text-xs font-semibold text-blue-600 hover:underline';
                up.textContent = 'Back';
                const parentId = data.breadcrumb.length > 1 ? data.breadcrumb[data.breadcrumb.length - 2].id : null;
                up.onclick = () => loadMediaPickerForEditor(parentId);
                body.appendChild(up);
            }
            data.folders.forEach(folder => {
                const el = document.createElement('button');
                el.type = 'button';
                el.className = 'flex flex-col items-center gap-1 rounded-lg border border-slate-200 p-3 hover:bg-slate-50';
                el.innerHTML = '<span class="text-xs font-semibold">' + folder.name + '</span>';
                el.onclick = () => loadMediaPickerForEditor(folder.id);
                body.appendChild(el);
            });
            data.files.forEach(file => {
                const el = document.createElement('button');
                el.type = 'button';
                el.className = 'overflow-hidden rounded-lg border border-slate-200 bg-slate-50 hover:ring-2 hover:ring-blue-400';
                el.innerHTML = '<img src="' + file.url + '" class="h-20 w-full object-contain p-1">';
                el.onclick = () => {
                    if (window.tinymce && tinymce.activeEditor) {
                        tinymce.activeEditor.insertContent('<img src="' + file.url + '" alt="' + (file.name || '') + '" style="max-width:100%;">');
                    }
                    closeMediaPickerForEditor();
                };
                body.appendChild(el);
            });
            if (!data.folders.length && !data.files.length) {
                body.innerHTML += '<p class="col-span-full text-center text-sm text-slate-400">No images in this folder yet.</p>';
            }
        });
    }

    document.getElementById('media-picker-upload-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('image', file);
        if (__mediaPickerCurrentFolder) formData.append('media_folder_id', __mediaPickerCurrentFolder);
        fetch('{{ route("admin.media.files.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData,
        }).then(() => {
            loadMediaPickerForEditor(__mediaPickerCurrentFolder);
            e.target.value = '';
        });
    });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
    <script>
    tinymce.init({
        relative_urls: false,
        remove_script_host: false,
        selector: '#welcome-message-editor',
        plugins: 'lists advlist link code table searchreplace wordcount visualblocks charmap emoticons preview anchor fullscreen nonbreaking',
        toolbar: 'undo redo | bold italic underline forecolor backcolor | alignleft aligncenter alignright | bullist numlist | customlineheight | link insertimage table anchor charmap emoticons | searchreplace preview fullscreen | removeformat code | fontfamily fontsize',
        browser_spellcheck: true,
        contextmenu: false,
        font_size_formats: '8px 10px 12px 14px 16px 18px 20px 24px 28px 32px 36px 42px 48px 60px 72px',
        font_family_formats:
            'Arial=arial,helvetica,sans-serif;' +
            'Helvetica=helvetica,arial,sans-serif;' +
            'Times New Roman=times new roman,times,serif;' +
            'Georgia=georgia,palatino,serif;' +
            'Garamond=garamond,serif;' +
            'Verdana=verdana,geneva,sans-serif;' +
            'Tahoma=tahoma,arial,helvetica,sans-serif;' +
            'Trebuchet MS=trebuchet ms,geneva,sans-serif;' +
            'Courier New=courier new,courier,monospace;' +
            'Comic Sans MS=comic sans ms,sans-serif;' +
            'Impact=impact,sans-serif;' +
            'Lucida Sans=lucida sans unicode,lucida grande,sans-serif;' +
            'Roboto=Roboto,arial,sans-serif;' +
            'Open Sans=\'Open Sans\',arial,sans-serif;' +
            'Montserrat=Montserrat,arial,sans-serif;' +
            'Merriweather=Merriweather,georgia,serif;' +
            'Playfair Display=\'Playfair Display\',georgia,serif',
        color_map: [
            '000000','Black', '424242','Dark Gray', '757575','Gray', 'BDBDBD','Light Gray', 'FFFFFF','White',
            'B71C1C','Dark Red', 'E53935','Red', 'F44336','Bright Red', 'FF7043','Orange Red', 'FB8C00','Orange',
            'FDD835','Yellow', 'C0CA33','Olive', '7CB342','Light Green', '43A047','Green', '00897B','Teal',
            '00ACC1','Cyan', '1E88E5','Blue', '3949AB','Indigo', '5E35B1','Purple', '8E24AA','Magenta', 'D81B60','Pink'
        ],
        valid_styles: {
            '*': 'font-size,font-family,color,background-color,text-align,text-decoration,line-height'
        },
        menubar: false,
        toolbar_mode: 'wrap',
        height: 320,
        ui_mode: 'split',
        promotion: false,
        branding: false,
        content_css: false,
        content_style: `
            @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;700&family=Montserrat:wght@400;700&family=Merriweather:wght@400;700&family=Playfair+Display:wght@400;700&display=swap');
            body { font-family: Helvetica, Arial, sans-serif; font-size: 14px; }
            p { margin: 0; }
        `,
        setup: function(editor) {
            var lineHeightValues = ['0.3', '0.5', '0.7', '0.9', '1', '1.15', '1.3', '1.5', '1.75', '2', '2.5', '3', '3.5', '4', '4.5', '5'];
            var lastSelectionRange = null;
            editor.on('NodeChange KeyUp MouseUp', function() {
                try { lastSelectionRange = editor.selection.getRng().cloneRange(); } catch (e) {}
            });
            editor.ui.registry.addMenuButton('customlineheight', {
                icon: 'line-height',
                tooltip: 'Line height',
                fetch: function(callback) {
                    var currentValue = null;
                    try {
                        var node0 = editor.selection.getNode();
                        var block0 = editor.dom.getParent(node0, editor.dom.isBlock) || node0;
                        if (block0 && block0.nodeName !== 'BODY') {
                            currentValue = editor.dom.getStyle(block0, 'line-height') || null;
                        }
                    } catch (e) {}
                    var items = lineHeightValues.map(function(v) {
                        return {
                            type: 'togglemenuitem',
                            text: v,
                            active: currentValue === v,
                            onAction: function() {
                                editor.focus();
                                if (lastSelectionRange) {
                                    try { editor.selection.setRng(lastSelectionRange); } catch (e) {}
                                }
                                var blocks = editor.selection.getSelectedBlocks();
                                if (!blocks || !blocks.length) {
                                    var node = editor.selection.getNode();
                                    var single = editor.dom.getParent(node, editor.dom.isBlock) || node;
                                    blocks = [single];
                                }
                                var applied = 0;
                                blocks.forEach(function(block) {
                                    if (block && block.nodeName !== 'BODY') {
                                        editor.dom.setStyle(block, 'line-height', v);
                                        applied++;
                                    }
                                });
                                if (applied > 0) {
                                    editor.nodeChanged();
                                }
                            }
                        };
                    });
                    callback(items);
                }
            });
            editor.ui.registry.addButton('insertimage', {
                icon: 'image',
                tooltip: 'Insert image from library',
                onAction: function() {
                    openMediaPickerForEditor();
                }
            });
        }
    });
    </script>
</x-admin-layout>
