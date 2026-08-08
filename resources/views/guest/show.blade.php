<x-guest-layout :booking="$booking" :property="$property" :title="$property->name" :state="$state">
@php
    $categories = isset($categories) ? $categories : collect();
    $checkinSteps = isset($checkinSteps) ? $checkinSteps : [];
    $checkoutSteps = isset($checkoutSteps) ? $checkoutSteps : [];
    $parkingSteps = isset($parkingSteps) ? $parkingSteps : [];
    $heroImg = $property->heroImageUrl();
    $welcomeBannerImg = null;
    $welcomeMessageClean = $welcomeMessage ?? '';
    if (!empty($welcomeMessageClean) && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $welcomeMessageClean, $m)) {
        $welcomeBannerImg = $m[1];
        $welcomeMessageClean = preg_replace('/<img[^>]*>/i', '', $welcomeMessageClean, 1);
    }
    $displayHeroImg = $welcomeBannerImg ?: $heroImg;
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

        @if($state === 'identity')
            <div class="guest-portal-card">
                <div class="guest-status-bar">
                    <div>
                        <p class="guest-status-kicker">{{ $property->name }}</p>
                    </div>
                    <span class="guest-status-pill">
                        <x-icon name="alert-triangle" class="h-4 w-4" />
                        Not checked in
                    </span>
                </div>
                {{-- Step indicator: big circled current step, dash-separated others (persistent across all steps, top of card) --}}
                <div class="px-6 pt-5 step-indicator">
                    <span class="step-num is-current" data-num="1" id="step-num-1">1</span>
                    <span class="step-dash">&mdash;</span>
                    <span class="step-num" data-num="2" id="step-num-2">2</span>
                    <span class="step-dash">&mdash;</span>
                    <span class="step-num" data-num="3" id="step-num-3">3</span>
                </div>
                {{-- Hero image: persistent/static across all check-in steps --}}
                <img src="{{ $displayHeroImg }}" alt="{{ $property->name }}" class="w-full block rounded-xl mt-4">

                <form id="guest-booking-form" method="post" data-skip-loading enctype="multipart/form-data" action="{{ route('guest.identity', [$booking->booking_id, $booking->token]) }}" class="guest-booking-card">
                    @csrf

                    {{-- ══════════════════ STEP 1 — Welcome + Booking details (read-only) ══════════════════ --}}
                    <div class="idw-step" data-step="1">
                        <div class="guest-stay-grid mt-5">
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
                                <p class="guest-stay-tile-date">{{ $booking->check_out_date->format('M d, Y') }}</p>
                                <p class="guest-stay-tile-time">11:00 AM</p>
                            </div>
                        </div>
                        <div class="px-0 pt-5 pb-2">
                            <h2 class="text-xl font-extrabold text-slate-950">Welcome, {{ $booking->guest_first_name ?? $booking->guest_name }}!</h2>
                            <div class="mt-2 text-sm leading-6 text-slate-600">{!! $welcomeMessageClean !!}</div>
                        </div>
                        @php
                            $isRegistrationComplete = filled($booking->guest_name)
                                && filled($booking->email)
                                && filled($booking->phone)
                                && ! is_null($booking->parking_needed)
                                && $booking->photo_id_received;
                        @endphp
                        @if($isRegistrationComplete)
                            <button type="button" class="guest-primary-btn guest-primary-btn-lg is-go mt-6 w-full" data-next="2">
                                Begin Check In
                                <x-icon name="arrow-right" class="h-5 w-5 ml-1 inline-block align-middle" />
                            </button>
                        @else
                            <button type="button" class="guest-primary-btn guest-primary-btn-lg mt-6 w-full" data-next="2">
                                Begin Registration
                                <x-icon name="arrow-right" class="h-5 w-5 ml-1 inline-block align-middle" />
                            </button>
                        @endif
                    </div>

                    {{-- ══════════════════ STEP 2 — Phone, Email, Parking, Check-in time ══════════════════ --}}
                    <div class="idw-step hidden" data-step="2">
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
                            <p class="text-sm font-bold">Will you be parking a vehicle at the property?</p>
                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm font-semibold hover:bg-slate-50">
                                    <input type="radio" name="parking_needed" value="1" class="accent-blue-600">
                                    Yes, I am parking
                                </label>
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm font-semibold hover:bg-slate-50">
                                    <input type="radio" name="parking_needed" value="0" class="accent-blue-600">
                                    No, not parking
                                </label>
                            </div>
                        </div>
                        @endif

                        {{-- Check-in time --}}
                        <div class="mt-5">
                            <label class="text-sm font-bold">What time are you planning to check in?
                                <select name="checkin_time_preference" required class="guest-input mt-2 @error('checkin_time_preference') border-red-400 @enderror" aria-describedby="@error('checkin_time_preference') checkin-time-error @enderror">
                                    <option value="" disabled {{ old('checkin_time_preference', $booking->checkin_time_preference) ? '' : 'selected' }}>Select a time</option>
                                    @foreach($checkinTimeOptions as $value => $label)
                                        <option value="{{ $value }}" {{ old('checkin_time_preference', $booking->checkin_time_preference) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            @error('checkin_time_preference')
                                <span id="checkin-time-error" class="guest-field-error">{{ $message }}</span>
                            @enderror
                            <p class="mt-1 text-xs text-slate-400">This helps us prepare for your arrival and unlocks your check-in details at the selected time.</p>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <button type="button" class="guest-outline-btn w-full" data-prev="1">Back</button>
                            <button type="button" class="guest-primary-btn w-full" data-next="3">Continue</button>
                        </div>
                    </div>

                    {{-- ══════════════════ STEP 3 — ID capture ══════════════════ --}}
                    <div class="idw-step hidden" data-step="3">
                        @if($booking->photo_id_received)
                        <div class="mt-5 text-center">
                            <div class="guest-big-check mx-auto">
                                <x-icon name="check" class="h-8 w-8" />
                            </div>
                            <p class="mt-4 font-semibold text-slate-950">ID already received</p>
                            <p class="mt-1 text-sm text-slate-500">No need to upload it again, you're all set to submit.</p>
                        </div>
                        @else
                        <div class="mt-5" id="id-capture-section">
                            @if($booking->decline_reason)
                                <div class="guest-detail-banner mb-4" style="background:#fef3c7;border-color:#fde68a;">
                                    <span class="guest-detail-banner-icon" style="color:#92400e;">
                                        <x-icon name="alert-triangle" class="h-5 w-5" />
                                    </span>
                                    <div>
                                        <p class="guest-detail-banner-title" style="color:#92400e;">New Photo ID Needed</p>
                                        <p class="guest-detail-banner-sub">{{ $booking->decline_reason }}</p>
                                    </div>
                                </div>
                            @endif
                            <p class="text-sm font-bold mb-3">Photo ID <span class="text-red-500">*</span></p>
                            <div id="camera-container" class="relative w-full rounded-xl overflow-hidden bg-black hidden" style="aspect-ratio:16/9">
                                <video id="camera-stream" class="w-full h-full object-cover" autoplay playsinline muted></video>
                                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                    <div class="w-[88%] rounded-lg border-4 border-white" style="aspect-ratio:1.586/1;box-shadow:0 0 0 9999px rgba(0,0,0,0.45)"></div>
                                </div>
                                <p id="camera-instruction-label" class="pointer-events-none absolute top-2 left-0 right-0 text-center text-white text-sm font-bold" style="text-shadow:0 1px 3px rgba(0,0,0,0.8)"></p>
                            </div>
                            <div id="capture-btn-wrapper" class="hidden mt-3 flex flex-col items-center gap-2">
                                <button type="button" id="capture-btn" class="bg-slate-900 text-white rounded-full w-16 h-16 flex items-center justify-center shadow-xl border-4 border-white mx-auto">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
                                </button>
                                <span class="text-xs font-semibold text-slate-600">Tap to capture</span>
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
                            <div id="upload-zone-trigger" class="guest-upload guest-upload-id mt-3 cursor-pointer" onclick="startCamera('front')">
                                @if($booking->id_type === 'passport')
                                <img src="{{ asset('id_icons/passportID.png') }}" alt="Passport example">
                                @else
                                <img src="{{ asset('id_icons/frontID.jpg') }}" alt="Front of ID example">
                                @endif
                            </div>
                            @if($booking->id_type === 'passport')
                            <p id="upload-zone-trigger-front-label" class="mt-2 text-center font-bold">Tap to take photo of passport data page</p>
                            @else
                            <p id="upload-zone-trigger-front-label" class="mt-2 text-center font-bold">Tap to take photo of front of ID</p>
                            @endif
                            <div id="upload-zone-trigger-back" class="guest-upload guest-upload-id mt-3 cursor-pointer hidden" onclick="startCamera('back')">
                                <img src="{{ asset('id_icons/backID.jpg') }}" alt="Back of ID example">
                            </div>
                            <p id="upload-zone-trigger-back-label" class="mt-2 text-center font-bold hidden">Tap to take photo of back of ID</p>
                            <input type="hidden" name="photo_id" id="photo-id-data">
                            <input type="hidden" name="photo_id_back" id="photo-id-back-data">
                        </div>
                        @endif

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            <button type="button" class="guest-outline-btn w-full" data-prev="2">Back</button>
                            @if($booking->photo_id_received)
                                @if($booking->isApproved())
                                    <button type="submit" class="guest-primary-btn w-full">Continue</button>
                                @else
                                    <button type="button" class="guest-primary-btn w-full" disabled style="opacity:.5;cursor:not-allowed">Continue</button>
                                @endif
                            @else
                                <button type="submit" class="guest-primary-btn w-full">Submit details</button>
                            @endif
                        </div>
                        <p class="mt-3 text-center text-xs leading-5 text-slate-500">Your information is used only for secure check-in verification.</p>
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
                    }

                    @if($booking->needsIdApproval())
                        goToStep(3);
                    @endif

                    document.querySelectorAll("[data-next]").forEach(function(btn) {
                        btn.addEventListener("click", function() {
                            var step = btn.closest(".idw-step");
                            if (step && !step.reportValidity) {
                                // no-op, older browsers
                            }
                            if (step && step.querySelector("input:invalid")) {
                                var invalid = step.querySelector("input:invalid");
                                invalid.reportValidity();
                                return;
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
                })();

                var currentSide = "front";
                var stream = null;
                var photoIdRequired = {{ $booking->photo_id_received ? 'false' : 'true' }};
                var isPassport = {{ $booking->id_type === 'passport' ? 'true' : 'false' }};

                // Downsamples the captured image, computes a Laplacian-based sharpness
                // score (real focus/blur detection, not just brightness contrast), and a
                // grid edge-density heuristic as a lightweight (non-OCR) "is there
                // text-like structure here" signal.
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
                            document.getElementById("capture-btn").title = label;
                            document.getElementById("camera-instruction-label").textContent = label;
                        })
                        .catch(function() { alert("Camera access denied. Please allow camera permissions and try again."); });
                }

                function stopCamera() {
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
                    document.getElementById("capture-btn").addEventListener("click", function() {
                        var video = document.getElementById("camera-stream");
                        var crop = __idwGetGuideCropRect(video);
                        var canvas = document.createElement("canvas");
                        canvas.width = crop.w;
                        canvas.height = crop.h;
                        canvas.getContext("2d").drawImage(video, crop.x, crop.y, crop.w, crop.h, 0, 0, crop.w, crop.h);
                        var dataUrl = canvas.toDataURL("image/jpeg", 0.92);
                        stopCamera();
                        if (currentSide === "front") {
                            var img = document.getElementById("front-preview");
                            img.src = dataUrl;
                            document.getElementById("front-preview-block").classList.remove("hidden");
                            img.onload = function() {
                                var ok = checkBlur(img, document.getElementById("front-blur-warning"));
                                if (ok) {
                                    document.getElementById("photo-id-data").value = dataUrl;
                                    if (!isPassport) {
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
                                if (ok) { document.getElementById("photo-id-back-data").value = dataUrl; }
                            };
                        }
                    });

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

                document.getElementById("guest-booking-form").addEventListener("submit", function(e) {
                    e.preventDefault();

                    if (photoIdRequired) {
                        var front = document.getElementById("photo-id-data").value;
                        var back = document.getElementById("photo-id-back-data").value;
                        var frontBlur = document.getElementById("front-blur-warning");
                        var backBlur = document.getElementById("back-blur-warning");
                        if (!front) { alert("Please take a photo of the front of your ID."); return; }
                        if (!isPassport && !back) { alert("Please take a photo of the back of your ID."); return; }
                        if (!frontBlur.classList.contains("hidden")) { alert("Front ID photo is blurry. Please retake."); return; }
                        if (!isPassport && !backBlur.classList.contains("hidden")) { alert("Back ID photo is blurry. Please retake."); return; }
                    }

                    function b64toBlob(b64) {
                        var arr = b64.split(","), mime = arr[0].match(/:(.*?);/)[1];
                        var bstr = atob(arr[1]), n = bstr.length, u8 = new Uint8Array(n);
                        for (var i = 0; i < n; i++) u8[i] = bstr.charCodeAt(i);
                        return new Blob([u8], {type: mime});
                    }
                    var form = document.getElementById("guest-booking-form");
                    var fd = new FormData(form);
                    if (photoIdRequired) {
                        fd.set("photo_id", b64toBlob(document.getElementById("photo-id-data").value), "front.jpg");
                        if (!isPassport) {
                            fd.set("photo_id_back", b64toBlob(document.getElementById("photo-id-back-data").value), "back.jpg");
                        }
                    }

                    var submitBtn = form.querySelector('[type="submit"]');
                    var submitBtnOrigHtml = submitBtn ? submitBtn.innerHTML : null;
                    function resetSubmitBtn() {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = submitBtnOrigHtml;
                        }
                    }
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="ui-spinner"></span><span>Submitting…</span>';
                    }

                    fetch(form.action, { method: "POST", body: fd })
                        .then(function(r) {
                            if (r.ok || r.redirected) {
                                window.location = r.url;
                            } else {
                                resetSubmitBtn();
                                return r.text().then(function(t) {
                                    console.error("Server error:", t);
                                    alert("Submission failed (server error). Please try again.");
                                });
                            }
                        })
                        .catch(function(e) {
                            resetSubmitBtn();
                            console.error(e);
                            alert("Submission failed. Please try again.");
                        });
                });
                </script>
            </div>
        @elseif($state === 'waiting')
            <div class="guest-portal-card">
                <div class="guest-status-bar">
                    <div>
                        <p class="guest-status-kicker">{{ $property->name }}</p>
                    </div>
                    <span class="guest-status-pill is-ready">
                        <x-icon name="calendar" class="h-4 w-4" />
                        Not checked in
                    </span>
                </div>
                <img src="{{ $heroImg }}" alt="{{ $property->name }}" class="w-full block" style="height:auto">
                <div class="px-6 pt-8 pb-2 text-center">
                    <div class="guest-big-check">
                        <x-icon name="check" class="h-8 w-8" />
                    </div>
                    <h2 class="mt-4 text-xl font-extrabold text-slate-950">You're All Set{{ $booking->guest_first_name ? ', '.$booking->guest_first_name : '' }}!</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">We'll see you soon.</p>
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
                            <p class="guest-stay-tile-date">{{ $booking->check_out_date->format('M d, Y') }}</p>
                            <p class="guest-stay-tile-time">11:00 AM</p>
                        </div>
                    </div>

                    <div class="guest-detail-banner">
                        <span class="guest-detail-banner-icon">
                            <x-icon name="check" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="guest-detail-banner-title">Check-In Details Available</p>
                            <p class="guest-detail-banner-sub">{{ $booking->check_in_date->format('M d, Y') }} at {{ $booking->effectiveCheckinTimeFormatted() }}</p>
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
                        <p class="guest-status-kicker">{{ $property->name }}</p>
                        <h1 class="guest-status-title">Verify your location</h1>
                        <p class="mt-2 text-sm leading-6 text-slate-600">You are not checked in yet. Verify that you are at the property to unlock the welcome guide.</p>
                    </div>
                    <span class="guest-status-pill">
                        <x-icon name="alert-triangle" class="h-4 w-4" />
                        Not checked in
                    </span>
                </div>
            <div class="p-6 md:p-10">
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
                        <p class="guest-stay-tile-date">{{ $booking->check_out_date->format('M d, Y') }}</p>
                        <p class="guest-stay-tile-time">11:00 AM</p>
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
                            <p class="guest-detail-banner-title">Check-In Details Available</p>
                            <p class="guest-detail-banner-sub">{{ $booking->check_in_date->format('M d, Y') }} at {{ $booking->effectiveCheckinTimeFormatted() }}</p>
                        </div>
                    </div>
                @endif
                <p class="mx-auto mt-8 max-w-md text-center text-sm font-semibold leading-6">We need to verify that you are at the property location.</p>
                <div class="mx-auto mt-8 grid h-28 w-28 place-items-center rounded-full bg-blue-50 text-[#082b49]">
                    <x-icon name="map" class="h-12 w-12" />
                </div>
                <p class="mt-8 text-center text-sm font-semibold">Getting your location...</p>
                <p class="mx-auto mt-8 max-w-md text-center text-sm leading-6 text-slate-600">This helps us ensure a smooth and secure check-in process.</p>
                <div id="gps-ajax-message" class="hidden"></div>
                <div class="mx-auto mt-10 grid max-w-md gap-3">
                    <button id="gps-ajax-verify-btn" type="button" data-url="{{ route('guest.gps', [$booking->booking_id, $booking->token]) }}" data-csrf="{{ csrf_token() }}" class="guest-primary-btn is-go w-full">Verify Location</button>
                    <a href="{{ route('guest.show', [$booking->booking_id, $booking->token]) }}" class="guest-outline-btn w-full">Cancel</a>
                </div>
            </div>
            </div>
        @elseif($state === 'checkout')
            @if(count($checkoutSteps) > 0)
                <x-step-wizard :steps="$checkoutSteps" type="checkout" next-section="checkout-complete" :booking-id="$booking->booking_id" :token="$booking->token" />
                <div class="guest-portal-card" id="checkout-complete" style="display:none">
                    <div class="flex flex-col items-center justify-center gap-4 px-6 py-16 text-center md:py-24">
                        <span class="guest-status-pill is-checked">
                            <x-icon name="check" class="h-4 w-4" />
                            Checked out
                        </span>
                        <p class="guest-status-kicker">{{ $property->name }}</p>
                        <h1 class="guest-status-title">You're all checked out</h1>
                        <p class="max-w-md text-sm leading-6 text-slate-600">Thanks so much for staying with us. Safe travels!</p>
                    </div>
                </div>
            @else
            <div class="guest-guide-open">
                <div class="guest-status-bar">
                    <div>
                        <p class="guest-status-kicker">{{ $property->name }}</p>
                        <h1 class="guest-status-title">Check-out instructions</h1>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Thank you for staying with us. Please review these steps before you leave.</p>
                    </div>
                    <span class="guest-status-pill is-checked">
                        <x-icon name="check" class="h-4 w-4" />
                        Checked in
                    </span>
                </div>
            <div class="guest-guide-body">
                <img src="https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?auto=format&fit=crop&w=900&q=80" alt="Packed luggage in a clean room" class="h-48 w-full rounded-md object-cover md:h-72" loading="lazy">
                <ul class="mt-6 grid gap-4 text-sm">
                    @foreach([
                        'Check-out Time 11:00 AM',
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
            <div class="guest-guide-open" id="guest-guide-section" {{ (count($checkinSteps) > 0 || count($parkingSteps) > 0) && $booking->status !== 'checked_in' ? 'style=display:none' : '' }}>
                <div class="guest-status-bar">
                    <div>
                        <p class="guest-status-kicker">{{ $property->name }}</p>
                        <h1 class="guest-status-title">Welcome Guide</h1>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Everything you need during your stay is ready below.</p>
                    </div>
                    <span class="guest-status-pill is-checked">
                        <x-icon name="check" class="h-4 w-4" />
                        Checked in
                    </span>
                </div>
            <div class="guest-guide-body">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-sm leading-6 text-slate-500">Explore information about your stay.</p>
                </div>
                <div id="guide-grid" class="guest-guide-grid mt-10">
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
            </div>
        @endif
    </div>
</section>
</x-guest-layout>
