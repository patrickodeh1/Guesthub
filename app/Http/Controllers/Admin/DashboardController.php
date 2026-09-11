<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Property;
use App\Models\PropertyLock;
use App\Services\SeamService;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
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
        // "Today" is the host's local day, not the server's UTC day — using
        // now()/today() here is what made check-in times and "arriving today"
        // read ~12h off.
        $displayTz = config('app.display_timezone');
        $today = now()->setTimezone($displayTz)->toDateString();

        $overview = [
            'checkins'          => Booking::notArchived()->whereDate('check_in_date', $today)->count(),
            'checkouts'         => Booking::notArchived()->whereDate('check_out_date', $today)->count(),
            'hosting'           => Booking::notArchived()->where('status', 'currently_hosting')->count(),
            'pending_approvals' => Booking::notArchived()
                ->whereNotNull('photo_id_path')->whereNull('approved_at')
                ->whereNotIn('status', ['checked_out'])->count(),
        ];

        // Guests grouped by property: today's arrivals/departures, anyone
        // currently hosting, then upcoming arrivals. Ordered within each
        // property so the most time-sensitive guest sits at the top.
        $properties = Property::query()
            ->with(['bookings' => function ($query) use ($today) {
                $query->where('status', '!=', 'cancelled')
                    ->where(function ($q) use ($today) {
                        $q->whereDate('check_in_date', '>=', $today)
                            ->orWhereDate('check_out_date', $today)
                            ->orWhere('status', 'currently_hosting');
                    });
            }])
            ->orderBy('name')
            ->get()
            ->map(function (Property $property) use ($today) {
                $property->setRelation('bookings', $property->bookings
                    ->sortBy(function (Booking $booking) use ($today) {
                        $checkIn = $booking->check_in_date?->toDateString();
                        $checkOut = $booking->check_out_date?->toDateString();

                        $group = match (true) {
                            $checkIn === $today => 0,
                            $checkOut === $today => 1,
                            $booking->status === 'currently_hosting' => 2,
                            default => 3,
                        };

                        return sprintf('%d|%s|%s', $group, $checkIn, $booking->guest_name);
                    })
                    ->values());

                return $property;
            })
            ->filter(fn (Property $property) => $property->bookings->isNotEmpty())
            ->values();

        return view('admin.dashboard', [
            'overview'       => $overview,
            'properties'     => $properties,
            'today'          => $today,
            'recentActivity' => ActivityLog::with('user')->latest()->take(8)->get(),
            'propertyLocks'  => $this->lockStatuses(),
        ]);
    }
}
