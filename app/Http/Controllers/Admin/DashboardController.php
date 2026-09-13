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
            // Live lock state. last_known_locked is only refreshed by Seam
            // webhooks, so if webhooks are down or a delivery is missed the
            // dashboard kept showing a stale state that didn't match the
            // actual door. Query Seam directly and persist what we learn.
            try {
                $live = $seam->getLockStatus($lock->seam_device_id);
                if ($live !== null && $lock->last_known_locked !== $live) {
                    $lock->update(['last_known_locked' => $live, 'last_status_at' => now()]);
                }
            } catch (\Throwable $e) {
                report($e);
            }

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
                        return $this->dashboardSortKey($booking, $today);
                    })
                    ->values());

                return $property;
            })
            ->filter(fn (Property $property) => $property->bookings->isNotEmpty())
            ->values();

        // Flat priority lists for the "Today" and "Upcoming" cards: pending
        // check-ins first, then approved/checked-in guests, then check-outs.
        $todayGuests = Booking::with('property')
            ->notArchived()
            ->where(fn ($q) => $q->whereDate('check_in_date', $today)->orWhereDate('check_out_date', $today))
            ->get()
            ->sortBy(fn (Booking $booking) => $this->dashboardSortKey($booking, $today))
            ->values();

        $upcomingGuests = Booking::with('property')
            ->notArchived()
            ->whereNull('checked_out_at')
            ->whereDate('check_in_date', '>', $today)
            ->get()
            ->sortBy(fn (Booking $booking) => $this->dashboardSortKey($booking, $today))
            ->values();

        return view('admin.dashboard', [
            'overview'       => $overview,
            'properties'     => $properties,
            'todayGuests'    => $todayGuests,
            'upcomingGuests' => $upcomingGuests,
            'today'          => $today,
            'recentActivity' => ActivityLog::with('user')->latest()->take(8)->get(),
            'propertyLocks'  => $this->lockStatuses(),
        ]);
    }

    /**
     * Urgency ordering for today's and upcoming guests: pending check-ins
     * first, then approved/checked-in guests, then check-outs, with earlier
     * check-in dates ahead of later ones.
     */
    private function dashboardSortKey(Booking $booking, string $today): string
    {
        $checkIn = $booking->check_in_date?->toDateString();
        $checkOut = $booking->check_out_date?->toDateString();

        $group = match (true) {
            $checkIn === $today && ! $booking->isMarkedCheckedIn() => 0,
            $checkIn === $today => 1,
            $checkOut === $today => 2,
            default => 3,
        };

        return sprintf('%d|%s|%s', $group, $checkIn, $booking->guest_name);
    }
}
