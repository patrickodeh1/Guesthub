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
    // Re-verified 2026-08-02: parking_needed save logic confirmed correct on create + update

    public function index(Request $request)
    {
        $showArchived = $request->boolean('archived');
        $bookings = Booking::with('property')
            ->when($request->search, fn ($query, $search) => $query->where(fn ($inner) => $inner
                ->where('guest_name', 'like', "%{$search}%")
                ->orWhere('booking_id', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
            ))
            ->when($request->status, fn ($query, $status) => $status === 'pending_check_in'
                ? $query->where('status', 'guest_approved')->whereDate('check_in_date', '<=', today())
                : $query->where('status', $status))
            ->when($request->property_id, fn ($query, $pid) => $query->where('property_id', $pid))
            ->when($showArchived, fn ($query) => $query->archived(), fn ($query) => $query->notArchived())
            ->latest()
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

        return view('admin.bookings.index', compact('bookings', 'properties', 'showArchived', 'stats'));
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
            'gpsRadius'      => (int) Setting::getValue('gps_radius_meters', 150),
            'previewMode'    => true,
            'welcomeMessage' => $booking->welcome_message ?: Setting::getValue('default_intro', 'We are glad to have you. Please complete the following details prior to check-in.'),
            'checkinSteps'   => $checkinSteps,
            'parkingSteps'   => $parkingSteps,
            'checkoutSteps'  => $checkoutSteps,
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

        ActivityLogService::admin('background_check_completed', auth()->user()->name." marked background check complete for {$booking->guest_name}.", 'guests', [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'success',
        ]);

        return back()->with('success', 'Background check marked complete — guest is now awaiting deposit.');
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

        ActivityLogService::admin('deposit_verified', auth()->user()->name." verified the deposit for {$booking->guest_name}.", 'guests', [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'success',
        ]);

        return back()->with('success', 'Deposit verified — guest is now approved.');
    }

    public function declineBooking(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'decline_reason' => ['required', 'string', 'max:1000'],
        ]);

        $booking->update([
            'approved_at' => null,
            'decline_reason' => $data['decline_reason'],
            'photo_id_received' => false,
            'status' => 'pending',
        ]);

        ActivityLogService::admin('booking_declined', auth()->user()->name." declined ID for {$booking->guest_name}: {$data['decline_reason']}", 'guests', [
            'subject_type' => Booking::class,
            'subject_id'   => $booking->id,
            'booking_id'   => $booking->id,
            'property_id'  => $booking->property_id,
            'severity'     => 'warning',
        ]);

        return back()->with('success', 'Guest declined — they will be asked to re-upload their ID.');
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

    private function validated(Request $request, ?Booking $booking = null): array
    {
        return $request->validate([
            'booking_id'     => ['nullable', 'string', 'max:255', 'unique:bookings,booking_id,'.($booking?->id ?? 'NULL')],
            'guest_name'     => ['required', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:255'],
            'email'          => ['nullable', 'email', 'max:255'],
            'check_in_date'  => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after_or_equal:check_in_date'],
            'property_id'    => ['required', 'exists:properties,id'],
            'id_type'        => ['required', 'in:state_id,passport'],
            'parking_needed' => ['nullable', 'boolean'],
            'early_checkin'  => ['nullable', 'boolean'],
            'photo_id_received' => ['nullable', 'boolean'],
            'status'         => ['required', 'in:pending,pre_checkin_complete,awaiting_deposit,guest_approved,currently_hosting,checked_out'],
            'notes'          => ['nullable', 'string'],
        ]);
    }
}
