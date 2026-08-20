<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'booking_id', 'reservation_id', 'guest_name', 'phone', 'email', 'check_in_date', 'check_out_date',
        'property_id', 'id_type', 'token', 'photo_id_path', 'photo_id_back_path', 'photo_id_received', 'parking_needed', 'early_checkin', 'checkin_time_preference', 'checkout_time_preference', 'gps_verified', 'guest_authenticated_at',
        'manually_checked_in', 'checked_in_at', 'checked_out_at', 'gps_overridden', 'status', 'notes', 'welcome_message', 'identity_confirmed_at',
        'approved_at', 'decline_reason', 'archived_at', 'background_check_completed_at', 'deposit_verified_at',
        'access_blocked_at', 'access_blocked_reason',
        'photo_id_front_approved_at', 'photo_id_front_declined_reason',
        'photo_id_back_approved_at', 'photo_id_back_declined_reason',
        'parking_charge', 'parking_charge_override', 'incidentals_charge',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'parking_needed' => 'boolean',
            'photo_id_received' => 'boolean',
            'gps_verified' => 'boolean',
            'manually_checked_in' => 'boolean',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'guest_authenticated_at' => 'datetime',
            'approved_at' => 'datetime',
            'identity_confirmed_at' => 'datetime',
            'archived_at' => 'datetime',
            'background_check_completed_at' => 'datetime',
            'deposit_verified_at' => 'datetime',
            'access_blocked_at' => 'datetime',
            'photo_id_front_approved_at' => 'datetime',
            'photo_id_back_approved_at' => 'datetime',
            'parking_charge' => 'decimal:2',
            'parking_charge_override' => 'decimal:2',
            'incidentals_charge' => 'decimal:2',
                    ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
    public function scopeNotArchived($query)
    {
        return $query->whereNull('archived_at');
    }
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }
    public function isArchived(): bool
    {
        return filled($this->archived_at);
    }

    public function isIdentityComplete(): bool
    {
        return filled($this->identity_confirmed_at);
    }

    public function isCheckedIn(): bool
    {
        return !is_null($this->checked_in_at);
    }

    public function isApproved(): bool
    {
        return filled($this->approved_at);
    }

    public function isBackgroundCheckComplete(): bool
    {
        return filled($this->background_check_completed_at);
    }

    public function isDepositVerified(): bool
    {
        return filled($this->deposit_verified_at);
    }

    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    public function needsIdApproval(): bool
    {
        return filled($this->photo_id_path) && ! $this->isApproved();
    }

    public function isFrontIdApproved(): bool
    {
        return filled($this->photo_id_front_approved_at);
    }

    public function isBackIdApproved(): bool
    {
        return filled($this->photo_id_back_approved_at);
    }

    /**
     * True once every side of ID the guest is expected to provide is approved.
     * Back side is only required if the booking has a back-side path/requirement
     * on record (some ID types are front-only).
     */
    public function isIdFullyApproved(): bool
    {
        $frontOk = $this->isFrontIdApproved();
        $backRequired = filled($this->photo_id_back_path) || filled($this->photo_id_back_declined_reason);
        $backOk = ! $backRequired || $this->isBackIdApproved();

        return $frontOk && $backOk;
    }

    public function hasPendingIdRejection(): bool
    {
        return filled($this->photo_id_front_declined_reason) || filled($this->photo_id_back_declined_reason);
    }

    public function stayRangeLabel(): string
    {
        $in = $this->check_in_date;
        $out = $this->check_out_date;

        if ($in->format('Y-m') === $out->format('Y-m')) {
            return $in->format('M j').'-'.$out->format('j');
        }

        if ($in->format('Y') === $out->format('Y')) {
            return $in->format('M j').' - '.$out->format('M j');
        }

        return $in->format('M j, Y').' - '.$out->format('M j, Y');
    }

    /**
     * Calculate the parking charge for this stay by summing the property's
     * per-weekday parking rate across each night of the stay (task 20/25).
     * Nights with no configured rate for that weekday are skipped (treated as $0),
     * rather than blocking the whole calculation, since the client fills in
     * rates per property over time.
     * Returns null if parking isn't needed, or if there's no check-in/check-out
     * date pair to calculate nights from.
     */
    public function calculateParkingCharge(): ?float
    {
        if (!$this->parking_needed) {
            return null;
        }

        if (!$this->check_in_date || !$this->check_out_date || !$this->property) {
            return null;
        }

        $total = 0.0;
        $night = $this->check_in_date->copy();

        while ($night->lt($this->check_out_date)) {
            $total += $this->property->parkingRateForDay($night) ?? 0.0;
            $night->addDay();
        }

        return round($total, 2);
    }

    /**
     * Recalculate and persist the auto-calculated parking_charge field.
     * Does not touch parking_charge_override — that's set independently by an admin.
     */
    public function recalculateParkingCharge(): void
    {
        $this->parking_charge = $this->calculateParkingCharge();
        $this->save();
    }

    /**
     * The charge actually used for billing: admin override wins if set,
     * otherwise fall back to the auto-calculated amount.
     */
    public function effectiveParkingCharge(): ?float
    {
        if ($this->parking_charge_override !== null) {
            return (float) $this->parking_charge_override;
        }

        return $this->parking_charge !== null ? (float) $this->parking_charge : null;
    }

    public function instructionsCompleted(): bool
    {
        return $this->manually_checked_in || $this->status === 'currently_hosting';
    }

    public function isCheckinDay(?CarbonInterface $date = null): bool
    {
        $date ??= now();

        return $date->toDateString() >= $this->check_in_date->toDateString();
    }

    public function effectiveCheckinTime(): string
    {
        return $this->checkin_time_preference ?: \App\Models\Setting::getValue('default_checkin_time', '15:00');
    }

    public function effectiveCheckinTimeFormatted(): string
    {
        return $this->safeFormatTime($this->effectiveCheckinTime());
    }

    public function checkinTimePreferenceFormatted(): ?string
    {
        return $this->checkin_time_preference ? $this->safeFormatTime($this->checkin_time_preference) : null;
    }

    public function checkoutTimePreferenceFormatted(): ?string
    {
        return $this->checkout_time_preference ? $this->safeFormatTime($this->checkout_time_preference) : null;
    }

    public function addressAvailableAtFormatted(): string
    {
        return $this->safeFormatTime($this->effectiveCheckinTime(), subHour: true);
    }

    private function safeParseTime(string $value): \Carbon\Carbon
    {
        try {
            return \Carbon\Carbon::createFromFormat('H:i', trim($value));
        } catch (\Exception $e) {
            try {
                return \Carbon\Carbon::parse(trim($value));
            } catch (\Exception $e) {
                return \Carbon\Carbon::createFromFormat('H:i', '15:00');
            }
        }
    }
    private function safeFormatTime(string $value, bool $subHour = false): string
    {
        $time = null;

        try {
            $time = \Carbon\Carbon::createFromFormat('H:i', trim($value));
        } catch (\Exception $e) {
            try {
                $time = \Carbon\Carbon::parse(trim($value));
            } catch (\Exception $e) {
                $time = \Carbon\Carbon::createFromFormat('H:i', '15:00');
            }
        }

        if ($subHour) {
            $time = $time->subHour();
        }

        return $time->format('g:i A');
    }

    public function canViewAddress(?CarbonInterface $now = null): bool
    {
        $timezone = $this->property?->timezone ?? 'America/New_York';
        $now = ($now ?? now())->setTimezone($timezone);

        if ($this->early_checkin) return true;

        $checkinDate = $this->check_in_date->toDateString();

        if ($now->toDateString() < $checkinDate) return false;
        if ($now->toDateString() > $checkinDate) return true;

        $parsedTime = $this->safeParseTime($this->effectiveCheckinTime());
        $threshold = \Carbon\Carbon::parse($checkinDate, $timezone)->setTime($parsedTime->hour, $parsedTime->minute)->subHour();

        return $now->greaterThanOrEqualTo($threshold);
    }

    public function isCheckoutDay(?CarbonInterface $date = null): bool
    {
        $date ??= now();

        return $date->toDateString() >= $this->check_out_date->toDateString();
    }

    public function isPastCheckoutDay(?CarbonInterface $now = null): bool
    {
        $timezone = $this->property?->timezone ?? 'America/New_York';
        $now = ($now ?? now())->setTimezone($timezone);

        return $now->toDateString() > $this->check_out_date->toDateString();
    }

    public function isCheckoutDayBeforeSixPM(?CarbonInterface $now = null): bool
    {
        $timezone = $this->property?->timezone ?? 'America/New_York';
        $now = ($now ?? now())->setTimezone($timezone);

        if ($now->toDateString() !== $this->check_out_date->copy()->subDay()->toDateString()) {
            return false;
        }

        return $now->hour >= 18;
    }

    public function effectiveCheckoutTime(): string
    {
        return $this->checkout_time_preference ?: ($this->property?->checkout_time ?: '11:00');
    }

    public function effectiveCheckoutTimeFormatted(): string
    {
        return $this->safeFormatTime($this->effectiveCheckoutTime());
    }

    public function isPastCheckoutTime(?CarbonInterface $now = null): bool
    {
        $timezone = $this->property?->timezone ?? 'America/New_York';
        $now = ($now ?? now())->setTimezone($timezone);

        if ($now->toDateString() < $this->check_out_date->toDateString()) return false;
        if ($now->toDateString() > $this->check_out_date->toDateString()) return true;

        [$hour, $minute] = array_map('intval', explode(':', $this->effectiveCheckoutTime()));

        return $now->hour > $hour || ($now->hour === $hour && $now->minute >= $minute);
    }

    public function isPastCheckoutGracePeriod(int $graceMinutes = 30, ?CarbonInterface $now = null): bool
    {
        $timezone = $this->property?->timezone ?? 'America/New_York';
        $now = ($now ?? now())->setTimezone($timezone);

        if ($now->toDateString() < $this->check_out_date->toDateString()) return false;
        if ($now->toDateString() > $this->check_out_date->toDateString()) return true;

        [$hour, $minute] = array_map('intval', explode(':', $this->effectiveCheckoutTime()));
        $threshold = $now->copy()->setTime($hour, $minute)->addMinutes($graceMinutes);

        return $now->greaterThanOrEqualTo($threshold);
    }

    public static function archiveOverdue(): int
    {
        $count = 0;
        static::notArchived()->chunkById(100, function ($bookings) use (&$count) {
            foreach ($bookings as $booking) {
                if (! $booking->isPastCheckoutTime()) {
                    continue;
                }
                $updates = ['archived_at' => now()];
                $booking->update($updates);
                $count++;
            }
        });
        return $count;
    }

    /**
     * Auto-checkout bookings whose guests never pressed "All Done", once the
     * configured grace period after their checkout time has elapsed (task 23).
     * Intentionally separate from archiveOverdue(): archiving is cosmetic/admin
     * housekeeping and can happen immediately at checkout time, but flipping a
     * booking's status to checked_out is a real state change the guest should
     * get a grace window for.
     */
    public static function autoCheckoutOverdue(int $graceMinutes = 30): int
    {
        $count = 0;
        static::where('status', '!=', 'checked_out')->chunkById(100, function ($bookings) use (&$count, $graceMinutes) {
            foreach ($bookings as $booking) {
                if (! $booking->isPastCheckoutGracePeriod($graceMinutes)) {
                    continue;
                }
                $booking->update([
                    'status' => 'checked_out',
                    'checked_out_at' => now(),
                ]);
                $count++;
            }
        });
        return $count;
    }

    public function publicUrl(): string
    {
        if ($this->reservation_id) {
            return route('checkin.rid', ['RID' => $this->reservation_id]);
        }
        return route('guest.show', [$this->booking_id, $this->token]);
    }

    public function effectiveStatus(): string
    {
        if ($this->status === 'guest_approved' && $this->isCheckinDay()) {
            return 'pending_check_in';
        }
        return $this->status ?: 'pending';
    }

    public function statusLabel(): string
    {
        return str($this->effectiveStatus())->replace('_', ' ')->title()->toString();
    }
}
