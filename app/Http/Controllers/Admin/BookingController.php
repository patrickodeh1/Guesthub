<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Setting;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    use \App\Traits\BuildsGuestPortalData;
    // Re-verified 2026-08-02: parking_needed save logic confirmed correct on create + update

    public function index(Request $request)
    {
        Booking::archiveOverdue();

        $showArchived = $request->boolean('archived');
        $hasSearch = filled($request->search);

        $baseQuery = fn () => Booking::with('property')
            ->when($request->search, fn ($query, $search) => $query->where(fn ($inner) => $inner
                ->where('guest_name', 'like', "%{$search}%")
                ->orWhere('booking_id', $search)
                ->orWhere('reservation_id', $search)
                ->orWhere('email', $search)
            ))
            ->when($request->status, fn ($query, $status) => $status === 'pending_check_in'
                ? $query->where('status', 'guest_approved')->whereDate('check_in_date', '<=', today())
                : $query->where('status', $status))
            ->when($request->property_id, fn ($query, $pid) => $query->where('property_id', $pid))
            ->when(! $hasSearch, fn ($query) => $showArchived ? $query->archived() : $query->notArchived());

        $currentlyHosting = ($baseQuery)()
            ->where(fn ($q) => $q->where('manually_checked_in', true)->orWhere('status', 'currently_hosting'))
            ->whereNull('checked_out_at')
            ->orderBy('check_in_date')
            ->get();

        $currentlyHostingIds = $currentlyHosting->pluck('id');

        $needsAttention = Booking::with('property')
            ->notArchived()
            ->whereNotNull('photo_id_path')
            ->whereNull('approved_at')
            ->orderBy('check_in_date')
            ->limit(10)
            ->get();

        $bookings = ($baseQuery)()
            ->when($currentlyHostingIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $currentlyHostingIds))
            ->orderBy('check_in_date')
            ->paginate(15)
            ->withQueryString();

        $properties = Property::orderBy('name')->get();

        $stats = [
            'total_guests'     => Booking::notArchived()->count(),
            'todays_arrivals'  => Booking::notArchived()->whereDate('check_in_date', today())->count(),
            'waiting_approval' => Booking::notArchived()->where('status', 'pre_checkin_complete')->whereNull('approved_at')->count(),
            'checked_in'       => Booking::notArchived()
                ->where(fn ($q) => $q->where('manually_checked_in', true)->orWhere('status', 'currently_hosting'))
                ->whereNull('checked_out_at')
                ->count(),
        ];

        return view('admin.bookings.index', compact('bookings', 'currentlyHosting', 'needsAttention', 'properties', 'showArchived', 'stats'));
    }

    public function create()
    {
        return view('admin.bookings.form', [
            'booking'          => new Booking(),
            'properties'       => Property::where('active', true)->orderBy('name')->get(),
            'instructionSteps' => collect(),
        ]);
    }

    public function store(Request $request)
    {
        $data               = $this->validated($request);
        $data['booking_id'] = $data['booking_id'] ?: 'BK-'.strtoupper(Str::random(8));
        $data['token']      = Str::random(40);
        $data['early_checkin'] = $request->boolean('early_checkin');
        $data['photo_id_received'] = $request->boolean('photo_id_received');
        if (($data['status'] ?? null) === 'pre_checkin_complete') {
            $data['photo_id_received'] = true;
        }
        if ($data['photo_id_received'] && empty($data['approved_at'])) {
            $data['approved_at'] = now();
        }
        $booking            = Booking::create($data);
        $booking->recalculateParkingCharge();

        ActivityLogService::admin('booking_created', "Guest booking created for {$booking->guest_name} ({$booking->booking_id}).", 'guests', [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'success',
            'metadata'     => [
                'guest_name'    => $booking->guest_name,
                'check_in_date' => $booking->check_in_date,
                'property_id'   => $booking->property_id,
            ],
        ]);

        return redirect()->route('admin.guests.show', $booking)->with('success', 'Guest booking created successfully.');
    }

    public function show(Booking $booking)
    {
        $booking->load('property');

        $guestLogs = \App\Models\ActivityLog::where('booking_id', $booking->id)
            ->orWhere(fn ($q) => $q
                ->where('subject_type', Booking::class)
                ->where('subject_id', $booking->id)
            )
            ->latest()
            ->take(25)
            ->get();

        return view('admin.bookings.show', compact('booking', 'guestLogs'));
    }

    public function preview(Booking $booking, string $state)
    {
        abort_unless(in_array($state, ['identity', 'waiting', 'arrival', 'guide', 'checkout'], true), 404);
        $booking->load(['property.categories', 'property.amenities']);

        ActivityLogService::admin('booking_previewed', auth()->user()->name." previewed guest page (state: {$state}) for {$booking->guest_name}.", 'guests', [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'metadata'     => ['preview_state' => $state],
        ]);

        $checkinSteps = \App\Models\InstructionStep::where('property_id', $booking->property_id)
            ->where('type', 'checkin')->where('active', true)
            ->where($booking->parking_needed ? fn($q) => $q->where('visibility', '!=', 'non_parkers_only') : fn($q) => $q->where('visibility', '!=', 'parkers_only'))
            ->orderBy('sort_order')->get()
            ->map(fn($s) => ['title' => $s->title, 'content' => $s->renderContent($booking), 'image' => $s->imageUrl()])
            ->values()->toArray();
        $parkingSteps = $booking->parking_needed ? \App\Models\InstructionStep::where('property_id', $booking->property_id)
            ->where('type', 'parking')->where('active', true)->orderBy('sort_order')->get()
            ->map(fn($s) => ['title' => $s->title, 'content' => $s->renderContent($booking), 'image' => $s->imageUrl()])
            ->values()->toArray() : [];
        $checkoutSteps = \App\Models\InstructionStep::where('property_id', $booking->property_id)
            ->where('type', 'checkout')->where('active', true)
            ->where($booking->parking_needed ? fn($q) => $q->where('visibility', '!=', 'non_parkers_only') : fn($q) => $q->where('visibility', '!=', 'parkers_only'))
            ->orderBy('sort_order')->get()
            ->map(fn($s) => ['title' => $s->title, 'content' => $s->renderContent($booking), 'image' => $s->imageUrl()])
            ->values()->toArray();

        return view('guest.show', [
            'booking'        => $booking,
            'property'       => $booking->property,
            'state'          => $state,
            'categories'     => $booking->property->categories->filter(fn ($c) => $c->active && $c->pivot->active)->values(),
            'locks'          => $this->resolveLocks($booking),
            'gpsRadius'      => (int) Setting::getValue('gps_radius_meters', 150),
            'previewMode'    => true,
            'welcomeMessage' => $booking->welcome_message ?: Setting::getValue('default_intro', 'We are glad to have you. Please complete the following details prior to check-in.'),
            'gpsVerifyMessage' => Setting::getValue('gps_verify_message', "It's Go Time!"),
            'checkinSteps'   => $checkinSteps,
            'parkingSteps'   => $parkingSteps,
            'checkoutSteps'  => $checkoutSteps,
            'checkinTimeOptions' => $this->checkinTimeOptions(),
            'checkoutTimeOptions' => $this->checkoutTimeOptions(),
        ]);
    }

    public function edit(Booking $booking)
    {
        $instructionSteps = \App\Models\InstructionStep::where('property_id', $booking->property_id)
            ->where('active', true)
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get(['id', 'type', 'title']);

        return view('admin.bookings.form', [
            'booking'          => $booking,
            'properties'       => Property::where('active', true)->orderBy('name')->get(),
            'instructionSteps' => $instructionSteps,
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        $oldStatus = $booking->status;
        $data = $this->validated($request, $booking);
        $data['early_checkin'] = $request->boolean('early_checkin');
        $data['photo_id_received'] = $request->boolean('photo_id_received');
        if (($data['status'] ?? null) === 'pre_checkin_complete') {
            $data['photo_id_received'] = true;
        }
        if ($data['photo_id_received'] && empty($booking->approved_at) && empty($data['approved_at'])) {
            $data['approved_at'] = now();
        }
        $booking->update($data);
        $booking->recalculateParkingCharge();

        ActivityLogService::admin('booking_updated', auth()->user()->name." updated booking for {$booking->guest_name}.", 'guests', [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'metadata'     => ['old_status' => $oldStatus, 'new_status' => $booking->status],
        ]);

        return redirect()->route('admin.guests.show', $booking)->with('success', 'Booking updated.');
    }

    public function updateWelcomeMessage(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'welcome_message' => ['nullable', 'string'],
        ]);

        $booking->update($data);

        ActivityLogService::admin('booking_welcome_message_updated', auth()->user()->name." updated the welcome message for {$booking->guest_name}.", 'guests', [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
        ]);

        return back()->with('success', 'Welcome message saved.');
    }

    public function destroy(Booking $booking)
    {
        $name = $booking->guest_name;

        ActivityLogService::admin('booking_deleted', auth()->user()->name." deleted booking for {$name} ({$booking->booking_id}).", 'guests', [
            'severity' => 'warning',
            'metadata' => ['booking_id' => $booking->booking_id, 'guest_name' => $name],
        ]);

        $booking->delete();

        return redirect()->route('admin.guests.index')->with('success', "Booking for {$name} deleted.");
    }

    public function archive(Booking $booking)
    {
        $booking->update(['archived_at' => now()]);
        ActivityLogService::admin('booking_archived', auth()->user()->name." archived booking for {$booking->guest_name} ({$booking->booking_id}).", 'guests', [
            'severity' => 'info',
            'metadata' => ['booking_id' => $booking->booking_id, 'guest_name' => $booking->guest_name],
        ]);
        return back()->with('success', "Booking for {$booking->guest_name} archived.");
    }
    public function unarchive(Booking $booking)
    {
        $booking->update(['archived_at' => null]);
        ActivityLogService::admin('booking_unarchived', auth()->user()->name." unarchived booking for {$booking->guest_name} ({$booking->booking_id}).", 'guests', [
            'severity' => 'info',
            'metadata' => ['booking_id' => $booking->booking_id, 'guest_name' => $booking->guest_name],
        ]);
        return back()->with('success', "Booking for {$booking->guest_name} restored from archive.");
    }
    public function overrideGps(Booking $booking)
    {
        $booking->update(['gps_verified' => true]);
        ActivityLogService::security('gps_override', auth()->user()->name." overrode GPS verification for {$booking->guest_name} ({$booking->booking_id}).", [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'warning',
            'metadata'     => ['guest_name' => $booking->guest_name, 'override_by' => auth()->user()->name],
        ]);
        return back()->with('success', 'GPS verification overridden for guest.');
    }

    public function overrideCheckin(Booking $booking)
    {
        $booking->update([
            'manually_checked_in' => true,
            'checked_in_at'       => now(),
            'status'              => 'currently_hosting',
        ]);

        ActivityLogService::security('manual_checkin_override', auth()->user()->name." manually checked in {$booking->guest_name} ({$booking->booking_id}).", [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'warning',
            'metadata'     => ['guest_name' => $booking->guest_name, 'override_by' => auth()->user()->name],
        ]);

        return back()->with('success', 'Guest manually marked as checked in.');
    }

    public function overrideCheckout(Booking $booking)
    {
        $booking->update([
            'checked_out_at' => now(),
            'status'         => 'checked_out',
        ]);

        ActivityLogService::security('manual_checkout_override', auth()->user()->name." manually checked out {$booking->guest_name} ({$booking->booking_id}).", [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'warning',
            'metadata'     => ['guest_name' => $booking->guest_name, 'override_by' => auth()->user()->name],
        ]);

        return back()->with('success', 'Guest manually marked as checked out.');
    }

    public function markIdReceived(Booking $booking)
    {
        $booking->update([
            'photo_id_received' => true,
            'status' => $booking->status === 'pending' ? 'pre_checkin_complete' : $booking->status,
            'approved_at' => $booking->approved_at ?: now(),
        ]);

        ActivityLogService::admin('photo_id_marked', auth()->user()->name." marked photo ID as received for {$booking->guest_name}.", 'photo_id', [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'success',
        ]);

        return back()->with('success', 'Photo ID marked as received.');
    }

    public function approveBooking(Booking $booking)
    {
        $booking->update([
            'approved_at' => now(),
            'decline_reason' => null,
        ]);

        ActivityLogService::admin('booking_approved', auth()->user()->name." approved {$booking->guest_name} for check-in.", 'guests', [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'success',
        ]);

        return back()->with('success', 'Guest approved for check-in.');
    }

    public function markBackgroundCheckComplete(Booking $booking)
    {
        if (! $booking->isApproved()) {
            return back()->with('error', 'Photo ID must be approved before marking the background check complete.');
        }

        $booking->update([
            'background_check_completed_at' => now(),
            'status' => 'awaiting_deposit',
        ]);
        \App\Services\GuestAlertService::send('background_check_complete', $booking);

        ActivityLogService::admin('background_check_completed', auth()->user()->name." marked background check complete for {$booking->guest_name}.", 'guests', [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'success',
        ]);

        return back()->with('success', 'Background check marked complete, guest is now awaiting deposit.');
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,pre_checkin_complete,awaiting_deposit,guest_approved,currently_hosting,checked_out'],
        ]);

        $booking->update(['status' => $data['status']]);

        ActivityLogService::admin('status_manually_changed', auth()->user()->name." manually set status to \"".str($data['status'])->replace('_', ' ')->title()."\" for {$booking->guest_name}.", 'guests', [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'warning',
        ]);

        return back()->with('success', 'Status updated.');
    }

    public function markDepositVerified(Booking $booking)
    {
        if (! $booking->isBackgroundCheckComplete()) {
            return back()->with('error', 'Background check must be completed before verifying the deposit.');
        }

        $booking->update([
            'deposit_verified_at' => now(),
            'status' => 'guest_approved',
        ]);
        \App\Services\GuestAlertService::send('fully_approved', $booking);

        ActivityLogService::admin('deposit_verified', auth()->user()->name." verified the deposit for {$booking->guest_name}.", 'guests', [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'success',
        ]);

        return back()->with('success', 'Deposit verified. Guest is now approved.');
    }

    /**
     * Approve a single side of the guest's ID (front or back) independently.
     * Overall booking approval (approved_at) is only set once every side on
     * file is approved.
     */
    /**
     * Approve or deny a guest's requested non-standard check-in/check-out
     * time (task 0). Manual only — no automatic guest notification is sent;
     * approving does not itself set a charge, admin still fills in the
     * task 26 billing fields (early_checkin_tier / late_checkout_type etc.)
     * as needed.
     */
    public function updateTimePreferenceStatus(Request $request, Booking $booking, string $type)
    {
        abort_unless(in_array($type, ['checkin', 'checkout'], true), 404);

        $data = $request->validate([
            'decision' => ['required', 'in:approved,denied'],
        ]);

        $statusField = $type === 'checkin' ? 'checkin_time_status' : 'checkout_time_status';

        $booking->update([
            $statusField => $data['decision'],
        ]);

        $label = $type === 'checkin' ? 'check-in' : 'check-out';

        ActivityLogService::admin(
            'time_preference_'.$data['decision'],
            auth()->user()->name." {$data['decision']} {$booking->guest_name}'s requested {$label} time.",
            'guests',
            [
                'subject_type' => Booking::class,
                'subject_id'   => $booking->id,
                'booking_id'   => $booking->id,
                'property_id'  => $booking->property_id,
                'severity'     => $data['decision'] === 'approved' ? 'success' : 'warning',
            ]
        );

        return back()->with('success', ucfirst($label)." time request {$data['decision']}.");
    }

    public function approveIdSide(Request $request, Booking $booking, string $side)
    {
        abort_unless(in_array($side, ['front', 'back'], true), 404);

        $field = $side === 'back' ? 'photo_id_back_approved_at' : 'photo_id_front_approved_at';
        $reasonField = $side === 'back' ? 'photo_id_back_declined_reason' : 'photo_id_front_declined_reason';

        $booking->update([
            $field => now(),
            $reasonField => null,
        ]);

        if ($booking->fresh()->isIdFullyApproved()) {
            $booking->update([
                'approved_at' => now(),
                'decline_reason' => null,
                'photo_id_received' => true,
            ]);
        }

        ActivityLogService::admin('id_side_approved', auth()->user()->name." approved the {$side} of {$booking->guest_name}'s ID.", 'guests', [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'success',
        ]);

        return back()->with('success', ucfirst($side).' of ID approved.');
    }

    /**
     * Decline a single side of the guest's ID (front or back) independently.
     * The declined side's uploaded photo is cleared so the guest is forced to
     * re-upload only that side; the other side (if already approved) is left
     * untouched. Triggers an email + SMS to the guest with the reason.
     */
    public function declineIdSide(Request $request, Booking $booking, string $side)
    {
        abort_unless(in_array($side, ['front', 'back'], true), 404);

        $data = $request->validate([
            'decline_reason' => ['required', 'string', 'max:1000'],
        ]);

        $pathField = $side === 'back' ? 'photo_id_back_path' : 'photo_id_path';
        $approvedField = $side === 'back' ? 'photo_id_back_approved_at' : 'photo_id_front_approved_at';
        $reasonField = $side === 'back' ? 'photo_id_back_declined_reason' : 'photo_id_front_declined_reason';

        $booking->update([
            $pathField => null,
            $approvedField => null,
            $reasonField => $data['decline_reason'],
            'approved_at' => null,
            'decline_reason' => $data['decline_reason'],
            'photo_id_received' => false,
            'status' => 'pending',
        ]);

        ActivityLogService::admin('id_side_declined', auth()->user()->name." declined the {$side} of {$booking->guest_name}'s ID: {$data['decline_reason']}", 'guests', [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'warning',
        ]);

        $sideLabel = $side === 'back' ? 'back' : 'front';

        \App\Services\GuestAlertService::send('photo_id_declined', $booking, [
            'id_side' => $sideLabel,
            'decline_reason' => $data['decline_reason'],
        ]);

        return back()->with('success', ucfirst($side).' of ID declined. Guest has been notified and asked to re-upload.');
    }

    public function blockAccess(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'access_blocked_reason' => ['required', 'string', 'max:1000'],
        ]);

        $booking->update([
            'access_blocked_at' => now(),
            'access_blocked_reason' => $data['access_blocked_reason'],
        ]);

        ActivityLogService::admin('booking_access_blocked', auth()->user()->name." blocked access for {$booking->guest_name}: {$data['access_blocked_reason']}", 'guests', [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'warning',
        ]);

        return back()->with('success', 'Guest access blocked.');
    }

    public function unblockAccess(Booking $booking)
    {
        $booking->update([
            'access_blocked_at' => null,
            'access_blocked_reason' => null,
        ]);

        ActivityLogService::admin('booking_access_unblocked', auth()->user()->name." restored access for {$booking->guest_name}", 'guests', [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'info',
        ]);

        return back()->with('success', 'Guest access restored.');
    }

    public function photoId(Booking $booking)
    {
        abort_unless($booking->photo_id_path && Storage::disk('local')->exists($booking->photo_id_path), 404);

        ActivityLogService::security('photo_id_viewed', auth()->user()->name." viewed photo ID for {$booking->guest_name} ({$booking->booking_id}).", [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'security',
            'metadata'     => ['accessed_by' => auth()->user()->name],
        ]);

        $ext = pathinfo($booking->photo_id_path, PATHINFO_EXTENSION) ?: 'jpg';

        return response()->download(
            \Storage::path($booking->photo_id_path),
            $booking->booking_id.'-photo-id.'.$ext
        );
    }

    public function photoIdView(Booking $booking)
    {
        abort_unless($booking->photo_id_path && Storage::disk('local')->exists($booking->photo_id_path), 404);

        return response()->file(\Storage::path($booking->photo_id_path));
    }

    public function photoIdBack(Booking $booking)
    {
        abort_unless($booking->photo_id_back_path && Storage::disk('local')->exists($booking->photo_id_back_path), 404);
        ActivityLogService::security('photo_id_back_viewed', auth()->user()->name." viewed back of photo ID for {$booking->guest_name} ({$booking->booking_id}).", [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'security',
            'metadata'     => ['accessed_by' => auth()->user()->name],
        ]);

        $ext = pathinfo($booking->photo_id_back_path, PATHINFO_EXTENSION) ?: 'jpg';

        return response()->download(
            \Storage::path($booking->photo_id_back_path),
            $booking->booking_id.'-photo-id-back.'.$ext
        );
    }

    public function photoIdBackView(Booking $booking)
    {
        abort_unless($booking->photo_id_back_path && Storage::disk('local')->exists($booking->photo_id_back_path), 404);

        return response()->file(\Storage::path($booking->photo_id_back_path));
    }

    public function licensePlate(Booking $booking)
    {
        abort_unless($booking->license_plate_photo_path && Storage::disk('local')->exists($booking->license_plate_photo_path), 404);

        ActivityLogService::security('license_plate_photo_viewed', auth()->user()->name." viewed license plate photo for {$booking->guest_name} ({$booking->booking_id}).", [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'security',
            'metadata'     => ['accessed_by' => auth()->user()->name],
        ]);

        $ext = pathinfo($booking->license_plate_photo_path, PATHINFO_EXTENSION) ?: 'jpg';

        return response()->download(
            \Storage::path($booking->license_plate_photo_path),
            $booking->booking_id.'-license-plate.'.$ext
        );
    }

    public function licensePlateView(Booking $booking)
    {
        abort_unless($booking->license_plate_photo_path && Storage::disk('local')->exists($booking->license_plate_photo_path), 404);

        return response()->file(\Storage::path($booking->license_plate_photo_path));
    }

    private function validated(Request $request, ?Booking $booking = null): array
    {
        return $request->validate([
            'booking_id'     => ['nullable', 'string', 'max:255', 'unique:bookings,booking_id,'.($booking?->id ?? 'NULL')],
            'reservation_id' => ['required', 'string', 'max:255', 'unique:bookings,reservation_id,'.($booking?->id ?? 'NULL')],
            'guest_name'     => ['required', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:255'],
            'email'          => ['nullable', 'email', 'max:255'],
            'check_in_date'  => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after_or_equal:check_in_date'],
            'property_id'    => ['required', 'exists:properties,id'],
            'id_type'        => ['required', 'in:state_id,passport'],
            'parking_needed' => ['nullable', 'boolean'],
            'parking_charge_override' => ['nullable', 'numeric', 'min:0'],
            'incidentals_charge' => ['nullable', 'numeric', 'min:0'],
            'early_checkin'  => ['nullable', 'boolean'],
            'early_checkin_tier' => ['nullable', 'in:8am,12pm'],
            'late_checkout_type' => ['nullable', 'in:authorized,unauthorized'],
            'late_checkout_hours' => ['nullable', 'numeric', 'min:0'],
            'late_checkout_actual_time' => ['nullable', 'date'],
            'photo_id_received' => ['nullable', 'boolean'],
            'checkin_time_preference'  => ['nullable', 'date_format:H:i'],
            'checkout_time_preference' => ['nullable', 'date_format:H:i'],
            'status'         => ['required', 'in:pending,pre_checkin_complete,awaiting_deposit,guest_approved,currently_hosting,checked_out'],
            'notes'          => ['nullable', 'string'],
        ]);
    }
}
