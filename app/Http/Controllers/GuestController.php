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
            'welcomeMessage' => $booking->welcome_message ?: \App\Models\Setting::getValue('default_intro', 'We are glad to have you. Please complete the following details prior to check-in.'),
            'gpsRadius'     => (int) Setting::getValue('gps_radius_meters', 150),
            'checkinSteps'  => ($state === 'guide' && ! $booking->instructionsCompleted()) ? $this->checkinSteps($booking) : [],
            'checkoutSteps' => $this->checkoutSteps($booking),
            'parkingSteps'  => ($state === 'guide' && ! $booking->instructionsCompleted()) ? $this->parkingSteps($booking) : [],
            'checkinTimeOptions' => $this->checkinTimeOptions(),
        ]);
    }

    public function confirmCheckin(string $bookingId, string $token)
    {
        $booking = $this->booking($bookingId, $token);
        $booking->update([
            'status'        => 'currently_hosting',
            'checked_in_at' => now(),
        ]);
        \App\Services\SmsNotificationService::guestCheckedIn($booking);
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
        \App\Services\SmsNotificationService::guestCheckedOut($booking);
        ActivityLogService::guest('guest_confirmed_checkout', "Guest {$booking->guest_name} confirmed check-out.", 'check', [
            'booking_id'  => $booking->id,
            'property_id' => $booking->property_id,
            'actor_name'  => $booking->guest_name,
            'actor_email' => $booking->email,
            'severity'    => 'success',
        ]);
        return response()->json(['ok' => true]);
    }

    public function submitIdentity(Request $request, string $bookingId, string $token)
    {
        $booking = $this->booking($bookingId, $token);
        $photoRequired = ! $booking->photo_id_received;

        $data = $request->validate([
            'guest_name' => ['nullable', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:50'],
            'photo_id' => [$photoRequired ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'photo_id_back' => [($photoRequired && $booking->id_type !== 'passport') ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'parking_needed' => ['nullable', 'boolean'],
            'checkin_time_preference' => ['required', 'regex:/^([01][0-9]|2[0-3]):(00|30)$/'],
        ]);

        $updates = [
            'guest_name'   => ($data['guest_name'] ?? null) ?: $booking->guest_name,
            'email'        => $data['email'],
            'phone'        => ($data['phone'] ?? null) ?: $booking->phone,
            'parking_needed' => is_null($booking->parking_needed) ? $request->boolean('parking_needed') : $booking->parking_needed,
            'checkin_time_preference' => $data['checkin_time_preference'],
            'status'       => 'pre_checkin_complete',
            'identity_confirmed_at' => now(),
            'decline_reason' => null,
            'photo_id_received' => true,
        ];

        $archiveFolder = 'photo-ids-archive/'.$booking->booking_id.'-'.\Illuminate\Support\Str::slug($booking->guest_name);

        if ($request->hasFile('photo_id')) {
            if ($booking->photo_id_path && \Storage::disk('local')->exists($booking->photo_id_path)) {
                \Storage::disk('local')->move(
                    $booking->photo_id_path,
                    $archiveFolder.'/'.now()->format('Ymd-His').'-front-'.basename($booking->photo_id_path)
                );
            }
            $updates['photo_id_path'] = $request->file('photo_id')->store('photo-ids');
        }
        if ($request->hasFile('photo_id_back')) {
            if ($booking->photo_id_back_path && \Storage::disk('local')->exists($booking->photo_id_back_path)) {
                \Storage::disk('local')->move(
                    $booking->photo_id_back_path,
                    $archiveFolder.'/'.now()->format('Ymd-His').'-back-'.basename($booking->photo_id_back_path)
                );
            }
            $updates['photo_id_back_path'] = $request->file('photo_id_back')->store('photo-ids');
        }

        $isFirstCompletion = $booking->status === 'pending';

        $booking->update($updates);

        if ($isFirstCompletion) {
            \App\Services\SmsNotificationService::preCheckinComplete($booking);
        }

        ActivityLogService::guest('photo_id_uploaded', "Guest {$booking->guest_name} submitted photo ID and pre-arrival details.", 'photo_id', [
            'booking_id'  => $booking->id,
            'property_id' => $booking->property_id,
            'actor_name'  => $booking->guest_name,
            'actor_email' => $data['email'],
            'severity'    => 'success',
            'metadata'    => ['email' => $data['email'], 'booking_ref' => $booking->booking_id],
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
            ? $booking->property->locks->map(fn ($lock) => ['lock' => $lock, 'status' => $this->lockStatusFor($booking, $lock)])
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
            \Illuminate\Support\Facades\Cache::put('seam_attempt_lock:'.$attempt['action_attempt_id'], $lock->id, now()->addMinutes(10));
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
            \Illuminate\Support\Facades\Cache::put('seam_attempt_lock:'.$attempt['action_attempt_id'], $lock->id, now()->addMinutes(10));
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

        if (! $booking) {
            ActivityLogService::security('invalid_token_access', "Invalid guest token access attempt for booking: {$bookingId}.", [
                'actor_type' => 'guest',
                'severity'   => 'warning',
                'metadata'   => ['booking_id' => $bookingId],
            ]);
            abort(404);
        }

        return $booking;
    }

    private function state(Booking $booking): string
    {
        if (! $booking->isIdentityComplete()) {
            return 'identity';
        }

        if (! $booking->photo_id_received) {
            return 'identity';
        }

        if ($booking->needsIdApproval()) {
            return 'identity';
        }

        if (in_array($booking->status, ['pre_checkin_complete', 'awaiting_deposit'], true)) {
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
        $options = [];
        for ($hour = 0; $hour < 24; $hour++) {
            foreach ([0, 30] as $minute) {
                $value = sprintf('%02d:%02d', $hour, $minute);
                $label = \Carbon\Carbon::createFromTime($hour, $minute)->format('g:i A');
                $options[$value] = $label;
            }
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
