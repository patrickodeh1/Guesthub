<x-guest-layout :booking="$booking" :property="$property" :title="$property->name" :state="$state">
@php
    $categories = isset($categories) ? $categories : collect();
    $checkinSteps = isset($checkinSteps) ? $checkinSteps : [];
    $checkoutSteps = isset($checkoutSteps) ? $checkoutSteps : [];
    $parkingSteps = isset($parkingSteps) ? $parkingSteps : [];
    $heroImg = $property->heroImageUrl();
    $welcomeMessageClean = $welcomeMessage ?? '';
    $siteLogo = \App\Models\Setting::getValue('site_logo');
    $categoryColor = ['#eef2ff', '#3b65ce'];
    $guideCats = $categories;
@endphp

@if(! empty($previewMode))
    <div class="mb-3 rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-800">
        Admin preview - state: {{ $state }}
    </div>
@endif

<section class="phone-frame">
    <div class="phone-screen">
        <div class="ios-status">
            <span>9:41</span>
            <span class="ios-notch"></span>
            <span class="ios-signal">cell wifi battery</span>
        </div>

        @if($state === 'access_blocked')
            <div class="guest-portal-card">
                <div class="guest-status-bar">
                    <div>
                        @if($siteLogo)
                            <img src="{{ url('/img/'.$siteLogo) }}" alt="" class="h-8 max-w-[140px] w-auto object-contain">
                        @endif
                    </div>
                    <span class="guest-status-pill" style="background:#fef2f2;color:#991b1b;">
                        <x-icon name="alert-triangle" class="h-4 w-4" />
                        Access blocked
                    </span>
                </div>
                <div class="flex flex-col items-center justify-center gap-4 px-6 py-16 text-center md:py-24">
                    <h1 class="guest-status-title">Access unavailable</h1>
                    <p class="max-w-md text-sm leading-6 text-slate-600">{{ $booking->access_blocked_reason }}</p>
                </div>
            </div>
        @elseif($state === 'identity' && $booking->isIdentityComplete() && $booking->photo_id_received && ($booking->needsIdApproval() || ! $booking->isBackgroundCheckComplete()))
            <div data-poll-id-status="{{ route('guest.id-status', [$booking->booking_id, $booking->token]) }}" data-poll-fields="id_approved,background_check_complete"></div>
            <div class="guest-portal-card">
                <div class="guest-status-bar">
                    <div>
                        @if($siteLogo)
                            <img src="{{ url('/img/'.$siteLogo) }}" alt="" class="h-8 max-w-[140px] w-auto object-contain">
                        @endif
                    </div>
                </div>
                <img src="{{ $heroImg }}" alt="{{ $property->name }}" class="w-full block rounded-xl mt-4">
                <div class="p-6 md:p-10 text-center">
                    <h1 class="guest-status-title">{{ $backgroundCheckStepName }}</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $backgroundCheckStepInstructions }}</p>
                </div>
            </div>
        @elseif($state === 'identity')
            <div class="guest-portal-card">
                <div class="guest-status-bar">
                    <div>
                        @if($siteLogo)
                            <img src="{{ url('/img/'.$siteLogo) }}" alt="" class="h-8 max-w-[140px] w-auto object-contain">
                        @endif
                    </div>
                    <span class="guest-status-pill">
                        <x-icon name="alert-triangle" class="h-4 w-4" />
                        Not checked in
                    </span>
                </div>
                {{-- Step indicator: big circled current step, dash-separated others. Hidden on welcome (step 0). --}}
                <div class="px-6 pt-5 step-indicator hidden" id="step-indicator-wrapper">
                    <span class="step-num" data-num="1" id="step-num-1">1</span>
                    <span class="step-dash">-</span>
                    <span class="step-num" data-num="2" id="step-num-2">2</span>
                    <span class="step-dash">-</span>
                    <span class="step-num" data-num="3" id="step-num-3">3</span>
                </div>
            </div>

            <div class="guest-portal-card mt-4">
                <img src="{{ $heroImg }}" alt="{{ $property->name }}" class="w-full block rounded-xl">
            </div>

            <div class="guest-portal-card mt-4">
                <form id="guest-booking-form" method="post" data-skip-loading enctype="multipart/form-data" action="{{ route('guest.identity', [$booking->booking_id, $booking->token]) }}" class="guest-booking-card">
                    @csrf

                    {{-- ══════════════════ STEP 1 — Welcome + Booking details (read-only) ══════════════════ --}}
                    <div class="idw-step" data-step="0">
                        <div class="px-0 pb-2">
                            <h2 class="text-xl font-extrabold text-slate-950">Welcome, {{ $booking->guest_first_name ?: explode(' ', trim($booking->guest_name))[0] }}!</h2>
                        </div>
                        <div class="guest-stay-grid mt-5">
                            <div class="guest-stay-tile">
                                <div class="guest-stay-tile-icon">
                                    <x-icon name="calendar" class="h-5 w-5" />
                                </div>
                                <p class="guest-stay-tile-label">Check-In</p>
                                <p class="guest-stay-tile-date">{{ $booking->check_in_date->format('M d, Y') }}</p>
                            </div>
                            <div class="guest-stay-tile">
                                <div class="guest-stay-tile-icon">
                                    <x-icon name="calendar" class="h-5 w-5" />
                                </div>
                                <p class="guest-stay-tile-label">Check-Out</p>
                                <p class="guest-stay-tile-date">{{ $booking->check_out_date->format('M d, Y') }} {{ $booking->nightsLabel() }}</p>
                            </div>
                        </div>
                        @php
                            $isRegistrationComplete = filled($booking->guest_name)
                                && filled($booking->email)
                                && filled($booking->phone)
                                && ! is_null($booking->parking_needed)
                                && $booking->photo_id_received;
                        @endphp
                        @if($isRegistrationComplete)
                            <button type="button" class="guest-primary-btn guest-primary-btn-lg is-go mt-6 w-full" onclick="document.getElementById('welcome-modal').classList.remove('hidden')">
                                Begin Check In
                                <x-icon name="arrow-right" class="h-5 w-5 ml-1 inline-block align-middle" />
                            </button>
                        @else
                            <button type="button" class="guest-primary-btn guest-primary-btn-lg mt-6 w-full" onclick="document.getElementById('welcome-modal').classList.remove('hidden')">
                                Begin Pre-Checkin
                                <x-icon name="arrow-right" class="h-5 w-5 ml-1 inline-block align-middle" />
                            </button>
                        @endif

                        {{-- Welcome message modal --}}
                        <div id="welcome-modal" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 p-4">
                            <div class="bg-white rounded-2xl max-w-md w-full max-h-[80vh] overflow-y-auto p-6">
                                <div class="text-sm leading-6 text-slate-600">{!! $welcomeMessageClean !!}</div>
                                <button type="button" class="guest-primary-btn guest-primary-btn-lg mt-6 w-full" data-next="1" onclick="document.getElementById('welcome-modal').classList.add('hidden')">
                                    I Agree
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- ══════════════════ STEP 2 — Phone, Email, Parking, Check-in time ══════════════════ --}}
                    <div class="idw-step hidden" data-step="1">
                        {{-- Name --}}
                        <div class="mt-5" id="name-display-block">
                            <p class="text-sm font-bold">Name</p>
                            <div class="mt-2 flex items-center justify-between guest-input" style="cursor:default">
                                <span>{{ $booking->guest_name }}</span>
                                <button type="button" id="name-edit-pencil" class="text-slate-400 hover:text-slate-600" title="Edit name">
                                    <x-icon name="edit" class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <label class="mt-5 hidden block text-sm font-bold" id="name-input-block">
                            Name
                            <input name="guest_name" type="text" value="{{ old('guest_name', $booking->guest_name) }}" placeholder="Full name" autocomplete="name" required class="guest-input mt-2">
                        </label>
                        {{-- Phone --}}
                        @if($booking->phone)
                        <div class="mt-5" id="phone-display-block">
                            <p class="text-sm font-bold">Phone number</p>
                            <div class="mt-2 flex items-center justify-between guest-input" style="cursor:default">
                                <span>{{ $booking->phone }}</span>
                                <button type="button" id="phone-edit-pencil" class="text-slate-400 hover:text-slate-600" title="Edit phone number">
                                    <x-icon name="edit" class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <label class="mt-5 hidden block text-sm font-bold" id="phone-input-block">
                            Phone number
                            <input name="phone" type="tel" value="{{ old('phone', $booking->phone) }}" placeholder="+1 555 000 0000" autocomplete="tel" class="guest-input mt-2">
                        </label>
                        @else
                        <label class="mt-5 block text-sm font-bold">
                            Phone number
                            <input name="phone" type="tel" value="{{ old('phone') }}" placeholder="+1 555 000 0000" autocomplete="tel" required class="guest-input mt-2">
                        </label>
                        @endif

                        {{-- Email --}}
                        @if($booking->email)
                        <div class="mt-7" id="email-display-block">
                            <p class="text-sm font-bold">Email address</p>
                            <div class="mt-2 flex items-center justify-between guest-input" style="cursor:default">
                                <span>{{ $booking->email }}</span>
                                <button type="button" id="email-edit-pencil" class="text-slate-400 hover:text-slate-600" title="Edit email address">
                                    <x-icon name="edit" class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <label class="mt-7 hidden block text-sm font-bold" id="email-input-block">
                            Email address
                            <input name="email" type="email" value="{{ old('email', $booking->email) }}" placeholder="name@example.com" autocomplete="email" class="guest-input @error('email') border-red-400 @enderror" aria-describedby="@error('email') email-error @enderror">
                            @error('email')
                                <span id="email-error" class="guest-field-error">{{ $message }}</span>
                            @enderror
                        </label>
                        @else
                        <label class="mt-7 block text-sm font-bold">
                            Email address
                            <input name="email" type="email" value="{{ old('email') }}" required placeholder="name@example.com" autocomplete="email" class="guest-input @error('email') border-red-400 @enderror" aria-describedby="@error('email') email-error @enderror">
                            @error('email')
                                <span id="email-error" class="guest-field-error">{{ $message }}</span>
                            @enderror
                        </label>
                        @endif

                        {{-- Parking --}}
                        @if(is_null($booking->parking_needed))
                        <div class="mt-5">
                            <p class="text-sm font-bold">Will You Have A Vehicle?</p>
                            <div id="parking-question-block" class="mt-3 grid grid-cols-2 gap-3">
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm font-semibold hover:bg-slate-50">
                                    <input type="radio" name="parking_needed" value="1" class="accent-blue-600">
                                    Yes I Need Parking
                                </label>
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm font-semibold hover:bg-slate-50">
                                    <input type="radio" name="parking_needed" value="0" class="accent-blue-600">
                                    No I Will Not Need Parking
                                </label>
                            </div>
                            <span id="parking-error" class="guest-field-error" style="display:none">Please let us know if you'll be parking.</span>
                        </div>
                        @endif

                        {{-- Vehicle info, task 34: collected when parking is (or becomes) "yes" --}}
                        <div id="vehicle-info-block" class="mt-5" style="{{ $booking->parking_needed ? '' : 'display:none' }}">
                            <label class="block text-sm font-bold">
                                Vehicle make and model
                                <input name="vehicle_make_model" value="{{ old('vehicle_make_model', $booking->vehicle_make_model) }}" placeholder="e.g. Toyota Camry" class="guest-input mt-2">
                            </label>
                            <label class="mt-4 block text-sm font-bold">
                                Photo of your license plate
                                @if($booking->license_plate_photo_path)
                                    <span class="mt-2 block text-xs font-semibold text-emerald-700">A license plate photo is already on file. Choose a new one below to replace it.</span>
                                @endif
                                <input type="file" name="license_plate_photo" accept="image/*" capture="environment" class="guest-input mt-2">
                            </label>
                            <span id="vehicle-error" class="guest-field-error" style="display:none">Please add your vehicle's make/model and a photo of your license plate.</span>
                        </div>

                        {{-- Check-in time --}}
                        <div class="mt-5">
                            <label class="text-sm font-bold">What time are you planning to check in? <span class="text-red-600">*</span>
                                <select name="checkin_time_preference" id="checkin_time_preference_select" class="guest-input mt-2 @error('checkin_time_preference') border-red-400 @enderror" aria-describedby="checkin-time-error">
                                    <option value="" disabled {{ old('checkin_time_preference', $booking->checkin_time_preference) ? '' : 'selected' }}>Select a time</option>
                                    @foreach($checkinTimeOptions as $value => $label)
                                        <option value="{{ $value }}" {{ old('checkin_time_preference', $booking->checkin_time_preference) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <span id="checkin-time-error" class="guest-field-error" style="display:@error('checkin_time_preference')block @else none @enderror">
                                @error('checkin_time_preference'){{ $message }}@else Please select a check-in time. @enderror
                            </span>
                            <p class="mt-1 text-xs text-slate-400">Check in time is 4pm, we will try our best to accommodate early check in if desired, and then update you if available.</p>
                        </div>

                        {{-- Check-out time --}}
                        <div class="mt-5">
                            <label class="text-sm font-bold">What time are you planning to check out? <span class="text-red-600">*</span>
                                <select name="checkout_time_preference" id="checkout_time_preference_select" class="guest-input mt-2 @error('checkout_time_preference') border-red-400 @enderror" aria-describedby="checkout-time-error">
                                    <option value="" disabled {{ old('checkout_time_preference', $booking->checkout_time_preference) ? '' : 'selected' }}>Select a time</option>
                                    @foreach($checkoutTimeOptions as $value => $label)
                                        <option value="{{ $value }}" {{ old('checkout_time_preference', $booking->checkout_time_preference) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <span id="checkout-time-error" class="guest-field-error" style="display:@error('checkout_time_preference')block @else none @enderror">
                                @error('checkout_time_preference'){{ $message }}@else Please select a check-out time. @enderror
                            </span>
                            <p class="mt-1 text-xs text-slate-400">Check out time is 10am, we will try our best to accommodate late check out if desired, and then update you if available.</p>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <button type="button" class="guest-outline-btn w-full" data-prev="0">Back</button>
                            <button type="button" id="step1-next-btn" class="guest-primary-btn w-full">Next</button>
                        </div>
                    </div>

                    {{-- ══════════════════ STEP 2 — ID capture ══════════════════ --}}
                    <div class="idw-step hidden" data-step="2">
                        @if($booking->photo_id_received)
                        <div class="mt-5 text-center">
                            <div class="guest-big-check mx-auto">
                                <x-icon name="check" class="h-8 w-8" />
                            </div>
                            <p class="mt-4 font-semibold text-slate-950">ID already received</p>
                            <p class="mt-1 text-sm text-slate-500">No need to upload it again, you're all set to continue.</p>
                        </div>
                        @else
                        @php
                            // Only require (and show a capture tile for) a side that's actually
                            // missing — cleared by an admin decline, or never uploaded. A decline
                            // on one side should never re-prompt an already-approved other side.
                            $idwFrontRequired = blank($booking->photo_id_path);
                            $idwBackRequired = blank($booking->photo_id_back_path) && $booking->id_type !== 'passport';
                        @endphp
                        <div class="mt-5" id="id-capture-section">
                            @if($booking->photo_id_front_declined_reason)
                                <div class="guest-detail-banner mb-4" style="background:#fef3c7;border-color:#fde68a;">
                                    <span class="guest-detail-banner-icon" style="color:#92400e;">
                                        <x-icon name="alert-triangle" class="h-5 w-5" />
                                    </span>
                                    <div>
                                        <p class="guest-detail-banner-title" style="color:#92400e;">Front of ID needs to be re-uploaded</p>
                                        <p class="guest-detail-banner-sub">{{ $booking->photo_id_front_declined_reason }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($booking->photo_id_back_declined_reason)
                                <div class="guest-detail-banner mb-4" style="background:#fef3c7;border-color:#fde68a;">
                                    <span class="guest-detail-banner-icon" style="color:#92400e;">
                                        <x-icon name="alert-triangle" class="h-5 w-5" />
                                    </span>
                                    <div>
                                        <p class="guest-detail-banner-title" style="color:#92400e;">Back of ID needs to be re-uploaded</p>
                                        <p class="guest-detail-banner-sub">{{ $booking->photo_id_back_declined_reason }}</p>
                                    </div>
                                </div>
                            @endif
                            <p class="text-sm font-bold mb-3">Photo ID <span class="text-red-500">*</span></p>

                            <div id="idw-desktop-notice" class="hidden rounded-xl border border-slate-200 bg-slate-50 p-4 text-center">
                                <p class="text-sm font-bold text-slate-800">It's easier to snap this on your phone</p>
                                <p class="mt-1 text-xs text-slate-500">Scan the code below with your phone's camera to pick up right where you left off.</p>
                                <div id="idw-qr-canvas" class="mx-auto mt-4 flex items-center justify-center" style="width:180px;height:180px"></div>
                                <p class="mt-3 text-xs font-semibold text-slate-600">Or open this link on your phone:</p>
                                <p id="idw-qr-link" class="mt-1 break-all rounded-lg bg-white px-3 py-2 text-xs font-mono text-slate-700 border border-slate-200"></p>
                                <button type="button" id="idw-continue-desktop-btn" class="mt-4 text-xs font-semibold text-blue-600 underline">Don't have a mobile device? Continue on desktop</button>
                            </div>

                            <div id="idw-mobile-capture-ui">
                            <div id="camera-container" class="relative w-full rounded-xl overflow-hidden bg-black hidden" style="aspect-ratio:16/9">
                                <video id="camera-stream" class="w-full h-full object-cover" autoplay playsinline muted></video>
                                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                    <div class="w-[88%] rounded-lg border-4 border-white" style="aspect-ratio:1.586/1;box-shadow:0 0 0 9999px rgba(0,0,0,0.45)"></div>
                                </div>
                                <p id="camera-instruction-label" class="pointer-events-none absolute top-2 left-0 right-0 text-center text-white text-sm font-bold" style="text-shadow:0 1px 3px rgba(0,0,0,0.8)"></p>
                            </div>
                            <div id="capture-btn-wrapper" class="hidden mt-3 flex flex-col items-center gap-2">
                                <p id="idw-capture-status" class="text-sm font-semibold text-slate-700 text-center">Loading camera…</p>
                                <button type="button" id="capture-btn" class="hidden bg-slate-900 text-white rounded-full w-16 h-16 flex items-center justify-center shadow-xl border-4 border-white mx-auto">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                                </button>
                                <span id="capture-btn-fallback-label" class="hidden text-xs font-semibold text-slate-600">Tap to capture</span>
                            </div>
                            <div id="front-preview-block" class="hidden mt-3">
                                <p class="text-xs font-semibold text-slate-500 mb-1">Front of ID</p>
                                <img id="front-preview" class="w-full rounded-xl object-cover" style="max-height:180px">
                                <p id="front-blur-warning" class="mt-1 hidden text-xs font-semibold text-red-500">Image is blurry. Please retake.</p>
                                <button type="button" id="retake-front-btn" class="mt-2 text-xs font-semibold text-blue-600 underline">{{ $booking->id_type === 'passport' ? 'Retake' : 'Retake front' }}</button>
                            </div>
                            <div id="back-preview-block" class="hidden mt-3">
                                <p class="text-xs font-semibold text-slate-500 mb-1">Back of ID</p>
                                <img id="back-preview" class="w-full rounded-xl object-cover" style="max-height:180px">
                                <p id="back-blur-warning" class="mt-1 hidden text-xs font-semibold text-red-500">Image is blurry. Please retake.</p>
                                <button type="button" id="retake-back-btn" class="mt-2 text-xs font-semibold text-blue-600 underline">Retake back</button>
                            </div>
                            <div id="upload-zone-trigger" class="guest-upload guest-upload-id mt-3 cursor-pointer{{ $booking->id_type === 'passport' ? ' is-passport' : '' }}{{ $idwFrontRequired ? '' : ' hidden' }}" onclick="startCamera('front')">
                                @if($booking->id_type === 'passport')
                                <img src="{{ asset('id_icons/passportID.png') }}" alt="Passport example">
                                @else
                                <img src="{{ asset('id_icons/frontID.jpg') }}" alt="Front of ID example">
                                @endif
                            </div>
                            @if($booking->id_type === 'passport')
                            <p id="upload-zone-trigger-front-label" class="mt-2 text-center font-bold{{ $idwFrontRequired ? '' : ' hidden' }}">Tap to take photo of passport data page</p>
                            @else
                            <p id="upload-zone-trigger-front-label" class="mt-2 text-center font-bold{{ $idwFrontRequired ? '' : ' hidden' }}">Tap to take photo of front of ID</p>
                            @endif
                            <div id="upload-zone-trigger-back" class="guest-upload guest-upload-id mt-3 cursor-pointer{{ $idwBackRequired && ! $idwFrontRequired ? '' : ' hidden' }}" onclick="startCamera('back')">
                                <img src="{{ asset('id_icons/backID.jpg') }}" alt="Back of ID example">
                            </div>
                            <p id="upload-zone-trigger-back-label" class="mt-2 text-center font-bold{{ $idwBackRequired && ! $idwFrontRequired ? '' : ' hidden' }}">Tap to take photo of back of ID</p>
                            <input type="hidden" name="photo_id" id="photo-id-data">
                            <input type="hidden" name="photo_id_back" id="photo-id-back-data">
                            </div>
                        </div>
                        @endif

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <button type="button" class="guest-outline-btn w-full" data-prev="1">Back</button>
                            <button type="button" class="guest-primary-btn w-full" id="id-capture-next-btn">Next</button>
                        </div>
                        <p class="mt-3 text-center text-xs leading-5 text-slate-500">Your information is used only for secure check-in verification.</p>
                    </div>

                    {{-- ══════════════════ STEP 3 — Smart lock / August Home ══════════════════ --}}
                    <div class="idw-step hidden" data-step="3">
                        <div class="mt-5 text-center">
                            <p class="font-semibold text-slate-950">Smart Lock Access</p>
                            <div class="mt-1 text-sm text-slate-500">{!! \App\Models\Setting::getValue('lock_message', "If you'd like quicker access to the unit, you can download the August Home app.") !!}</div>
                        </div>
                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <button type="button" class="guest-outline-btn w-full" data-prev="2">Back</button>
                            <button type="button" id="smart-lock-continue-btn" class="guest-primary-btn w-full">Next</button>
                        </div>
                    </div>

                </form>

                <script>
                (function() {
                    var steps = document.querySelectorAll(".idw-step");
                    var stepNums = document.querySelectorAll(".step-num");

                    function goToStep(n) {
                        steps.forEach(function(s) {
                            s.classList.toggle("hidden", s.getAttribute("data-step") !== String(n));
                        });
                        stepNums.forEach(function(el) {
                            el.classList.toggle("is-current", el.getAttribute("data-num") === String(n));
                        });
                        var indicatorWrapper = document.getElementById("step-indicator-wrapper");
                        if (indicatorWrapper) {
                            indicatorWrapper.classList.toggle("hidden", String(n) === "0");
                        }
                        idwSaveState({ step: String(n) });
                    }
                    window.goToStep = goToStep;

                    var IDW_STORAGE_KEY = "idw_form_state_{{ $booking->booking_id }}";

                    function idwSaveState(partial) {
                        try {
                            var current = JSON.parse(sessionStorage.getItem(IDW_STORAGE_KEY) || "{}");
                            var merged = Object.assign(current, partial);
                            sessionStorage.setItem(IDW_STORAGE_KEY, JSON.stringify(merged));
                        } catch (_) {}
                    }
                    window.idwSaveState = idwSaveState;

                    function idwClearState() {
                        try { sessionStorage.removeItem(IDW_STORAGE_KEY); } catch (_) {}
                    }
                    window.idwClearState = idwClearState;

                    function idwRestoreState() {
                        var saved;
                        try { saved = JSON.parse(sessionStorage.getItem(IDW_STORAGE_KEY) || "null"); } catch (_) { saved = null; }
                        if (!saved) {
                            @if($booking->needsIdApproval() || $booking->guest_authenticated_at)
                                goToStep(2);
                            @endif
                            return false;
                        }

                        var fieldNames = ["guest_name", "phone", "email", "checkin_time_preference", "checkout_time_preference"];
                        fieldNames.forEach(function(name) {
                            if (saved[name] === undefined) return;
                            var input = document.querySelector('[name="' + name + '"]');
                            if (input) input.value = saved[name];
                        });
                        if (saved.parking_needed !== undefined) {
                            var radio = document.querySelector('input[name="parking_needed"][value="' + saved.parking_needed + '"]');
                            if (radio) radio.checked = true;
                        }
                        var photoIdEl = document.getElementById("photo-id-data");
                        if (saved.photo_id && photoIdEl) {
                            photoIdEl.value = saved.photo_id;
                            var frontImg = document.getElementById("front-preview");
                            var uploadTrigger = document.getElementById("upload-zone-trigger");
                            var uploadTriggerLabel = document.getElementById("upload-zone-trigger-front-label");
                            var uploadTriggerBack = document.getElementById("upload-zone-trigger-back");
                            var uploadTriggerBackLabel = document.getElementById("upload-zone-trigger-back-label");
                            if (frontImg) {
                                frontImg.src = saved.photo_id;
                                document.getElementById("front-preview-block").classList.remove("hidden");
                                if (uploadTrigger) uploadTrigger.classList.add("hidden");
                                if (uploadTriggerLabel) uploadTriggerLabel.classList.add("hidden");
                                if (idwBackRequired && (saved.photo_id_back || isPassportGlobal())) {
                                    if (uploadTriggerBack) uploadTriggerBack.classList.remove("hidden");
                                    if (uploadTriggerBackLabel) uploadTriggerBackLabel.classList.remove("hidden");
                                }
                            }
                        }
                        var photoIdBackEl = document.getElementById("photo-id-back-data");
                        if (saved.photo_id_back && photoIdBackEl) {
                            photoIdBackEl.value = saved.photo_id_back;
                            var backImg = document.getElementById("back-preview");
                            if (backImg) {
                                backImg.src = saved.photo_id_back;
                                document.getElementById("back-preview-block").classList.remove("hidden");
                            }
                        }
                        if (saved.step) {
                            goToStep(saved.step);
                        }
                        return true;
                    }

                    function isPassportGlobal() {
                        return document.getElementById("upload-zone-trigger") &&
                            document.getElementById("upload-zone-trigger").classList.contains("is-passport");
                    }

                    document.querySelectorAll("[data-next]:not(#id-capture-next-btn)").forEach(function(btn) {
                        btn.addEventListener("click", function() {
                            var step = btn.closest(".idw-step");
                            if (step && step.querySelector("input:invalid")) {
                                var invalid = step.querySelector("input:invalid");
                                invalid.reportValidity();
                                return;
                            }
                            if (step) {
                                var fieldChecks = [
                                    { name: "guest_name", label: "your name" },
                                    { name: "phone", label: "your phone number" },
                                    { name: "email", label: "your email address" }
                                ];
                                for (var fc = 0; fc < fieldChecks.length; fc++) {
                                    var visibleInputs = Array.prototype.filter.call(
                                        step.querySelectorAll('input[name="' + fieldChecks[fc].name + '"]'),
                                        function(el) { return el.offsetParent !== null; }
                                    );
                                    var activeInput = visibleInputs[0];
                                    if (activeInput && !activeInput.value.trim()) {
                                        activeInput.classList.add("border-red-400");
                                        activeInput.scrollIntoView({ behavior: "smooth", block: "center" });
                                        activeInput.focus();
                                        return;
                                    } else if (activeInput) {
                                        activeInput.classList.remove("border-red-400");
                                    }
                                }
                                var parkingGroup = step.querySelectorAll('input[name="parking_needed"]');
                                var parkingError = document.getElementById("parking-error");
                                if (parkingGroup.length) {
                                    var parkingChecked = Array.prototype.some.call(parkingGroup, function(r) { return r.checked; });
                                    if (!parkingChecked) {
                                        if (parkingError) parkingError.style.display = "block";
                                        var parkingBlock = document.getElementById("parking-question-block");
                                        if (parkingBlock) parkingBlock.scrollIntoView({ behavior: "smooth", block: "center" });
                                        return;
                                    } else if (parkingError) {
                                        parkingError.style.display = "none";
                                    }
                                }
                                var timeSelect = step.querySelector('#checkin_time_preference_select');
                                if (timeSelect && !timeSelect.value) {
                                    timeSelect.classList.add("border-red-400");
                                    var timeError = document.getElementById("checkin-time-error");
                                    if (timeError) timeError.style.display = "block";
                                    timeSelect.scrollIntoView({ behavior: "smooth", block: "center" });
                                    timeSelect.focus();
                                    return;
                                }
                                var checkoutSelect = step.querySelector('#checkout_time_preference_select');
                                if (checkoutSelect && !checkoutSelect.value) {
                                    checkoutSelect.classList.add("border-red-400");
                                    var checkoutError = document.getElementById("checkout-time-error");
                                    if (checkoutError) checkoutError.style.display = "block";
                                    checkoutSelect.scrollIntoView({ behavior: "smooth", block: "center" });
                                    checkoutSelect.focus();
                                    return;
                                }
                            }
                            goToStep(btn.getAttribute("data-next"));
                        });
                    });

                    document.querySelectorAll("[data-prev]").forEach(function(btn) {
                        btn.addEventListener("click", function() {
                            goToStep(btn.getAttribute("data-prev"));
                        });
                    });

                    var namePencil = document.getElementById("name-edit-pencil");
                    if (namePencil) {
                        namePencil.addEventListener("click", function() {
                            document.getElementById("name-display-block").classList.add("hidden");
                            document.getElementById("name-input-block").classList.remove("hidden");
                        });
                    }
                    var phonePencil = document.getElementById("phone-edit-pencil");
                    if (phonePencil) {
                        phonePencil.addEventListener("click", function() {
                            document.getElementById("phone-display-block").classList.add("hidden");
                            document.getElementById("phone-input-block").classList.remove("hidden");
                        });
                    }

                    var emailPencil = document.getElementById("email-edit-pencil");
                    if (emailPencil) {
                        emailPencil.addEventListener("click", function() {
                            document.getElementById("email-display-block").classList.add("hidden");
                            document.getElementById("email-input-block").classList.remove("hidden");
                        });
                    }

                    ["guest_name", "phone", "email", "checkin_time_preference", "checkout_time_preference"].forEach(function(name) {
                        var input = document.querySelector('[name="' + name + '"]');
                        if (input) {
                            input.addEventListener("input", function() {
                                var partial = {};
                                partial[name] = input.value;
                                idwSaveState(partial);
                            });
                            input.addEventListener("change", function() {
                                var partial = {};
                                partial[name] = input.value;
                                idwSaveState(partial);
                            });
                        }
                    });
                    document.querySelectorAll('input[name="parking_needed"]').forEach(function(radio) {
                        radio.addEventListener("change", function() {
                            idwSaveState({ parking_needed: radio.value });
                            var vehicleBlock = document.getElementById("vehicle-info-block");
                            if (vehicleBlock) vehicleBlock.style.display = radio.value === "1" && radio.checked ? "" : "none";
                        });
                    });

                    try { idwRestoreState(); } catch (e) { console.error("idwRestoreState failed:", e); }
                })();

                var currentSide = "front";
                var stream = null;
                var photoIdRequired = {{ $booking->photo_id_received ? 'false' : 'true' }};
                var isPassport = {{ $booking->id_type === 'passport' ? 'true' : 'false' }};
                // Which side(s) actually need a (re)capture right now — a side that's
                // already approved (or not cleared by a decline) should never be
                // re-prompted, even after the guest finishes capturing the other side.
                var idwFrontRequired = {{ $idwFrontRequired ? 'true' : 'false' }};
                var idwBackRequired = {{ $idwBackRequired ? 'true' : 'false' }};

                // ── Device detection (mobile/tablet vs desktop) ────────────────────
                // Deliberately NOT a screen-width check — that's trivially spoofed by
                // resizing a desktop browser window. Instead this checks the actual
                // device/browser signals: the modern userAgentData.mobile flag where
                // available, falling back to a User-Agent match for older browsers,
                // plus a special case for iPads — they report a desktop "Macintosh"
                // UA by default, but a real Mac never reports multi-touch, so
                // platform === 'MacIntel' + maxTouchPoints > 1 reliably catches them.
                function idwIsMobileOrTablet() {
                    try {
                        if (navigator.userAgentData && typeof navigator.userAgentData.mobile === "boolean") {
                            if (navigator.userAgentData.mobile) return true;
                        }
                        var ua = navigator.userAgent || "";
                        if (/Android|iPhone|iPod|iPad|Mobile|Tablet/i.test(ua)) return true;
                        if (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1) return true;
                        return false;
                    } catch (e) {
                        // If detection throws for any reason, don't block a real guest —
                        // default to showing the camera flow.
                        return true;
                    }
                }

                // ── QR handoff for desktop guests ───────────────────────────────────
                // Photo capture only makes sense on a device with a camera in hand, so
                // desktop guests are pointed to their phone instead of being shown a
                // camera UI. Loaded lazily and only for desktop guests. Generated
                // entirely client-side — no external service call, no cost, and it
                // still works if this CDN is ever unreachable (the plain link below
                // the code is always shown as a fallback).
                var IDW_QRCODE_SRC = "https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js";
                var idwQrLoadStarted = false;

                function idwShowDesktopNotice() {
                    document.getElementById("idw-mobile-capture-ui").classList.add("hidden");
                    document.getElementById("idw-desktop-notice").classList.remove("hidden");
                    document.getElementById("idw-qr-link").textContent = window.location.href;

                    if (idwQrLoadStarted) return;
                    idwQrLoadStarted = true;
                    var script = document.createElement("script");
                    script.src = IDW_QRCODE_SRC;
                    script.onload = function() {
                        try {
                            new QRCode(document.getElementById("idw-qr-canvas"), {
                                text: window.location.href,
                                width: 180,
                                height: 180,
                                correctLevel: QRCode.CorrectLevel.M
                            });
                        } catch (e) {
                            // QR render failed — the plain link underneath still works.
                        }
                    };
                    document.head.appendChild(script);
                }

                (function idwInitDeviceGate() {
                    if (!photoIdRequired) return;
                    if (!idwIsMobileOrTablet()) {
                        idwShowDesktopNotice();
                    }
                    var continueBtn = document.getElementById("idw-continue-desktop-btn");
                    if (continueBtn) {
                        continueBtn.addEventListener("click", function() {
                            document.getElementById("idw-desktop-notice").classList.add("hidden");
                            document.getElementById("idw-mobile-capture-ui").classList.remove("hidden");
                        });
                    }
                })();

                // ── OpenCV.js loader ────────────────────────────────────────────────
                // OpenCV.js is loaded lazily (only once the guest reaches this step) and
                // runs entirely client-side in the browser — this has no server/hosting
                // requirements at all (works the same on any host, cPanel included).
                // If it fails to load (blocked network, offline, slow connection) we fall
                // back to the old manual tap-to-capture button + JS blur check so nobody
                // gets stuck unable to upload their ID.
                var IDW_OPENCV_SRC = "https://docs.opencv.org/4.9.0/opencv.js";
                var idwCvReady = false;
                var idwCvFailed = false;
                var idwCvLoadStarted = false;

                function loadOpenCv(onReady, onFail) {
                    if (idwCvReady) { onReady(); return; }
                    if (idwCvFailed) { onFail(); return; }
                    if (!idwCvLoadStarted) {
                        idwCvLoadStarted = true;
                        var script = document.createElement("script");
                        script.src = IDW_OPENCV_SRC;
                        script.async = true;
                        script.onerror = function() { idwCvFailed = true; onFail(); };
                        document.head.appendChild(script);
                        // Give it a reasonable timeout — slow connections shouldn't block
                        // the guest from uploading their ID indefinitely.
                        setTimeout(function() {
                            if (!idwCvReady && !idwCvFailed) { idwCvFailed = true; onFail(); }
                        }, 8000);
                    }
                    var checkInterval = setInterval(function() {
                        if (window.cv && window.cv.Mat) {
                            // Some builds need onRuntimeInitialized; guard for both cases.
                            if (window.cv.onRuntimeInitialized !== undefined && !idwCvReady) {
                                window.cv["onRuntimeInitialized"] = function() {
                                    idwCvReady = true;
                                    clearInterval(checkInterval);
                                    onReady();
                                };
                            } else if (!idwCvReady) {
                                idwCvReady = true;
                                clearInterval(checkInterval);
                                onReady();
                            }
                        }
                    }, 100);
                }

                // Downsamples the captured image, computes a Laplacian-based sharpness
                // score (real focus/blur detection, not just brightness contrast), and a
                // grid edge-density heuristic as a lightweight (non-OCR) "is there
                // text-like structure here" signal.
                // NOTE: this is the legacy JS-only fallback, used only if OpenCV.js fails
                // to load. When OpenCV is available, idwCvCheckFrame() below is used
                // instead — it's faster and considerably more reliable.
                var IDW_DEBUG_LOG_SCORES = false; // tuning logs disabled

                function __idwGetGrayscaleSample(imgEl, targetWidth) {
                    var scale = targetWidth / imgEl.naturalWidth;
                    var w = targetWidth;
                    var h = Math.max(1, Math.round(imgEl.naturalHeight * scale));
                    var canvas = document.createElement("canvas");
                    canvas.width = w;
                    canvas.height = h;
                    var ctx = canvas.getContext("2d");
                    ctx.drawImage(imgEl, 0, 0, w, h);
                    var data = ctx.getImageData(0, 0, w, h).data;
                    var gray = new Float32Array(w * h);
                    for (var i = 0, p = 0; i < data.length; i += 4, p++) {
                        gray[p] = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
                    }
                    return { gray: gray, w: w, h: h };
                }

                function __idwLaplacianMap(gray, w, h) {
                    var lap = new Float32Array(w * h);
                    for (var y = 1; y < h - 1; y++) {
                        for (var x = 1; x < w - 1; x++) {
                            var idx = y * w + x;
                            var val =
                                gray[idx - w] + gray[idx + w] + gray[idx - 1] + gray[idx + 1] - 4 * gray[idx];
                            lap[idx] = val;
                        }
                    }
                    return lap;
                }

                function __idwSharpnessVariance(lap, w, h) {
                    var n = w * h;
                    var sum = 0;
                    for (var i = 0; i < n; i++) sum += lap[i];
                    var mean = sum / n;
                    var variance = 0;
                    for (var i = 0; i < n; i++) variance += Math.pow(lap[i] - mean, 2);
                    return variance / n;
                }

                function __idwTextLikeCellRatio(lap, w, h) {
                    var cellSize = 16;
                    var cols = Math.floor(w / cellSize);
                    var rows = Math.floor(h / cellSize);
                    if (cols < 1 || rows < 1) return 0;
                    var textLikeCells = 0;
                    var totalCells = 0;
                    for (var cy = 0; cy < rows; cy++) {
                        for (var cx = 0; cx < cols; cx++) {
                            var edgeCount = 0;
                            var strong = 0;
                            for (var y = cy * cellSize; y < (cy + 1) * cellSize && y < h; y++) {
                                for (var x = cx * cellSize; x < (cx + 1) * cellSize && x < w; x++) {
                                    var v = Math.abs(lap[y * w + x]);
                                    if (v > 15) edgeCount++;
                                    if (v > 60) strong++;
                                }
                            }
                            totalCells++;
                            var cellPixels = cellSize * cellSize;
                            var edgeDensity = edgeCount / cellPixels;
                            if (edgeDensity > 0.12 && edgeDensity < 0.55 && strong < cellPixels * 0.3) {
                                textLikeCells++;
                            }
                        }
                    }
                    return totalCells > 0 ? textLikeCells / totalCells : 0;
                }

                var IDW_SHARPNESS_MIN = 45;      // Laplacian variance floor - retune after real captures
                var IDW_TEXT_RATIO_MIN = 0.03;   // fraction of grid cells that must look text-like

                // Thresholds for the OpenCV auto-capture path — these run on a different
                // scale (full guide-box crop, not a 400px downsample) so they are NOT the
                // same numbers as the legacy JS thresholds above. Untested against real
                // captures yet — retune once real guest photos are reviewed.
                var IDW_CV_FILL_MIN = 0.80;       // detected document must fill ≥80% of the guide box
                var IDW_CV_SHARPNESS_MIN = 120;   // OpenCV Laplacian variance floor
                var IDW_CV_STABLE_FRAMES_NEEDED = 4; // consecutive good frames (~1s) before auto-capture

                function checkBlur(imgEl, warningEl) {
                    var sample = __idwGetGrayscaleSample(imgEl, 400);
                    var lap = __idwLaplacianMap(sample.gray, sample.w, sample.h);
                    var sharpness = __idwSharpnessVariance(lap, sample.w, sample.h);
                    var textRatio = __idwTextLikeCellRatio(lap, sample.w, sample.h);

                    if (IDW_DEBUG_LOG_SCORES) {
                        console.log("[ID capture check] sharpness=" + sharpness.toFixed(2) + " textRatio=" + textRatio.toFixed(3));
                    }

                    if (sharpness < IDW_SHARPNESS_MIN) {
                        warningEl.textContent = "Image is blurry. Please retake.";
                        warningEl.classList.remove("hidden");
                        return false;
                    }
                    if (textRatio < IDW_TEXT_RATIO_MIN) {
                        warningEl.textContent = "No legible ID text detected. Please retake with the ID clearly in frame.";
                        warningEl.classList.remove("hidden");
                        return false;
                    }
                    warningEl.classList.add("hidden");
                    return true;
                }

                // ── OpenCV-based live frame analysis ────────────────────────────────
                // Runs on a downsized crop of the guide-box region while the camera
                // stream is live, every ~250ms. Reports back:
                //   fillRatio  — how much of the guide box the detected document
                //                rectangle occupies (auto-capture requires the ID to
                //                actually fill the frame, not just be present in it)
                //   sharpness  — Laplacian variance (higher = sharper); this uses the
                //                same live frame the guide-box crop will actually use
                //                for the final capture, so by the time we auto-fire,
                //                the frame is already known-sharp (fixes the "haze"
                //                issue caused by capturing before autofocus settles)
                function idwCvCheckFrame(canvas) {
                    var src = cv.imread(canvas);
                    var gray = new cv.Mat();
                    cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);

                    // Sharpness via Laplacian variance (OpenCV's own, much faster than
                    // the hand-rolled JS version above).
                    var lap = new cv.Mat();
                    cv.Laplacian(gray, lap, cv.CV_64F);
                    var mean = new cv.Mat();
                    var stddev = new cv.Mat();
                    cv.meanStdDev(lap, mean, stddev);
                    var sharpness = Math.pow(stddev.data64F[0], 2);

                    // Document-fill detection: edge detect, find the largest contour,
                    // compare its bounding box to the full frame.
                    var edges = new cv.Mat();
                    cv.Canny(gray, edges, 50, 150);
                    var contours = new cv.MatVector();
                    var hierarchy = new cv.Mat();
                    cv.findContours(edges, contours, hierarchy, cv.RETR_LIST, cv.CHAIN_APPROX_SIMPLE);

                    var frameArea = canvas.width * canvas.height;
                    var bestArea = 0;
                    for (var i = 0; i < contours.size(); i++) {
                        var contour = contours.get(i);
                        var rect = cv.boundingRect(contour);
                        var area = rect.width * rect.height;
                        if (area > bestArea && area < frameArea * 1.02) {
                            bestArea = area;
                        }
                        contour.delete();
                    }
                    var fillRatio = frameArea > 0 ? bestArea / frameArea : 0;

                    src.delete(); gray.delete(); lap.delete(); mean.delete(); stddev.delete();
                    edges.delete(); contours.delete(); hierarchy.delete();

                    return { fillRatio: fillRatio, sharpness: sharpness };
                }

                var idwDetectionTimer = null;
                var idwStableFrameCount = 0;
                var idwAutoCaptureInFlight = false;

                function stopDetectionLoop() {
                    if (idwDetectionTimer) { clearInterval(idwDetectionTimer); idwDetectionTimer = null; }
                    idwStableFrameCount = 0;
                    idwAutoCaptureInFlight = false;
                }

                function startDetectionLoop() {
                    stopDetectionLoop();
                    var video = document.getElementById("camera-stream");
                    var statusEl = document.getElementById("idw-capture-status");
                    var detectCanvas = document.createElement("canvas");

                    idwDetectionTimer = setInterval(function() {
                        if (idwAutoCaptureInFlight || !video.videoWidth) return;
                        var crop = __idwGetGuideCropRect(video);
                        // Downsize for speed — we only need this for detection, not the
                        // final saved image (final capture re-crops at full resolution).
                        var scale = Math.min(1, 260 / crop.w);
                        detectCanvas.width = Math.round(crop.w * scale);
                        detectCanvas.height = Math.round(crop.h * scale);
                        detectCanvas.getContext("2d").drawImage(
                            video, crop.x, crop.y, crop.w, crop.h, 0, 0, detectCanvas.width, detectCanvas.height
                        );

                        var result;
                        try {
                            result = idwCvCheckFrame(detectCanvas);
                        } catch (e) {
                            return; // transient decode error, just skip this frame
                        }

                        if (result.fillRatio >= IDW_CV_FILL_MIN && result.sharpness >= IDW_CV_SHARPNESS_MIN) {
                            idwStableFrameCount++;
                            statusEl.textContent = "Hold steady…";
                        } else if (result.fillRatio < IDW_CV_FILL_MIN) {
                            idwStableFrameCount = 0;
                            statusEl.textContent = "Move the ID closer so it fills the frame";
                        } else {
                            idwStableFrameCount = 0;
                            statusEl.textContent = "Hold steady for a clear photo…";
                        }

                        if (idwStableFrameCount >= IDW_CV_STABLE_FRAMES_NEEDED) {
                            idwAutoCaptureInFlight = true;
                            statusEl.textContent = "Capturing…";
                            performCapture();
                        }
                    }, 250);
                }

                function startCamera(side) {
                    currentSide = side;
                    var container = document.getElementById("camera-container");
                    var btnWrapper = document.getElementById("capture-btn-wrapper");
                    var frontTrigger = document.getElementById("upload-zone-trigger");
                    var backTrigger = document.getElementById("upload-zone-trigger-back");
                    var frontLabel = document.getElementById("upload-zone-trigger-front-label");
                    var backLabel = document.getElementById("upload-zone-trigger-back-label");
                    var activeTrigger = side === "front" ? frontTrigger : backTrigger;

                    frontTrigger.classList.add("hidden");
                    backTrigger.classList.add("hidden");
                    frontLabel.classList.add("hidden");
                    backLabel.classList.add("hidden");

                    activeTrigger.parentNode.insertBefore(container, activeTrigger);
                    activeTrigger.parentNode.insertBefore(btnWrapper, activeTrigger);

                    container.classList.remove("hidden");
                    btnWrapper.classList.remove("hidden");
                    document.getElementById("idw-capture-status").textContent = "Loading camera…";
                    document.getElementById("capture-btn").classList.add("hidden");
                    document.getElementById("capture-btn-fallback-label").classList.add("hidden");

                    navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" }, audio: false })
                        .then(function(s) {
                            stream = s;
                            document.getElementById("camera-stream").srcObject = s;
                            var label;
                            if (side === "front") {
                                label = isPassport ? "Take a picture of the passport data page" : "Take a picture of the front of ID";
                            } else {
                                label = "Take a picture of the back of ID";
                            }
                            document.getElementById("camera-instruction-label").textContent = label;

                            loadOpenCv(function() {
                                document.getElementById("idw-capture-status").textContent = "Position the ID within the frame";
                                startDetectionLoop();
                            }, function() {
                                // OpenCV unavailable — fall back to manual capture so the
                                // guest isn't stuck.
                                document.getElementById("idw-capture-status").textContent = "Position the ID within the frame, then tap to capture";
                                document.getElementById("capture-btn").classList.remove("hidden");
                                document.getElementById("capture-btn-fallback-label").classList.remove("hidden");
                            });
                        })
                        .catch(function() { alert("Camera access denied. Please allow camera permissions and try again."); });
                }

                function stopCamera() {
                    stopDetectionLoop();
                    if (stream) { stream.getTracks().forEach(function(t){ t.stop(); }); stream = null; }
                    document.getElementById("camera-container").classList.add("hidden");
                    document.getElementById("capture-btn-wrapper").classList.add("hidden");
                }

                // Crops the capture to the same guide-border rectangle the user sees
                // (CSS overlay is decorative only and has no effect on the raw video
                // frame, so we replicate the object-cover + centered-88%-box math here
                // in native video pixel coordinates).
                function __idwGetGuideCropRect(video) {
                    var containerAspect = 16 / 9;
                    var videoAspect = video.videoWidth / video.videoHeight;
                    var visW, visH, offX, offY;
                    if (videoAspect > containerAspect) {
                        visH = video.videoHeight;
                        visW = visH * containerAspect;
                        offX = (video.videoWidth - visW) / 2;
                        offY = 0;
                    } else {
                        visW = video.videoWidth;
                        visH = visW / containerAspect;
                        offX = 0;
                        offY = (video.videoHeight - visH) / 2;
                    }
                    var guideAspect = 1.586;
                    var guideW = visW * 0.88;
                    var guideH = guideW / guideAspect;
                    if (guideH > visH) { guideH = visH; guideW = guideH * guideAspect; }
                    var guideX = offX + (visW - guideW) / 2;
                    var guideY = offY + (visH - guideH) / 2;
                    return { x: guideX, y: guideY, w: guideW, h: guideH };
                }

                if (photoIdRequired) {
                    function performCapture() {
                        var video = document.getElementById("camera-stream");
                        var crop = __idwGetGuideCropRect(video);
                        var canvas = document.createElement("canvas");
                        canvas.width = crop.w;
                        canvas.height = crop.h;
                        canvas.getContext("2d").drawImage(video, crop.x, crop.y, crop.w, crop.h, 0, 0, crop.w, crop.h);
                        var dataUrl = canvas.toDataURL("image/jpeg", 0.92);
                        var side = currentSide;
                        stopCamera();
                        if (side === "front") {
                            var img = document.getElementById("front-preview");
                            img.src = dataUrl;
                            document.getElementById("front-preview-block").classList.remove("hidden");
                            img.onload = function() {
                                var ok = checkBlur(img, document.getElementById("front-blur-warning"));
                                if (ok) {
                                    document.getElementById("photo-id-data").value = dataUrl;
                                    idwSaveState({ photo_id: dataUrl });
                                    if (!isPassport && idwBackRequired) {
                                        document.getElementById("upload-zone-trigger-back").classList.remove("hidden");
                                        document.getElementById("upload-zone-trigger-back-label").classList.remove("hidden");
                                    }
                                }
                            };
                        } else {
                            var img = document.getElementById("back-preview");
                            img.src = dataUrl;
                            document.getElementById("back-preview-block").classList.remove("hidden");
                            img.onload = function() {
                                var ok = checkBlur(img, document.getElementById("back-blur-warning"));
                                if (ok) { document.getElementById("photo-id-back-data").value = dataUrl; idwSaveState({ photo_id_back: dataUrl }); }
                            };
                        }
                    }

                    // Manual fallback button — only shown if OpenCV.js fails to load.
                    document.getElementById("capture-btn").addEventListener("click", performCapture);

                    document.getElementById("retake-front-btn").addEventListener("click", function() {
                        document.getElementById("front-preview-block").classList.add("hidden");
                        document.getElementById("upload-zone-trigger-back").classList.add("hidden");
                        document.getElementById("upload-zone-trigger-back-label").classList.add("hidden");
                        document.getElementById("photo-id-data").value = "";
                        startCamera("front");
                    });

                    document.getElementById("retake-back-btn").addEventListener("click", function() {
                        document.getElementById("back-preview-block").classList.add("hidden");
                        document.getElementById("photo-id-back-data").value = "";
                        startCamera("back");
                    });
                }

                function withButtonBusy(btn, busyLabel, fn) {
                    var origHtml = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="ui-spinner"></span><span>' + busyLabel + '</span>';
                    function restore() {
                        btn.disabled = false;
                        btn.innerHTML = origHtml;
                    }
                    fn(restore);
                }

                // ── Step 1 "Next": validate contact fields, AJAX-save via guest.login, then advance ──
                document.getElementById("step1-next-btn").addEventListener("click", function() {
                    var btn = this;
                    var step = btn.closest(".idw-step");
                    if (step && step.querySelector("input:invalid")) {
                        var invalid = step.querySelector("input:invalid");
                        invalid.reportValidity();
                        return;
                    }
                    var fieldChecks = ["guest_name", "phone", "email"];
                    for (var fc = 0; fc < fieldChecks.length; fc++) {
                        var visibleInputs = Array.prototype.filter.call(
                            step.querySelectorAll('input[name="' + fieldChecks[fc] + '"]'),
                            function(el) { return el.offsetParent !== null; }
                        );
                        var activeInput = visibleInputs[0];
                        if (activeInput && !activeInput.value.trim()) {
                            activeInput.classList.add("border-red-400");
                            activeInput.scrollIntoView({ behavior: "smooth", block: "center" });
                            activeInput.focus();
                            return;
                        } else if (activeInput) {
                            activeInput.classList.remove("border-red-400");
                        }
                    }
                    var parkingGroup = step.querySelectorAll('input[name="parking_needed"]');
                    var parkingError = document.getElementById("parking-error");
                    var parkingChecked2 = step.querySelector('input[name="parking_needed"]:checked');
                    if (parkingGroup.length) {
                        if (!parkingChecked2) {
                            if (parkingError) parkingError.style.display = "block";
                            var parkingBlock = document.getElementById("parking-question-block");
                            if (parkingBlock) parkingBlock.scrollIntoView({ behavior: "smooth", block: "center" });
                            return;
                        } else if (parkingError) {
                            parkingError.style.display = "none";
                        }
                    }

                    // Task 34: vehicle info required once parking is (or already is) "yes"
                    var parkingIsYes = parkingChecked2 ? parkingChecked2.value === "1" : {{ $booking->parking_needed ? 'true' : 'false' }};
                    if (parkingIsYes) {
                        var makeModelInput = step.querySelector('input[name="vehicle_make_model"]');
                        var plateFileInput = step.querySelector('input[name="license_plate_photo"]');
                        var hasExistingPlatePhoto = {{ $booking->license_plate_photo_path ? 'true' : 'false' }};
                        var vehicleError = document.getElementById("vehicle-error");
                        var missingMakeModel = makeModelInput && !makeModelInput.value.trim();
                        var missingPlatePhoto = plateFileInput && !(plateFileInput.files && plateFileInput.files.length) && !hasExistingPlatePhoto;
                        if (missingMakeModel || missingPlatePhoto) {
                            if (vehicleError) vehicleError.style.display = "block";
                            var vehicleBlock = document.getElementById("vehicle-info-block");
                            if (vehicleBlock) vehicleBlock.scrollIntoView({ behavior: "smooth", block: "center" });
                            return;
                        } else if (vehicleError) {
                            vehicleError.style.display = "none";
                        }
                    }
                    var timeSelect = step.querySelector('#checkin_time_preference_select');
                    if (timeSelect && !timeSelect.value) {
                        timeSelect.classList.add("border-red-400");
                        var timeError = document.getElementById("checkin-time-error");
                        if (timeError) timeError.style.display = "block";
                        timeSelect.scrollIntoView({ behavior: "smooth", block: "center" });
                        timeSelect.focus();
                        return;
                    }
                    var checkoutSelect = step.querySelector('#checkout_time_preference_select');
                    if (checkoutSelect && !checkoutSelect.value) {
                        checkoutSelect.classList.add("border-red-400");
                        var checkoutError = document.getElementById("checkout-time-error");
                        if (checkoutError) checkoutError.style.display = "block";
                        checkoutSelect.scrollIntoView({ behavior: "smooth", block: "center" });
                        checkoutSelect.focus();
                        return;
                    }

                    var loginFd = new FormData();
                    loginFd.append("_token", document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : "");
                    ["guest_name", "phone", "email", "checkin_time_preference", "checkout_time_preference"].forEach(function(name) {
                        var input = step.querySelector('[name="' + name + '"]');
                        if (input) loginFd.append(name, input.value);
                    });
                    if (parkingChecked2) loginFd.append("parking_needed", parkingChecked2.value);
                    if (parkingIsYes) {
                        if (makeModelInput) loginFd.append("vehicle_make_model", makeModelInput.value);
                        if (plateFileInput && plateFileInput.files && plateFileInput.files.length) {
                            loginFd.append("license_plate_photo", plateFileInput.files[0]);
                        }
                    }

                    withButtonBusy(btn, "Saving…", function(restore) {
                        fetch("{{ route('guest.login', [$booking->booking_id, $booking->token]) }}", {
                            method: "POST",
                            body: loginFd,
                            headers: { "Accept": "application/json" }
                        })
                            .then(function(r) {
                                if (r.status === 422) {
                                    return r.json().then(function(body) {
                                        restore();
                                        var messages = body.errors ? Object.values(body.errors).flat().join("\n") : "Please check the form and try again.";
                                        alert(messages);
                                    });
                                }
                                if (!r.ok) {
                                    restore();
                                    alert("Something went wrong. Please try again.");
                                    return;
                                }
                                restore();
                                goToStep(2);
                            })
                            .catch(function() {
                                restore();
                                alert("Network error. Please try again.");
                            });
                    });
                });

                // ── Step 2 "Next": validate + AJAX-submit photos via submitIdentity, then advance to Step 3 ──
                document.getElementById("id-capture-next-btn").addEventListener("click", function() {
                    var btn = this;
                    if (photoIdRequired) {
                        var front = document.getElementById("photo-id-data").value;
                        var back = document.getElementById("photo-id-back-data").value;
                        var frontBlur = document.getElementById("front-blur-warning");
                        var backBlur = document.getElementById("back-blur-warning");
                        if (!front) { alert("Please take a photo of the front of your ID."); return; }
                        if (!isPassport && idwBackRequired && !back) { alert("Please take a photo of the back of your ID."); return; }
                        if (!frontBlur.classList.contains("hidden")) { alert("Front ID photo is blurry. Please retake."); return; }
                        if (!isPassport && idwBackRequired && !backBlur.classList.contains("hidden")) { alert("Back ID photo is blurry. Please retake."); return; }
                    }

                    function b64toBlob(b64) {
                        var arr = b64.split(","), mime = arr[0].match(/:(.*?);/)[1];
                        var bstr = atob(arr[1]), n = bstr.length, u8 = new Uint8Array(n);
                        for (var i = 0; i < n; i++) u8[i] = bstr.charCodeAt(i);
                        return new Blob([u8], {type: mime});
                    }
                    var form = document.getElementById("guest-booking-form");
                    var fd = new FormData();
                    fd.append("_token", document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : "");
                    if (photoIdRequired) {
                        fd.set("photo_id", b64toBlob(document.getElementById("photo-id-data").value), "front.jpg");
                        if (!isPassport && idwBackRequired) {
                            fd.set("photo_id_back", b64toBlob(document.getElementById("photo-id-back-data").value), "back.jpg");
                        }
                    }

                    withButtonBusy(btn, "Uploading…", function(restore) {
                        fetch(form.action, {
                            method: "POST",
                            body: fd,
                            headers: { "Accept": "application/json" }
                        })
                            .then(function(r) {
                                if (r.status === 422) {
                                    return r.json().then(function(body) {
                                        restore();
                                        var messages = body.errors ? Object.values(body.errors).flat().join("\n") : "Please check the form and try again.";
                                        alert(messages);
                                    });
                                }
                                if (!r.ok && !r.redirected) {
                                    restore();
                                    return r.text().then(function(t) {
                                        console.error("Server error:", t);
                                        alert("Upload failed (server error). Please try again.");
                                    });
                                }
                                restore();
                                idwClearState();
                                goToStep(3);
                            })
                            .catch(function(e) {
                                restore();
                                console.error(e);
                                alert("Upload failed. Please try again.");
                            });
                    });
                });

                // ── Step 3 "Continue": no network call — data already saved, just reload to reflect pending-approval state ──
                document.getElementById("smart-lock-continue-btn").addEventListener("click", function() {
                    idwClearState();
                    window.location.reload();
                });
                </script>
            </div>
        @elseif($state === 'waiting')
            <div class="guest-portal-card">
                <div class="guest-status-bar">
                    <div>
                        @if($siteLogo)
                            <img src="{{ url('/img/'.$siteLogo) }}" alt="" class="h-8 max-w-[140px] w-auto object-contain">
                        @endif
                    </div>
                    <span class="guest-status-pill is-ready">
                        <x-icon name="calendar" class="h-4 w-4" />
                        Not checked in
                    </span>
                </div>
                <img src="{{ $heroImg }}" alt="{{ $property->name }}" class="w-full block mt-4" style="height:auto">
                <div class="px-6 pt-8 pb-2 text-center">
                    <div class="guest-big-check">
                        <x-icon name="check" class="h-8 w-8" />
                    </div>
                    <h2 class="mt-4 text-xl font-extrabold text-slate-950">Approved for check in!</h2>
                </div>
                <div class="px-6 pb-6">
                    <div class="guest-stay-grid">
                        <div class="guest-stay-tile">
                            <div class="guest-stay-tile-icon">
                                <x-icon name="calendar" class="h-5 w-5" />
                            </div>
                            <p class="guest-stay-tile-label">Check-In</p>
                            <p class="guest-stay-tile-date">{{ $booking->check_in_date->format('M d, Y') }}</p>
                            <p class="guest-stay-tile-time">{{ $booking->effectiveCheckinTimeFormatted() }}</p>
                        </div>
                        <div class="guest-stay-tile">
                            <div class="guest-stay-tile-icon">
                                <x-icon name="calendar" class="h-5 w-5" />
                            </div>
                            <p class="guest-stay-tile-label">Check-Out</p>
                            <p class="guest-stay-tile-date">{{ $booking->check_out_date->format('M d, Y') }} {{ $booking->nightsLabel() }}</p>
                            <p class="guest-stay-tile-time">{{ $booking->effectiveCheckoutTimeFormatted() }}</p>
                        </div>
                    </div>

                    <div class="guest-detail-banner">
                        <span class="guest-detail-banner-icon">
                            <x-icon name="check" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="guest-detail-banner-title">Check In Details Available</p>
                            <p class="guest-detail-banner-sub">{{ $booking->check_in_date->format('M d, Y') }} at {{ $booking->effectiveCheckinTimeFormatted() }}</p>
                            <p class="guest-detail-banner-sub mt-1">Please come back then for property address and check in details.</p>
                        </div>
                    </div>

                    <button class="guest-primary-btn mt-5 w-full" disabled>Check In</button>
                </div>
            </div>
        @elseif($state === 'arrival')
            <div data-poll-gps-status="{{ route('guest.gps-status', [$booking->booking_id, $booking->token]) }}"></div>
            <div class="guest-portal-card">
                <div class="guest-status-bar">
                    <div>
                        @if($siteLogo)
                            <img src="{{ url('/img/'.$siteLogo) }}" alt="" class="h-8 max-w-[140px] w-auto object-contain">
                        @endif
                    </div>
                    <span class="guest-status-pill">
                        <x-icon name="alert-triangle" class="h-4 w-4" />
                        Not checked in
                    </span>
                </div>
                <img src="{{ $heroImg }}" alt="{{ $property->name }}" class="w-full block rounded-xl mt-4">
            <div class="p-6 md:p-10">
                <h1 class="guest-status-title">We Can't Wait To See You!</h1>
                <div class="guest-stay-grid">
                    <div class="guest-stay-tile">
                        <div class="guest-stay-tile-icon">
                            <x-icon name="calendar" class="h-5 w-5" />
                        </div>
                        <p class="guest-stay-tile-label">Check-In</p>
                        <p class="guest-stay-tile-date">{{ $booking->check_in_date->format('M d, Y') }}</p>
                        <p class="guest-stay-tile-time">{{ $booking->effectiveCheckinTimeFormatted() }}</p>
                    </div>
                    <div class="guest-stay-tile">
                        <div class="guest-stay-tile-icon">
                            <x-icon name="calendar" class="h-5 w-5" />
                        </div>
                        <p class="guest-stay-tile-label">Check-Out</p>
                        <p class="guest-stay-tile-date">{{ $booking->check_out_date->format('M d, Y') }} {{ $booking->nightsLabel() }}</p>
                        <p class="guest-stay-tile-time">{{ $booking->effectiveCheckoutTimeFormatted() }}</p>
                    </div>
                </div>

                @if($booking->canViewAddress())
                    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4 text-sm">
                        <p class="font-semibold text-slate-800 mb-2">Property Address</p>
                        <p class="text-slate-600">{{ $property->address }}<br>{{ $property->city }}@if($property->state), {{ $property->state }}@endif {{ $property->zip }}</p>
                        @if($property->latitude && $property->longitude)
                            <div class="-mx-4 mt-3 overflow-hidden border-y border-slate-200 md:mx-0 md:rounded-lg md:border">
                                <iframe title="Map"
                                    src="https://www.google.com/maps?q={{ $property->latitude }},{{ $property->longitude }}&output=embed"
                                    class="h-64 w-full border-0 md:h-96"></iframe>
                            </div>
                        @endif
                        @if($property->map_directions_url)
                            <a href="{{ $property->map_directions_url }}" target="_blank" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600">
                                <x-icon name="map" class="h-3.5 w-3.5" /> Get Directions
                            </a>
                        @endif
                    </div>
                @else
                    <div class="guest-detail-banner">
                        <span class="guest-detail-banner-icon">
                            <x-icon name="check" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="guest-detail-banner-title">Check In Details Available</p>
                            <p class="guest-detail-banner-sub">{{ $booking->check_in_date->format('M d, Y') }} at {{ $booking->addressAvailableAtFormatted() }}</p>
                            <p class="guest-detail-banner-sub mt-1">Please come back then for property address and check in details.</p>
                        </div>
                    </div>
                @endif
            </div>
            </div>

            @if($booking->canViewAddress())
            <div class="guest-portal-card mt-4">
                <div class="p-6 md:p-8 text-center text-xl text-slate-950">{!! $gpsVerifyMessage !!}</div>
            </div>

            <div class="guest-portal-card mt-4">
                <div class="p-6 md:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-base font-bold text-slate-950">Navigate To:</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                {{ $property->address }}<br>
                                {{ $property->city }}@if($property->state), {{ $property->state }}@endif {{ $property->zip }}<br>
                                {{ $property->contact_phone ?: \App\Models\Setting::getValue('contact_phone') }}
                            </p>
                        </div>
                        @if($property->latitude && $property->longitude)
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $property->latitude }},{{ $property->longitude }}" target="_blank" class="shrink-0 flex flex-col items-center gap-1 text-xs font-semibold text-blue-600">
                                <img src="{{ asset('img/google-maps-icon.png') }}" alt="Google Maps" class="h-9 w-9">
                                Directions
                            </a>
                        @elseif($property->map_directions_url)
                            <a href="{{ $property->map_directions_url }}" target="_blank" class="shrink-0 flex flex-col items-center gap-1 text-xs font-semibold text-blue-600">
                                <img src="{{ asset('img/google-maps-icon.png') }}" alt="Google Maps" class="h-9 w-9">
                                Directions
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="guest-portal-card mt-4">
                <div class="p-6 md:p-8 text-center">
                    <div id="gps-ajax-message" class="hidden"></div>
                    <button id="gps-ajax-verify-btn" type="button" data-url="{{ route('guest.gps', [$booking->booking_id, $booking->token]) }}" data-csrf="{{ csrf_token() }}" class="guest-primary-btn is-go w-full">I Have Arrived</button>
                    <p class="mt-3 text-xs leading-5 text-slate-500">Please make sure your location is allowed. We will verify on the next page.</p>
                </div>
            </div>
            @endif
        @elseif($state === 'awaiting_deposit')
            <div data-poll-id-status="{{ route('guest.id-status', [$booking->booking_id, $booking->token]) }}" data-poll-fields="deposit_verified"></div>
            <div class="guest-portal-card">
                <div class="guest-status-bar">
                    <div>
                        @if($siteLogo)
                            <img src="{{ url('/img/'.$siteLogo) }}" alt="" class="h-8 max-w-[140px] w-auto object-contain">
                        @endif
                    </div>
                    <span class="guest-status-pill">
                        <x-icon name="clock" class="h-4 w-4" />
                        Awaiting deposit
                    </span>
                </div>
                <div class="px-6 pt-5">
                    <h1 class="guest-status-title">Almost there</h1>
                </div>
                <img src="{{ $heroImg }}" alt="{{ $property->name }}" class="w-full block rounded-xl mt-4">
                <div class="p-6 md:p-10 text-center">
                    @if($booking->status === 'pre_checkin_complete')
                        <h2 class="text-xl font-extrabold text-slate-950">Pre-check in completed!</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Please submit your required incidentals hold payment on the booking platform. This hold is refundable after check out.</p>
                    @else
                        <h2 class="text-xl font-extrabold text-slate-950">Pending incidentals hold payment</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-600">If you have already submitted the payment, please send us a message so that we can expedite this for you. It usually doesn't take that long.</p>
                    @endif
                </div>
            </div>
        @elseif($state === 'checkout_notice')
            <div class="guest-portal-card">
                <div class="guest-status-bar">
                    <div>
                        @if($siteLogo)
                            <img src="{{ url('/img/'.$siteLogo) }}" alt="" class="h-8 max-w-[140px] w-auto object-contain">
                        @endif
                    </div>
                    <span class="guest-status-pill is-checked">
                        <x-icon name="check" class="h-4 w-4" />
                        Checked in
                    </span>
                </div>
                <div class="p-6">
                    <h1 class="guest-status-title">Check-out is coming up</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Your check-out time is {{ $booking->effectiveCheckoutTimeFormatted() }} tomorrow. You'll still have full access to the guide until then.</p>
                    <a href="#guide-grid" class="guest-primary-btn w-full mt-4">View Guide</a>
                </div>
                @if($locks->isNotEmpty())
                    <div class="px-6 pb-2">
                        <div class="grid gap-6 {{ $locks->count() > 1 ? 'sm:grid-cols-2' : '' }}">
                            @foreach($locks as $entry)
                                <x-lock-card
                                    :booking-id="$booking->booking_id"
                                    :token="$booking->token"
                                    :lock-id="$entry['lock']->id"
                                    :lock-label="$locks->count() > 1 ? $entry['lock']->label : null"
                                    :lock-status="$entry['status']"
                                />
                            @endforeach
                        </div>
                    </div>
                @endif
                <div id="guide-grid" class="guest-guide-grid p-6 pt-0">
                    @foreach($guideCats as $category)
                        @php
                            $colors = $categoryColor;
                            $displayTitle = $category->pivot->custom_title ?: $category->title;
                            $displayDescription = $category->pivot->custom_description ?: $category->description;
                        @endphp
                        <x-guide-panel
                            :href="route('guest.category', [$booking->booking_id, $booking->token, $category])"
                            :icon="$category->slug"
                            :guest-icon="$category->guest_icon"
                            :title="$displayTitle"
                            :description="$displayDescription"
                            :tone="$colors[0]"
                            :accent="$colors[1]"
                            :wide="$category->slug === 'checkout-instructions'"
                        />
                    @endforeach
                </div>
            </div>
        @elseif($state === 'checkout_available')
            @if(count($checkoutSteps) > 0)
                <div id="checkout-wizard-wrapper" style="display:none">
                    <x-step-wizard :steps="$checkoutSteps" type="checkout" next-section="checkout-guide-section" :booking-id="$booking->booking_id" :token="$booking->token" />
                </div>
            @endif
            <div class="guest-portal-card" id="checkout-guide-section">
                <div class="guest-status-bar">
                    <div>
                        @if($siteLogo)
                            <img src="{{ url('/img/'.$siteLogo) }}" alt="" class="h-8 max-w-[140px] w-auto object-contain">
                        @endif
                    </div>
                    <span class="guest-status-pill is-checked">
                        <x-icon name="check" class="h-4 w-4" />
                        Checked in
                    </span>
                </div>
                <div class="p-6">
                    <h1 class="guest-status-title">Checking out today</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Check-out time is {{ $booking->effectiveCheckoutTimeFormatted() }}. You can still use the guide until then.</p>
                    @if(count($checkoutSteps) > 0)
                        <button type="button" onclick="document.getElementById('checkout-guide-section').style.display='none';document.getElementById('checkout-wizard-wrapper').style.display='';" class="guest-primary-btn w-full is-go">Thanks for staying. Time to check out. Click here to begin.</button>
                    @else
                        <form method="POST" action="{{ route('guest.confirm-checkout', [$booking->booking_id, $booking->token]) }}">
                            @csrf
                            <button type="submit" class="guest-primary-btn w-full is-go">Thanks for staying. Time to check out. Click here to begin.</button>
                        </form>
                    @endif
                </div>
                <div class="guest-guide-body px-6 pb-6">
                    <x-weather-badge :property="$property" class="guest-weather-card" />
                </div>
                @if($locks->isNotEmpty())
                    <div class="px-6 pb-2">
                        <div class="grid gap-6 {{ $locks->count() > 1 ? 'sm:grid-cols-2' : '' }}">
                            @foreach($locks as $entry)
                                <x-lock-card
                                    :booking-id="$booking->booking_id"
                                    :token="$booking->token"
                                    :lock-id="$entry['lock']->id"
                                    :lock-label="$locks->count() > 1 ? $entry['lock']->label : null"
                                    :lock-status="$entry['status']"
                                />
                            @endforeach
                        </div>
                    </div>
                @endif
                <div id="guide-grid" class="guest-guide-grid p-6 pt-0">
                    @foreach($guideCats as $category)
                        @php
                            $colors = $categoryColor;
                            $displayTitle = $category->pivot->custom_title ?: $category->title;
                            $displayDescription = $category->pivot->custom_description ?: $category->description;
                        @endphp
                        <x-guide-panel
                            :href="route('guest.category', [$booking->booking_id, $booking->token, $category])"
                            :icon="$category->slug"
                            :guest-icon="$category->guest_icon"
                            :title="$displayTitle"
                            :description="$displayDescription"
                            :tone="$colors[0]"
                            :accent="$colors[1]"
                            :wide="$category->slug === 'checkout-instructions'"
                        />
                    @endforeach
                </div>
            </div>
        @elseif($state === 'post_checkout')
            <div class="guest-portal-card">
                <div class="guest-status-bar">
                    <div>
                        @if($siteLogo)
                            <img src="{{ url('/img/'.$siteLogo) }}" alt="" class="h-8 max-w-[140px] w-auto object-contain">
                        @endif
                    </div>
                    <span class="guest-status-pill is-checked">
                        <x-icon name="check" class="h-4 w-4" />
                        Checked out
                    </span>
                </div>
                <div class="flex flex-col items-center justify-center gap-4 px-6 py-16 text-center md:py-24">
                    <h1 class="guest-status-title">Thank you for staying with us!</h1>
                    <p class="max-w-md text-sm leading-6 text-slate-600">We appreciate it. If you'd like to stay with us again, please contact us directly for a discount.</p>
                </div>
            </div>
        @elseif($state === 'checkout_locked')
            @if($booking->status === 'checked_out')
                <div class="guest-portal-card">
                    <div class="guest-status-bar">
                        <div>
                            @if($siteLogo)
                                <img src="{{ url('/img/'.$siteLogo) }}" alt="" class="h-8 max-w-[140px] w-auto object-contain">
                            @endif
                        </div>
                        <span class="guest-status-pill is-checked">
                            <x-icon name="check" class="h-4 w-4" />
                            Checked out
                        </span>
                    </div>
                    <div class="flex flex-col items-center justify-center gap-4 px-6 py-16 text-center md:py-24">
                        <h1 class="guest-status-title">You're all checked out</h1>
                        <p class="max-w-md text-sm leading-6 text-slate-600">We appreciate it. If you'd like to stay with us again, please contact us directly for a discount.</p>
                    </div>
                </div>
            @elseif(count($checkoutSteps) > 0)
                <x-step-wizard :steps="$checkoutSteps" type="checkout" next-section="checkout-complete" :booking-id="$booking->booking_id" :token="$booking->token" />
                <div class="guest-portal-card" id="checkout-complete" style="display:none">
                    <div class="guest-status-bar">
                        <div>
                            @if($siteLogo)
                                <img src="{{ url('/img/'.$siteLogo) }}" alt="" class="h-8 max-w-[140px] w-auto object-contain">
                            @endif
                        </div>
                        <span class="guest-status-pill is-checked">
                            <x-icon name="check" class="h-4 w-4" />
                            Checked out
                        </span>
                    </div>
                    <div class="flex flex-col items-center justify-center gap-4 px-6 py-16 text-center md:py-24">
                        <h1 class="guest-status-title">You're all checked out</h1>
                        <p class="max-w-md text-sm leading-6 text-slate-600">We appreciate it. If you'd like to stay with us again, please contact us directly for a discount.</p>
                    </div>
                </div>
            @else
            <div class="guest-guide-open">
                <div class="guest-status-bar">
                    <div>
                        @if($siteLogo)
                            <img src="{{ url('/img/'.$siteLogo) }}" alt="" class="h-8 max-w-[140px] w-auto object-contain">
                        @endif
                    </div>
                    <span class="guest-status-pill is-checked">
                        <x-icon name="check" class="h-4 w-4" />
                        Checked in
                    </span>
                </div>
            <div class="guest-guide-body">
                <div class="px-6 pt-6">
                    <h1 class="guest-status-title">Check-out instructions</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Thank you for staying with us. Please review these steps before you leave.</p>
                </div>
                <img src="https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?auto=format&fit=crop&w=900&q=80" alt="Packed luggage in a clean room" class="h-48 w-full rounded-md object-cover md:h-72" loading="lazy">
                <ul class="mt-6 grid gap-4 text-sm">
                    @foreach([
                        'Check-out Time '.$booking->effectiveCheckoutTimeFormatted(),
                        'Ensure all belongings are collected.',
                        'Turn off lights and AC.',
                        'Leave the keys on the table.',
                        'Thank you for staying with us!',
                    ] as $item)
                        <li class="flex items-start gap-3">
                            <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" />
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
                @if($guideCats->count())
                    <a href="#guide-grid" class="guest-primary-btn mt-8 w-full">View Other Info</a>
                    <div id="guide-grid" class="guest-guide-grid mt-8">
                        @foreach($guideCats as $category)
                            @php
                                $colors = $categoryColor;
                                $displayTitle = $category->pivot->custom_title ?: $category->title;
                                $displayDescription = $category->pivot->custom_description ?: $category->description;
                            @endphp
                            <x-guide-panel
                                :href="route('guest.category', [$booking->booking_id, $booking->token, $category])"
                                :icon="$category->slug"
                                :guest-icon="$category->guest_icon"
                                :title="$displayTitle"
                                :description="$displayDescription"
                                :tone="$colors[0]"
                                :accent="$colors[1]"
                                :wide="$category->slug === 'checkout-instructions'"
                            />
                        @endforeach
                    </div>
                @endif
            </div>
            </div>
            @endif
        @else
            @if(count($parkingSteps) > 0)
                <x-step-wizard :steps="$parkingSteps" type="parking" :next-section="count($checkinSteps) > 0 ? 'step-wizard-checkin' : 'guest-guide-section'" />
            @endif
            @if(count($checkinSteps) > 0)
                <div id="step-wizard-checkin-wrapper" style="{{ count($parkingSteps) > 0 ? 'display:none' : '' }}">
                    <x-step-wizard :steps="$checkinSteps" type="checkin" next-section="guest-guide-section" :booking-id="$booking->booking_id" :token="$booking->token" />
                </div>
            @endif
            <div id="guest-guide-section" {{ (count($checkinSteps) > 0 || count($parkingSteps) > 0) && $booking->status !== 'checked_in' ? 'style=display:none' : '' }}>
            <div class="guest-portal-card">
                <div class="guest-status-bar">
                    <div>
                        @if($siteLogo)
                            <img src="{{ url('/img/'.$siteLogo) }}" alt="" class="h-8 max-w-[140px] w-auto object-contain">
                        @endif
                    </div>
                    <span class="guest-status-pill is-checked">
                        <x-icon name="check" class="h-4 w-4" />
                        Checked in
                    </span>
                </div>
            </div>

            @if($property->latitude && $property->longitude)
                <div class="guest-portal-card mt-4 p-6">
                    <x-weather-badge :property="$property" />
                </div>
            @endif

            <div class="guest-portal-card mt-4">
                @if($locks->isNotEmpty())
                    <div class="p-6 pb-0">
                        <div class="grid gap-6 {{ $locks->count() > 1 ? 'sm:grid-cols-2' : '' }}">
                            @foreach($locks as $entry)
                                <x-lock-card
                                    :booking-id="$booking->booking_id"
                                    :token="$booking->token"
                                    :lock-id="$entry['lock']->id"
                                    :lock-label="$locks->count() > 1 ? $entry['lock']->label : null"
                                    :lock-status="$entry['status']"
                                />
                            @endforeach
                        </div>
                    </div>
                @endif
                <div id="guide-grid" class="guest-guide-grid p-6">
                    @foreach($guideCats as $category)
                        @php
                            $colors = $categoryColor;
                            $displayTitle = $category->pivot->custom_title ?: $category->title;
                            $displayDescription = $category->pivot->custom_description ?: $category->description;
                        @endphp
                        <x-guide-panel
                            :href="route('guest.category', [$booking->booking_id, $booking->token, $category])"
                            :icon="$category->slug"
                            :guest-icon="$category->guest_icon"
                            :title="$displayTitle"
                            :description="$displayDescription"
                            :tone="$colors[0]"
                            :accent="$colors[1]"
                            :wide="$category->slug === 'checkout-instructions'"
                        />
                    @endforeach
                </div>
            </div>

            <div class="guest-portal-card mt-4 p-6">
                <div class="guest-stay-grid">
                    <div class="guest-stay-tile">
                        <div class="guest-stay-tile-icon">
                            <x-icon name="guests" class="h-5 w-5" />
                        </div>
                        <p class="guest-stay-tile-label">Guest</p>
                        <p class="guest-stay-tile-date">{{ $booking->guest_name }}</p>
                    </div>
                    <div class="guest-stay-tile">
                        <div class="guest-stay-tile-icon">
                            <x-icon name="calendar" class="h-5 w-5" />
                        </div>
                        <p class="guest-stay-tile-label">Check-Out</p>
                        <p class="guest-stay-tile-date">{{ $booking->check_out_date->format('M d, Y') }} &middot; {{ $booking->effectiveCheckoutTimeFormatted() }} {{ $booking->nightsLabel() }}</p>
                    </div>
                </div>
            </div>
            </div>
        @endif
    </div>
</section>
</x-guest-layout>
