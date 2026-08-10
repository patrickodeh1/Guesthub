<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Property;
use App\Models\PropertyLock;
use App\Models\Setting;
use App\Services\SeamService;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    private function priorityBookings()
    {
        $today = now()->toDateString();

        return Property::with(['bookings' => function ($q) {
            $q->whereNotIn('status', ['checked_out'])->orderBy('check_in_date');
        }])->get()->map(function (Property $property) use ($today) {
            $booking = $property->bookings
                ->filter(fn ($b) => $b->status === 'currently_hosting' || $b->check_in_date->toDateString() >= $today)
                ->sortBy(fn ($b) => $b->status === 'currently_hosting' ? '0' : $b->check_in_date->toDateString())
                ->first();

            $requirements = [];
            if ($booking && ! $booking->isCheckedIn()) {
                if (! $booking->photo_id_received) $requirements[] = 'Needs to upload photo ID';
                if ($booking->needsIdApproval()) $requirements[] = 'ID pending admin approval';
                if (! $booking->isIdentityComplete()) $requirements[] = 'Identity verification incomplete';
                if (is_null($booking->parking_needed)) $requirements[] = 'Parking preference not specified';
                if (! $booking->gps_verified) $requirements[] = 'GPS location not verified';
            }

            return [
                'property' => $property,
                'booking' => $booking,
                'is_today' => $booking && $booking->status !== 'currently_hosting' && $booking->check_in_date->toDateString() === $today,
                'requirements' => $requirements,
            ];
        });
    }

    private function lockStatuses()
    {
        $seam = app(SeamService::class);

        return PropertyLock::with('property')->get()->map(function (PropertyLock $lock) use ($seam) {
            $cacheKey = "lock_battery_fetched:{$lock->id}";

            if (! Cache::has($cacheKey)) {
                try {
                    $level = $seam->getBatteryLevel($lock->seam_device_id);
                    $lock->update(['battery_level' => $level]);
                } catch (\Throwable $e) {
                    report($e);
                }
                Cache::put($cacheKey, true, now()->addMinutes(10));
            }

            return $lock;
        })->groupBy('property.name');
    }

    public function __invoke()
    {
        $today = now()->toDateString();
        $properties = Property::count();
        $guests = Booking::count();
        $brandReady = filled(Setting::getValue('site_logo')) || filled(Setting::getValue('brand_color'));
        $contactReady = filled(Setting::getValue('contact_phone')) && filled(Setting::getValue('contact_email'));
        $categoriesReady = \App\Models\Category::count() > 0;
        $firstBooking = Booking::latest()->first();
        $checklist = collect([
            ['label' => 'Add your first property', 'done' => $properties > 0, 'route' => route('admin.properties.create'), 'icon' => 'properties'],
            ['label' => 'Update brand and logo', 'done' => $brandReady, 'route' => route('admin.settings.edit'), 'icon' => 'sparkles'],
            ['label' => 'Add contact details', 'done' => $contactReady, 'route' => route('admin.settings.edit'), 'icon' => 'contact-guest-services'],
            ['label' => 'Add default categories', 'done' => $categoriesReady, 'route' => route('admin.categories.index'), 'icon' => 'categories'],
            ['label' => 'Add first guest booking', 'done' => $guests > 0, 'route' => route('admin.guests.create'), 'icon' => 'guests'],
            ['label' => 'Copy a guest URL', 'done' => (bool) $firstBooking, 'route' => $firstBooking ? route('admin.guests.show', $firstBooking) : route('admin.guests.index'), 'icon' => 'copy'],
            ['label' => 'Test guest pre-check-in page', 'done' => (bool) $firstBooking, 'route' => $firstBooking?->publicUrl() ?? route('admin.guests.index'), 'icon' => 'security'],
            ['label' => 'Review Admin Guide', 'done' => filled(auth()->user()->admin_tour_completed_at), 'route' => route('admin.guide'), 'icon' => 'guide'],
        ]);
        $checklistPercent = $checklist->count() ? (int) round(($checklist->where('done', true)->count() / $checklist->count()) * 100) : 0;

        return view('admin.dashboard', [
            'totalProperties' => $properties,
            'activeGuests' => Booking::whereIn('status', ['id_uploaded', 'waiting_checkin', 'checked_in'])->count(),
            'pendingIds' => Booking::whereNull('photo_id_path')->count(),
            'todayCheckins' => Booking::whereDate('check_in_date', $today)->count(),
            'todayCheckouts' => Booking::whereDate('check_out_date', $today)->count(),
            'missingParking' => Booking::whereNull('parking_needed')->count(),
            'gpsApprovalNeeded' => Booking::whereDate('check_in_date', '<=', $today)->whereNotIn('status', ['checked_in', 'checked_out'])->where('gps_verified', false)->where('manually_checked_in', false)->count(),
            'todayArrivals' => Booking::with('property')->whereDate('check_in_date', $today)->latest()->take(5)->get(),
            'todayDepartures' => Booking::with('property')->whereDate('check_out_date', $today)->latest()->take(5)->get(),
            'recentGuests' => Booking::with('property')->latest()->take(8)->get(),
            'recentActivity' => ActivityLog::with('user')->latest()->take(8)->get(),
            'checklist' => $checklist,
            'checklistPercent' => $checklistPercent,
            'propertyLocks' => $this->lockStatuses(),
            'priorityBookings' => $this->priorityBookings(),
        ]);
    }
}
