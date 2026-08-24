<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Category;
use App\Models\PropertyLock;
use App\Models\InstructionStep;
use App\Models\CategoryPage;
use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\SeamService;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function show(string $bookingId, string $token)
    {
        $booking = $this->booking($bookingId, $token);
        return $this->renderPortal($booking);
    }

    private function renderPortal(Booking $booking)
    {
        $booking->load(['property.categories', 'property.amenities', 'property.instructionSteps']);
        $state = $this->state($booking);

        ActivityLogService::guest('portal_viewed', "Guest {$booking->guest_name} viewed the portal (state: {$state}).", 'guest_portal', [
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'actor_name'   => $booking->guest_name,
            'actor_email'  => $booking->email,
            'metadata'     => ['state' => $state, 'booking_ref' => $booking->booking_id],
        ]);

        return view('guest.show', [
            'booking'       => $booking,
            'property'      => $booking->property,
            'state'         => $state,
            'categories'    => $this->availableCategories($booking),
            'locks'         => $this->resolveLocks($booking),
            'welcomeMessage' => $booking->welcome_message ?: \App\Models\Setting::getValue('default_intro', 'We are glad to have you. Please complete the following details prior to check-in.'),
            'gpsRadius'     => (int) Setting::getValue('gps_radius_meters', 150),
            'gpsVerifyMessage' => Setting::getValue('gps_verify_message', "It's Go Time!"),
            'backgroundCheckStepName' => Setting::getValue('background_check_step_name', 'Background Check'),
            'backgroundCheckStepInstructions' => Setting::getValue('background_check_step_instructions', 'Please be on the lookout for an email from Airbnb so that you can submit the required hold for incidentals. This hold is refunded after checkout.'),
            'checkinSteps'  => ($state === 'guide' && ! $booking->instructionsCompleted()) ? $this->checkinSteps($booking) : [],
            'checkoutSteps' => $this->checkoutSteps($booking),
            'parkingSteps'  => ($state === 'guide' && ! $booking->instructionsCompleted()) ? $this->parkingSteps($booking) : [],
            'checkinTimeOptions' => $this->checkinTimeOptions(),
            'checkoutTimeOptions' => $this->checkoutTimeOptions(),
        ]);
    }

    public function checkinByReservation(Request $request)
    {
        $rid = $request->query('RID');
        abort_unless($rid, 404);

        $booking = Booking::where('reservation_id', $rid)->firstOrFail();

        return $this->renderPortal($booking);
    }

    public function verifyReservationLogin(Request $request)
    {
        $rid = $request->input('RID');
        $booking = Booking::where('reservation_id', $rid)->firstOrFail();

        $data = $request->validate([
            'phone' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $emailMatch = strtolower(trim($data['email'])) === strtolower(trim($booking->email));
        $phoneMatch = preg_replace('/\\D/', '', $data['phone']) === preg_replace('/\\D/', '', $booking->phone);

        if (! $emailMatch || ! $phoneMatch) {
            ActivityLogService::security('guest_login_failed', "Failed RID login attempt for reservation: {$rid}.", [
                'actor_type' => 'guest',
                'severity'   => 'warning',
                'metadata'   => ['reservation_id' => $rid],
            ]);
            return back()->withInput()->with('error', 'Phone and email do not match our records.');
        }

        $booking->update(['guest_authenticated_at' => now()]);
        \App\Services\GuestSessionService::refreshCookie($booking);

        ActivityLogService::guest('guest_login_verified', "Guest {$booking->guest_name} logged in via reservation ID.", 'guest_portal', [
            'booking_id'  => $booking->id,
            'property_id' => $booking->property_id,
            'actor_name'  => $booking->guest_name,
            'actor_email' => $booking->email,
            'severity'    => 'success',
        ]);

        return redirect()->route('checkin.rid', ['RID' => $rid]);
    }

    public function login(Request $request, string $bookingId, string $token)
    {
        $booking = $this->booking($bookingId, $token);

        // Effective parking answer after this request: the newly-submitted
        // value if present, otherwise whatever's already on the booking.
        // Vehicle info is only required once parking is confirmed "yes" —
        // task 34.
        $parkingAnswer = $request->filled('parking_needed')
            ? filter_var($request->input('parking_needed'), FILTER_VALIDATE_BOOLEAN)
            : $booking->parking_needed;

        $data = $request->validate([
            'guest_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'parking_needed' => [is_null($booking->parking_needed) ? 'required' : 'nullable'],
            'checkin_time_preference' => ['required', 'string'],
            'checkout_time_preference' => ['nullable', 'string'],
            'vehicle_make_model' => [
                $parkingAnswer && ! $booking->vehicle_make_model ? 'required' : 'nullable',
                'string', 'max:255',
            ],
            'license_plate_photo' => [
                $parkingAnswer && ! $booking->license_plate_photo_path ? 'required' : 'nullable',
                'image', 'max:8192',
            ],
        ]);

        $newCheckinPreference = $data['checkin_time_preference'];
        $newCheckoutPreference = $data['checkout_time_preference'] ?? null;

        $updates = [
            'guest_name' => $data['guest_name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'parking_needed' => array_key_exists('parking_needed', $data) && $data['parking_needed'] !== null
                ? filter_var($data['parking_needed'], FILTER_VALIDATE_BOOLEAN)
                : $booking->parking_needed,
            'checkin_time_preference' => $newCheckinPreference,
            'checkout_time_preference' => $newCheckoutPreference,
            'guest_authenticated_at' => now(),
        ];

        // Task 0: a non-standard time request needs admin approval before it
        // takes effect (a charge may apply — see task 26 billing fields). A
        // request matching the property's standard time needs no review. If
        // the guest resubmits the same value as before, don't clobber an
        // existing admin decision (approved/denied) back to pending.
        if ($newCheckinPreference !== $booking->checkin_time_preference) {
            $updates['checkin_time_status'] = $newCheckinPreference && $newCheckinPreference !== $booking->standardCheckinTime()
                ? 'pending'
                : null;
        }

        if ($newCheckoutPreference !== $booking->checkout_time_preference) {
            $updates['checkout_time_status'] = $newCheckoutPreference && $newCheckoutPreference !== $booking->standardCheckoutTime()
                ? 'pending'
                : null;
        }

        if ($parkingAnswer) {
            $updates['vehicle_make_model'] = $data['vehicle_make_model'] ?? $booking->vehicle_make_model;
        }

        if ($request->hasFile('license_plate_photo')) {
            $updates['license_plate_photo_path'] = $request->file('license_plate_photo')->store('license-plates');
        }

        $booking->update($updates);

        $booking->recalculateParkingCharge();

        \App\Services\GuestSessionService::refreshCookie($booking);

        ActivityLogService::guest('guest_login_verified', "Guest {$booking->guest_name} completed login step.", 'guest_portal', [
            'booking_id'  => $booking->id,
            'property_id' => $booking->property_id,
            'actor_name'  => $booking->guest_name,
            'actor_email' => $booking->email,
            'severity'    => 'success',
        ]);

        return response()->json(['ok' => true]);
    }

    public function verifyLogin(string $bookingId, string $token, \Illuminate\Http\Request $request)
    {
        $booking = Booking::where('booking_id', $bookingId)->first();
        if (! $booking) {
            abort(404);
        }
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);
        $emailMatch = strtolower(trim($data['email'])) === strtolower(trim($booking->email));
        $phoneMatch = preg_replace('/\\D/', '', $data['phone']) === preg_replace('/\\D/', '', $booking->phone);
        if (! $emailMatch || ! $phoneMatch) {
            ActivityLogService::security('guest_login_failed', "Failed guest login attempt for booking: {$bookingId}.", [
                'actor_type' => 'guest',
                'severity'   => 'warning',
                'metadata'   => ['booking_id' => $bookingId],
            ]);
            return back()->withInput()->with('error', 'Phone and email do not match our records.');
        }
        \App\Services\GuestSessionService::refreshCookie($booking);
        ActivityLogService::guest('guest_login_verified', "Guest {$booking->guest_name} verified login.", 'guest_portal', [
            'booking_id'  => $booking->id,
            'property_id' => $booking->property_id,
            'actor_name'  => $booking->guest_name,
            'actor_email' => $booking->email,
            'severity'    => 'success',
        ]);
        return redirect()->route('guest.show', [$bookingId, $token]);
    }
    public function confirmCheckin(string $bookingId, string $token)
    {
        $booking = $this->booking($bookingId, $token);
        $booking->update([
            'status'        => 'currently_hosting',
            'checked_in_at' => now(),
        ]);
        \App\Services\GuestAlertService::send('checkin_completed', $booking);
        ActivityLogService::guest('guest_confirmed_checkin', "Guest {$booking->guest_name} confirmed check-in.", 'check', [
            'booking_id'  => $booking->id,
            'property_id' => $booking->property_id,
            'actor_name'  => $booking->guest_name,
            'actor_email' => $booking->email,
            'severity'    => 'success',
        ]);
        return response()->json(['ok' => true]);
    }

    public function confirmCheckout(string $bookingId, string $token)
    {
        $booking = $this->booking($bookingId, $token);
        $booking->update([
            'status'         => 'checked_out',
            'checked_out_at' => now(),
        ]);
        \App\Services\GuestAlertService::send('checkout_completed', $booking);
        ActivityLogService::guest('guest_confirmed_checkout', "Guest {$booking->guest_name} confirmed check-out.", 'check', [
            'booking_id'  => $booking->id,
            'property_id' => $booking->property_id,
            'actor_name'  => $booking->guest_name,
            'actor_email' => $booking->email,
            'severity'    => 'success',
        ]);
        return response()->json(['ok' => true]);
    }

    public function createDepositIntent(string $bookingId, string $token)
    {
        $booking = $this->booking($bookingId, $token);
        $service = app(\App\Services\Payments\PaymentService::class);

        if (! $service->isConfigured()) {
            return response()->json(['ok' => false, 'error' => 'Payments are not available right now. Please contact us.'], 503);
        }

        if ($booking->isDepositCaptured() || $booking->deposit_verified_at) {
            return response()->json(['ok' => false, 'error' => 'Deposit already paid.'], 422);
        }

        $amountCents = $booking->effectiveDepositAmountCents();

        if ($amountCents <= 0) {
            return response()->json(['ok' => false, 'error' => 'No deposit is configured for this stay.'], 422);
        }

        $result = $service->createPendingIntent(
            $booking,
            \App\Models\Charge::TYPE_DEPOSIT,
            $amountCents,
            'precheckin_approval',
            "Incidentals hold for booking {$booking->booking_id}"
        );

        return response()->json([
            'ok' => true,
            'client_secret' => $result['client_secret'],
            'publishable_key' => config('services.stripe.key'),
            'amount_cents' => $amountCents,
        ]);
    }

    public function confirmDepositPayment(Request $request, string $bookingId, string $token)
    {
        $booking = $this->booking($bookingId, $token);
        $request->validate(['payment_intent_id' => ['required', 'string']]);

        $charge = app(\App\Services\Payments\PaymentService::class)->finalize($request->input('payment_intent_id'));

        if (! $charge || $charge->booking_id !== $booking->id) {
            return response()->json(['ok' => false, 'error' => 'Payment not found.'], 404);
        }

        if ($charge->status !== \App\Models\Charge::STATUS_CAPTURED) {
            return response()->json(['ok' => false, 'error' => 'Payment was not successful. Please try again.'], 422);
        }

        ActivityLogService::guest('deposit_paid_online', "Guest {$booking->guest_name} paid the incidentals deposit online.", 'check', [
            'booking_id'  => $booking->id,
            'property_id' => $booking->property_id,
            'actor_name'  => $booking->guest_name,
            'actor_email' => $booking->email,
            'severity'    => 'success',
            'metadata'    => ['amount_cents' => $charge->amount_cents, 'charge_id' => $charge->id],
        ]);

        \App\Services\GuestAlertService::send('deposit_paid', $booking);

        return response()->json(['ok' => true]);
    }

    /**
     * Generic charge-intent creation, reusable for any charge type beyond
     * the deposit (parking, early check-in, the portion of late
     * checkout/incidentals not covered by the deposit). $type must be one
     * of Charge::TYPE_* and have a known, positive amount already computed
     * on the booking — this endpoint never accepts an arbitrary
     * client-supplied amount.
     */
    public function createChargeIntent(Request $request, string $bookingId, string $token)
    {
        $booking = $this->booking($bookingId, $token);
        $service = app(\App\Services\Payments\PaymentService::class);

        if (! $service->isConfigured()) {
            return response()->json(['ok' => false, 'error' => 'Payments are not available right now. Please contact us.'], 503);
        }

        $type = $request->validate(['type' => ['required', 'string', 'in:parking,early_checkin,late_checkout,incidentals']])['type'];

        $amountCents = match ($type) {
            \App\Models\Charge::TYPE_PARKING => (int) round(($booking->effectiveParkingCharge() ?? 0) * 100),
            \App\Models\Charge::TYPE_EARLY_CHECKIN => (int) round(($booking->earlyCheckinCharge() ?? 0) * 100),
            \App\Models\Charge::TYPE_LATE_CHECKOUT => (int) round(($booking->lateCheckoutCharge() ?? 0) * 100),
            \App\Models\Charge::TYPE_INCIDENTALS => (int) round(($booking->incidentals_charge ?? 0) * 100),
            default => 0,
        };

        if ($amountCents <= 0) {
            return response()->json(['ok' => false, 'error' => 'Nothing is currently due for this.'], 422);
        }

        // Avoid creating a duplicate outstanding intent for the same type —
        // reuse the existing pending one if there is one already.
        $existing = $booking->charges()->where('type', $type)->where('status', \App\Models\Charge::STATUS_PENDING)->latest()->first();
        if ($existing) {
            return response()->json([
                'ok' => true,
                'client_secret' => null,
                'existing_charge_id' => $existing->id,
                'amount_cents' => $existing->amount_cents,
            ]);
        }

        $result = $service->createPendingIntent($booking, $type, $amountCents, 'guest_initiated');

        return response()->json([
            'ok' => true,
            'client_secret' => $result['client_secret'],
            'publishable_key' => config('services.stripe.key'),
            'amount_cents' => $amountCents,
        ]);
    }

    public function confirmChargePayment(Request $request, string $bookingId, string $token)
    {
        $booking = $this->booking($bookingId, $token);
        $request->validate(['payment_intent_id' => ['required', 'string']]);

        $charge = app(\App\Services\Payments\PaymentService::class)->finalize($request->input('payment_intent_id'));

        if (! $charge || $charge->booking_id !== $booking->id) {
            return response()->json(['ok' => false, 'error' => 'Payment not found.'], 404);
        }

        if ($charge->status !== \App\Models\Charge::STATUS_CAPTURED) {
            return response()->json(['ok' => false, 'error' => 'Payment was not successful. Please try again.'], 422);
        }

        ActivityLogService::guest('charge_paid_online', "Guest {$booking->guest_name} paid a {$charge->type} charge online.", 'check', [
            'booking_id'  => $booking->id,
            'property_id' => $booking->property_id,
            'actor_name'  => $booking->guest_name,
            'actor_email' => $booking->email,
            'severity'    => 'success',
            'metadata'    => ['type' => $charge->type, 'amount_cents' => $charge->amount_cents, 'charge_id' => $charge->id],
        ]);

        return response()->json(['ok' => true]);
    }

    public function submitIdentity(Request $request, string $bookingId, string $token)
    {
        $booking = $this->booking($bookingId, $token);
        // Each side is required only if that specific side is missing (never uploaded,
        // or cleared out by an admin decline) and the booking isn't already marked
        // photo_id_received (e.g. captured outside the guest flow) — a decline on one
        // side should never force re-upload of an already-approved other side.
        $frontRequired = ! $booking->photo_id_received && blank($booking->photo_id_path);
        $backRequired = ! $booking->photo_id_received && blank($booking->photo_id_back_path) && $booking->id_type !== 'passport';

        $requiresContractAcceptance = filled(\App\Models\Setting::getValue('rental_contract', '')) && ! $booking->contract_accepted_at;

        $data = $request->validate([
            'photo_id' => [$frontRequired ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'photo_id_back' => [$backRequired ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'contract_accepted' => [$requiresContractAcceptance ? 'accepted' : 'nullable'],
        ]);

        $advancedStatuses = ['guest_approved', 'awaiting_deposit', 'currently_hosting', 'checked_out'];
        $updates = [
            'status'       => in_array($booking->status, $advancedStatuses, true) ? $booking->status : 'pre_checkin_complete',
            'identity_confirmed_at' => now(),
            'decline_reason' => null,
            'photo_id_received' => true,
        ];

        if ($requiresContractAcceptance) {
            // Forward-only: stamped once, at the moment of acceptance, never
            // re-checked against a "current" version later. If the admin
            // edits the contract text afterward, this guest is not
            // re-prompted — see settings controller for the version bump.
            $updates['contract_version'] = \App\Models\Setting::getValue('rental_contract_version', '1');
            $updates['contract_accepted_at'] = now();
        }

        $archiveFolder = 'photo-ids-archive/'.$booking->booking_id.'-'.\Illuminate\Support\Str::slug($booking->guest_name);

        if ($request->hasFile('photo_id')) {
            if ($booking->photo_id_path && \Storage::disk('local')->exists($booking->photo_id_path)) {
                \Storage::disk('local')->move(
                    $booking->photo_id_path,
                    $archiveFolder.'/'.now()->format('Ymd-His').'-front-'.basename($booking->photo_id_path)
                );
            }
            $updates['photo_id_path'] = $request->file('photo_id')->store('photo-ids');
            $updates['photo_id_front_declined_reason'] = null;
        }
        if ($request->hasFile('photo_id_back')) {
            if ($booking->photo_id_back_path && \Storage::disk('local')->exists($booking->photo_id_back_path)) {
                \Storage::disk('local')->move(
                    $booking->photo_id_back_path,
                    $archiveFolder.'/'.now()->format('Ymd-His').'-back-'.basename($booking->photo_id_back_path)
                );
            }
            $updates['photo_id_back_path'] = $request->file('photo_id_back')->store('photo-ids');
            $updates['photo_id_back_declined_reason'] = null;
        }

        $isFirstCompletion = $booking->status === 'pending';

        $booking->update($updates);

        if ($isFirstCompletion) {
            \App\Services\GuestAlertService::send('registration_received', $booking);
        }

        // Notify admin (and, per settings, the guest) every time a photo ID is
        // submitted — including re-uploads after a decline — not just on the
        // guest's very first completion.
        if ($request->hasFile('photo_id') || $request->hasFile('photo_id_back')) {
            \App\Services\GuestAlertService::send('photo_id_uploaded', $booking);
        }

        ActivityLogService::guest('photo_id_uploaded', "Guest {$booking->guest_name} submitted photo ID and pre-arrival details.", 'photo_id', [
            'booking_id'  => $booking->id,
            'property_id' => $booking->property_id,
            'actor_name'  => $booking->guest_name,
            'actor_email' => $booking->email,
            'severity'    => 'success',
            'metadata'    => ['email' => $booking->email, 'booking_ref' => $booking->booking_id],
        ]);

        return back()
            ->with('success', 'All complete. Your arrival information has been received securely.')
            ->with('identity_complete', true);
    }

    public function parking(Request $request, string $bookingId, string $token)
    {
        $booking = $this->booking($bookingId, $token);
        $data    = $request->validate(['parking_needed' => ['required', 'boolean']]);
        $booking->update($data);
        $booking->recalculateParkingCharge();

        ActivityLogService::guest('parking_answered', "Guest {$booking->guest_name} answered parking question: ".($data['parking_needed'] ? 'Yes' : 'No').".", 'guest_portal', [
            'booking_id'  => $booking->id,
            'property_id' => $booking->property_id,
            'actor_name'  => $booking->guest_name,
            'actor_email' => $booking->email,
            'metadata'    => ['parking_needed' => $data['parking_needed']],
        ]);

        return back()->with('success', 'Parking preference saved.');
    }

    public function verifyGps(Request $request, string $bookingId, string $token)
    {
        $booking = $this->booking($bookingId, $token);
        $data    = $request->validate([
            'latitude'  => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'accuracy'  => ['nullable', 'numeric', 'min:0'],
        ]);

        $property = $booking->property;
        abort_if(! $property->latitude || ! $property->longitude, 422, 'Property GPS coordinates are not configured.');

        $distance = $this->distanceMeters(
            (float) $data['latitude'],
            (float) $data['longitude'],
            (float) $property->latitude,
            (float) $property->longitude
        );
        $radius = (int) Setting::getValue('gps_radius_meters', 150);

        // Browser-reported GPS accuracy is a margin of error, not a guarantee.
        // A guest genuinely on-site can still get a low-precision fix (e.g. indoors),
        // so extend the effective radius by the reported accuracy, capped so a wildly
        // inaccurate/spoofed reading can't be used to pass verification from far away.
        $maxAccuracyBonus = (int) Setting::getValue('gps_accuracy_bonus_cap_meters', 100);
        $accuracy          = isset($data['accuracy']) ? (float) $data['accuracy'] : 0.0;
        $accuracyBonus     = min(max($accuracy, 0), $maxAccuracyBonus);
        $effectiveRadius   = $radius + $accuracyBonus;

        if ($distance > $effectiveRadius) {
            ActivityLogService::guest('gps_failed', "Guest {$booking->guest_name} GPS verification failed (distance: ".round($distance)."m, radius: {$radius}m, accuracy: ".round($accuracy)."m, effective radius: ".round($effectiveRadius)."m).", 'gps', [
                'booking_id'  => $booking->id,
                'property_id' => $booking->property_id,
                'actor_name'  => $booking->guest_name,
                'actor_email' => $booking->email,
                'severity'    => 'warning',
                'metadata'    => [
                    'submitted_lat'    => $data['latitude'],
                    'submitted_lon'    => $data['longitude'],
                    'distance_meters'  => round($distance),
                    'radius_meters'    => $radius,
                    'accuracy_meters'  => round($accuracy),
                    'effective_radius' => round($effectiveRadius),
                ],
            ]);

            return response()->json([
                'ok'       => false,
                'message'  => 'Your location appears to be outside the verification radius. Please contact guest services for manual approval.',
                'distance' => round($distance),
            ], 422);
        }

        $booking->update([
            'gps_verified'  => true,
        ]);

        ActivityLogService::guest('gps_verified', "Guest {$booking->guest_name} GPS verified and checked in (distance: ".round($distance)."m, accuracy: ".round($accuracy)."m).", 'gps', [
            'booking_id'  => $booking->id,
            'property_id' => $booking->property_id,
            'actor_name'  => $booking->guest_name,
            'actor_email' => $booking->email,
            'severity'    => 'success',
            'metadata'    => [
                'submitted_lat'    => $data['latitude'],
                'submitted_lon'    => $data['longitude'],
                'distance_meters'  => round($distance),
                'radius_meters'    => $radius,
                'accuracy_meters'  => round($accuracy),
                'effective_radius' => round($effectiveRadius),
            ],
        ]);

        return response()->json(['ok' => true, 'message' => 'Location verified. Your check-in details are unlocked.']);
    }

    public function gpsStatus(string $bookingId, string $token)
    {
        $booking = $this->booking($bookingId, $token);
        return response()->json(['gps_verified' => (bool) $booking->gps_verified]);
    }
    public function idStatus(string $bookingId, string $token)
    {
        $booking = $this->booking($bookingId, $token);
        return response()->json([
            'id_approved' => (bool) ($booking->photo_id_received && $booking->isApproved()),
            'background_check_complete' => $booking->isBackgroundCheckComplete(),
            'deposit_verified' => $booking->isDepositVerified(),
        ]);
    }
    public function category(string $bookingId, string $token, Category $category)
    {
        $booking = $this->booking($bookingId, $token);
        $booking->load(['property.categories', 'property.amenities']);
        $state = $this->state($booking);
        if (! in_array($state, ['checkout_notice', 'checkout_available', 'guide'], true)) {
            return redirect()->route('guest.show', [$booking->booking_id, $booking->token]);
        }

        $categories = $this->availableCategories($booking);
        abort_unless($categories->contains('id', $category->id), 404);
        $category = $categories->firstWhere('id', $category->id);

        $page = CategoryPage::where('property_id', $booking->property_id)
            ->where('category_id', $category->id)
            ->where('active', true)
            ->first();
        $locks = $category->action === 'door_lock'
            ? $this->resolveLocks($booking)
            : collect();
        $localEvents = collect();
        $eventsTotal = 0;
        $eventsHasMore = false;
        if ($category->action === 'local_events' && $booking->property->latitude && $booking->property->longitude) {
            $eventsResult = app(\App\Services\TicketmasterService::class)->findNearbyEvents(
                (float) $booking->property->latitude,
                (float) $booking->property->longitude,
                (int) ($booking->property->events_radius_miles ?? 25)
            );
            $localEvents = collect($eventsResult['events']);
            $eventsTotal = $eventsResult['totalElements'];
            $eventsHasMore = $eventsResult['hasMore'];
        }

        ActivityLogService::guest('category_viewed', "Guest {$booking->guest_name} viewed category: {$category->title}.", 'guest_portal', [
            'booking_id'  => $booking->id,
            'property_id' => $booking->property_id,
            'actor_name'  => $booking->guest_name,
            'actor_email' => $booking->email,
            'metadata'    => ['category' => $category->title, 'category_id' => $category->id],
        ]);

        return view('guest.category', compact('booking', 'category', 'page', 'categories', 'locks', 'localEvents', 'eventsTotal', 'eventsHasMore', 'state'));
    }

    public function moreEvents(Request $request, string $bookingId, string $token, Category $category)
    {
        $booking = $this->booking($bookingId, $token);
        $state = $this->state($booking);
        if (! in_array($state, ['checkout_notice', 'checkout_available', 'guide'], true)) {
            abort(403);
        }
        $categories = $this->availableCategories($booking);
        abort_unless($categories->contains('id', $category->id), 404);
        $category = $categories->firstWhere('id', $category->id);
        abort_unless($category->action === 'local_events', 404);

        $page = max(0, (int) $request->query('page', 0));

        $eventsResult = ['events' => [], 'totalElements' => 0, 'hasMore' => false];
        if ($booking->property->latitude && $booking->property->longitude) {
            $eventsResult = app(\App\Services\TicketmasterService::class)->findNearbyEvents(
                (float) $booking->property->latitude,
                (float) $booking->property->longitude,
                (int) ($booking->property->events_radius_miles ?? 25),
                20,
                $page
            );
        }

        return response()->json($eventsResult);
    }

    public function unlockDoor(string $bookingId, string $token, PropertyLock $lock)
    {
        $booking = $this->booking($bookingId, $token);
        abort_unless($lock->property_id === $booking->property_id, 404);
        try {
            $attempt = app(SeamService::class)->unlock($lock->seam_device_id);
        } catch (\Throwable $e) {
            ActivityLogService::guest('door_unlock_failed', "Guest {$booking->guest_name} failed to send unlock command for {$lock->label}: {$e->getMessage()}", 'guest_portal', [
                'booking_id'  => $booking->id,
                'property_id' => $booking->property_id,
                'actor_name'  => $booking->guest_name,
                'severity'    => 'warning',
                'metadata'    => ['lock_id' => $lock->id, 'seam_device_id' => $lock->seam_device_id],
            ]);
            return response()->json(['ok' => false, 'error' => 'Could not reach the door. Please try again in a moment.'], 502);
        }
        if (! empty($attempt['action_attempt_id'])) {
            \Illuminate\Support\Facades\Cache::put('seam_attempt_lock:'.$attempt['action_attempt_id'], ['lock_id' => $lock->id, 'guest_name' => $booking->guest_name, 'booking_id' => $booking->id], now()->addMinutes(10));
        }
        ActivityLogService::guest('door_unlock_attempted', "Guest {$booking->guest_name} sent unlock command for {$lock->label}.", 'guest_portal', [
            'booking_id'  => $booking->id,
            'property_id' => $booking->property_id,
            'actor_name'  => $booking->guest_name,
            'metadata'    => ['lock_id' => $lock->id, 'seam_device_id' => $lock->seam_device_id, 'action_attempt_id' => $attempt['action_attempt_id'] ?? null],
        ]);
        return response()->json(['ok' => true, 'status' => 'pending', 'action_attempt_id' => $attempt['action_attempt_id'] ?? null]);
    }
    public function lockStatus(string $bookingId, string $token, PropertyLock $lock)
    {
        $booking = $this->booking($bookingId, $token);
        abort_unless($lock->property_id === $booking->property_id, 404);

        return response()->json([
            'ok' => true,
            'locked' => $lock->last_known_locked,
            'updated_at' => optional($lock->last_status_at)->toIso8601String(),
        ]);
    }
    public function lockDoor(string $bookingId, string $token, PropertyLock $lock)
    {
        $booking = $this->booking($bookingId, $token);
        abort_unless($lock->property_id === $booking->property_id, 404);
        try {
            $attempt = app(SeamService::class)->lock($lock->seam_device_id);
        } catch (\Throwable $e) {
            ActivityLogService::guest('door_lock_failed', "Guest {$booking->guest_name} failed to send lock command for {$lock->label}: {$e->getMessage()}", 'guest_portal', [
                'booking_id'  => $booking->id,
                'property_id' => $booking->property_id,
                'actor_name'  => $booking->guest_name,
                'severity'    => 'warning',
                'metadata'    => ['lock_id' => $lock->id, 'seam_device_id' => $lock->seam_device_id],
            ]);
            return response()->json(['ok' => false, 'error' => 'Could not reach the door. Please try again in a moment.'], 502);
        }
        if (! empty($attempt['action_attempt_id'])) {
            \Illuminate\Support\Facades\Cache::put('seam_attempt_lock:'.$attempt['action_attempt_id'], ['lock_id' => $lock->id, 'guest_name' => $booking->guest_name, 'booking_id' => $booking->id], now()->addMinutes(10));
        }
        ActivityLogService::guest('door_lock_attempted', "Guest {$booking->guest_name} sent lock command for {$lock->label}.", 'guest_portal', [
            'booking_id'  => $booking->id,
            'property_id' => $booking->property_id,
            'actor_name'  => $booking->guest_name,
            'metadata'    => ['lock_id' => $lock->id, 'seam_device_id' => $lock->seam_device_id, 'action_attempt_id' => $attempt['action_attempt_id'] ?? null],
        ]);
        return response()->json(['ok' => true, 'status' => 'pending', 'action_attempt_id' => $attempt['action_attempt_id'] ?? null]);
    }


    private array $lockStatusCache = [];

    private function resolveLocks(Booking $booking)
    {
        return $booking->property->locks->map(fn ($lock) => [
            'lock'   => $lock,
            'status' => $this->lockStatusFor($booking, $lock),
        ]);
    }

    private function lockStatusFor(Booking $booking, ?PropertyLock $lock = null): ?bool
    {
        $lock = $lock ?: $booking->property->locks()->first();
        if (! $lock) {
            return null;
        }
        if (array_key_exists($lock->id, $this->lockStatusCache)) {
            return $this->lockStatusCache[$lock->id];
        }
        try {
            $status = app(SeamService::class)->getLockStatus($lock->seam_device_id);
        } catch (\Throwable $e) {
            $status = null;
        }
        return $this->lockStatusCache[$lock->id] = $status;
    }
    private function booking(string $bookingId, string $token): Booking
    {
        $booking = Booking::with('property')
            ->where('booking_id', $bookingId)
            ->where('token', $token)
            ->first();
        if ($booking) {
            \App\Services\GuestSessionService::refreshCookie($booking);
            return $booking;
        }
        $cookieToken = request()->cookie(\App\Services\GuestSessionService::COOKIE_NAME);
        $sessionBooking = \App\Services\GuestSessionService::resolve($cookieToken);
        if ($sessionBooking && $sessionBooking->booking_id === $bookingId) {
            return $sessionBooking;
        }
        $fallbackBooking = Booking::with('property')->where('booking_id', $bookingId)->first();
        if (! $fallbackBooking) {
            ActivityLogService::security('invalid_token_access', "Invalid guest token access attempt for booking: {$bookingId}.", [
                'actor_type' => 'guest',
                'severity'   => 'warning',
                'metadata'   => ['booking_id' => $bookingId],
            ]);
            abort(404);
        }
        ActivityLogService::security('invalid_token_access', "Token mismatch for booking: {$bookingId}, guest routed to login.", [
            'actor_type' => 'guest',
            'severity'   => 'warning',
            'metadata'   => ['booking_id' => $bookingId],
        ]);
        return $fallbackBooking;
    }

    private function state(Booking $booking): string
    {
        if ($booking->access_blocked_at) {
            return 'access_blocked';
        }

        if (! $booking->isCheckedIn()) {

        if (! $booking->isIdentityComplete()) {
            return 'identity';
        }

        if (! $booking->photo_id_received) {
            return 'identity';
        }

        if ($booking->needsIdApproval()) {
            return 'identity';
        }

        if (! $booking->isBackgroundCheckComplete()) {
            return 'identity';
        }
        }

        if (in_array($booking->status, ['pre_checkin_complete', 'awaiting_deposit'], true) && ! $booking->deposit_verified_at && ! $booking->isDepositCaptured()) {
            return 'awaiting_deposit';
        }

        if ($booking->status === 'checked_out') {
            return $booking->isPastCheckoutDay() ? 'post_checkout' : 'checkout_locked';
        }

        if ($booking->isPastCheckoutDay()) {
            return 'post_checkout';
        }

        if (! $booking->isCheckinDay()) {
            return 'waiting';
        }

        if (! $booking->gps_verified) {
            return 'arrival';
        }

        if (! $booking->isCheckedIn()) {
            return 'guide';
        }

        if ($booking->isCheckoutDay()) {
            return $booking->isPastCheckoutTime() ? 'checkout_locked' : 'checkout_available';
        }

        if ($booking->isCheckoutDayBeforeSixPM()) {
            return 'checkout_notice';
        }

        return 'guide';
    }

    private function checkinSteps(Booking $booking): array
    {
        $primaryLock = $booking->property->locks()->first();

        return InstructionStep::where('property_id', $booking->property_id)
            ->where('type', 'checkin')
            ->where('active', true)
            ->where($booking->parking_needed ? fn($q) => $q->where('visibility', '!=', 'non_parkers_only') : fn($q) => $q->where('visibility', '!=', 'parkers_only'))
            ->orderBy('sort_order')
            ->with('images')
            ->get()
            ->map(function ($s) use ($booking, $primaryLock) {
                $isLock = ($s->action ?? 'content') === 'door_lock';
                return ['title' => $s->title, 'content' => $s->renderContent($booking), 'image' => $s->imageUrl(), 'images' => $s->images->map(fn($img) => $img->imageUrl())->values()->toArray(), 'action' => $s->action ?? 'content', 'lock_status' => $isLock ? $this->lockStatusFor($booking, $primaryLock) : null, 'lock_id' => $isLock && $primaryLock ? $primaryLock->id : null];
            })
            ->values()
            ->toArray();
    }

    private function parkingSteps(Booking $booking): array
    {
        if (!$booking->parking_needed) return [];

        $primaryLock = $booking->property->locks()->first();

        return InstructionStep::where('property_id', $booking->property_id)
            ->where('type', 'parking')
            ->where('active', true)
            ->orderBy('sort_order')
            ->with('images')
            ->get()
            ->map(function ($s) use ($booking, $primaryLock) {
                $isLock = ($s->action ?? 'content') === 'door_lock';
                return ['title' => $s->title, 'content' => $s->renderContent($booking), 'image' => $s->imageUrl(), 'images' => $s->images->map(fn($img) => $img->imageUrl())->values()->toArray(), 'action' => $s->action ?? 'content', 'lock_status' => $isLock ? $this->lockStatusFor($booking, $primaryLock) : null, 'lock_id' => $isLock && $primaryLock ? $primaryLock->id : null];
            })
            ->values()
            ->toArray();
    }

    private function checkoutSteps(Booking $booking): array
    {
        $primaryLock = $booking->property->locks()->first();

        return InstructionStep::where('property_id', $booking->property_id)
            ->where('type', 'checkout')
            ->where('active', true)
            ->where($booking->parking_needed ? fn($q) => $q->where('visibility', '!=', 'non_parkers_only') : fn($q) => $q->where('visibility', '!=', 'parkers_only'))
            ->orderBy('sort_order')
            ->with('images')
            ->get()
            ->map(function ($s) use ($booking, $primaryLock) {
                $isLock = ($s->action ?? 'content') === 'door_lock';
                return ['title' => $s->title, 'content' => $s->renderContent($booking), 'image' => $s->imageUrl(), 'images' => $s->images->map(fn($img) => $img->imageUrl())->values()->toArray(), 'action' => $s->action ?? 'content', 'lock_status' => $isLock ? $this->lockStatusFor($booking, $primaryLock) : null, 'lock_id' => $isLock && $primaryLock ? $primaryLock->id : null];
            })
            ->values()
            ->toArray();
    }

    private function checkinTimeOptions(): array
    {
        $recommendedHour = 16; // 4:00 PM — the system's actual check-in time
        $hours = array_merge(range(8, 23), [0]);

        // Recommended time first, then the rest in chronological order.
        usort($hours, fn ($a, $b) => ($a === $recommendedHour ? -1 : ($b === $recommendedHour ? 1 : $a <=> $b)));

        $options = [];
        foreach ($hours as $hour) {
            $value = sprintf('%02d:00', $hour);
            $label = \Carbon\Carbon::createFromTime($hour, 0)->format('g:i A');
            if ($hour === $recommendedHour) {
                $label .= ' (Recommended)';
            }
            $options[$value] = $label;
        }
        return $options;
    }

    private function checkoutTimeOptions(): array
    {
        $recommendedHour = 10; // 10:00 AM — the system's actual check-out time
        $hours = range(7, 14); // 7:00 AM through 2:00 PM only

        // Recommended time first, then the rest in chronological order.
        usort($hours, fn ($a, $b) => ($a === $recommendedHour ? -1 : ($b === $recommendedHour ? 1 : $a <=> $b)));

        $options = [];
        foreach ($hours as $hour) {
            $value = sprintf('%02d:00', $hour);
            $label = \Carbon\Carbon::createFromTime($hour, 0)->format('g:i A');
            if ($hour === $recommendedHour) {
                $label .= ' (Recommended)';
            }
            $options[$value] = $label;
        }
        return $options;
    }
    private function availableCategories(Booking $booking)
    {
        return $booking->property->categories
            ->filter(fn ($c) => $c->active && $c->pivot->active)
            ->values();
    }

    private function distanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371000;
        $dLat  = deg2rad($lat2 - $lat1);
        $dLon  = deg2rad($lon2 - $lon1);
        $a     = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
