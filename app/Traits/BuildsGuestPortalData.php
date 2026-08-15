<?php

namespace App\Traits;

use App\Models\Booking;
use App\Models\PropertyLock;
use App\Services\SeamService;
use Carbon\Carbon;

trait BuildsGuestPortalData
{
    protected array $lockStatusCache = [];

    protected function resolveLocks(Booking $booking)
    {
        return $booking->property->locks->map(fn ($lock) => [
            'lock'   => $lock,
            'status' => $this->lockStatusFor($booking, $lock),
        ]);
    }

    protected function lockStatusFor(Booking $booking, ?PropertyLock $lock = null): ?bool
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

    protected function checkinTimeOptions(): array
    {
        $options = [];
        for ($hour = 0; $hour < 24; $hour++) {
            foreach ([0, 30] as $minute) {
                $value = sprintf('%02d:%02d', $hour, $minute);
                $label = Carbon::createFromTime($hour, $minute)->format('g:i A');
                $options[$value] = $label;
            }
        }
        return $options;
    }
}
