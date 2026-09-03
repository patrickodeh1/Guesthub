<?php

namespace App\Models;

use App\Support\PhoneFormatter;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'reservation_id', 'source', 'channex_booking_id', 'guest_name', 'phone', 'email', 'check_in_date', 'check_out_date',
        'property_id', 'id_type', 'token', 'photo_id_path', 'photo_id_back_path', 'photo_id_received', 'parking_needed', 'early_checkin_tier', 'checkin_time_preference', 'checkout_time_preference', 'checkin_time_status', 'checkout_time_status', 'gps_verified', 'guest_authenticated_at',
        'manually_checked_in', 'checked_in_at', 'checked_out_at', 'late_checkout_type', 'late_checkout_hours', 'late_checkout_actual_time', 'gps_overridden', 'status', 'cancelled_at', 'notes', 'welcome_message', 'identity_confirmed_at',
        'approved_at', 'decline_reason', 'archived_at', 'background_check_completed_at', 'deposit_verified_at',
        'contract_version', 'contract_accepted_at',
        'sms_consent_at', 'sms_consent_version', 'sms_consent_opted_in',
        'terms_accepted_at', 'terms_accepted_version',
        'deposit_payment_status', 'deposit_stripe_payment_intent_id', 'deposit_amount_cents',
        'incidentals_billed_cents', 'parking_billed_cents', 'early_checkin_billed_cents',
        'access_blocked_at', 'access_blocked_reason',
        'photo_id_front_approved_at', 'photo_id_front_declined_reason',
        'photo_id_back_approved_at', 'photo_id_back_declined_reason',
        'parking_charge', 'parking_charge_override', 'incidentals_charge', 'checkin_reminder_sent_at',
        'vehicle_make_model', 'license_plate_photo_path', 'vehicle_info_bypassed_at',
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
            'late_checkout_hours' => 'decimal:2',
            'late_checkout_actual_time' => 'datetime',
            'guest_authenticated_at' => 'datetime',
            'approved_at' => 'datetime',
            'identity_confirmed_at' => 'datetime',
            'archived_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'background_check_completed_at' => 'datetime',
            'deposit_verified_at' => 'datetime',
            'contract_accepted_at' => 'datetime',
            'sms_consent_at' => 'datetime',
            'sms_consent_opted_in' => 'boolean',
            'terms_accepted_at' => 'datetime',
            'deposit_amount_cents' => 'integer',
            'incidentals_billed_cents' => 'integer',
            'parking_billed_cents' => 'integer',
            'early_checkin_billed_cents' => 'integer',
            'access_blocked_at' => 'datetime',
            'photo_id_front_approved_at' => 'datetime',
            'photo_id_back_approved_at' => 'datetime',
            'parking_charge' => 'decimal:2',
            'parking_charge_override' => 'decimal:2',
            'incidentals_charge' => 'decimal:2',
            'checkin_reminder_sent_at' => 'datetime',
            'vehicle_info_bypassed_at' => 'datetime',
                    ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function charges(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Charge::class);
    }

    /**
     * True once a Stripe deposit charge exists (captured or failed) for
     * this booking. Entirely separate from isDepositVerified() / the legacy
     * manual deposit_verified_at flow, which is untouched by this.
     */
    public function hasDepositCharge(): bool
    {
        return filled($this->deposit_payment_status);
    }

    public function isDepositCaptured(): bool
    {
        return $this->deposit_payment_status === 'success';
    }

    /**
     * The pre-checkin combined charge: parking + incidentals + early
     * check-in (if already granted at this point), capped at the
     * property's flat-dollar ceiling (falling back to the global default),
     * then a global % processing fee added on top. This is what's actually
     * charged at the "after ID upload" step — replaces the old flat
     * admin-set deposit amount. If early check-in is granted *after* this
     * has already been paid, it's billed separately (see the standalone
     * early-check-in guest charge card) rather than reopening this total.
     */
    /**
     * Human-readable breakdown of calculatePreCheckinChargeCents(), for
     * admin visibility on the Payments page (a bare "$187.50" line item
     * would otherwise be meaningless once this is a combined charge).
     */
    public function preCheckinChargeBreakdown(): string
    {
        $parts = [];
        if (($parking = $this->effectiveParkingCharge()) > 0) {
            $parts[] = 'parking $' . number_format($parking, 2);
        }
        if (($this->incidentals_charge ?? 0) > 0) {
            $parts[] = 'incidentals $' . number_format($this->incidentals_charge, 2);
        }
        if (($earlyCheckin = $this->earlyCheckinCharge()) > 0) {
            $parts[] = 'early check-in $' . number_format($earlyCheckin, 2);
        }

        $capCents = $this->property && $this->property->deposit_cap_cents !== null
            ? $this->property->deposit_cap_cents
            : (int) \App\Models\Setting::getValue('default_deposit_cap_cents', 0);
        $feePercent = (float) \App\Models\Setting::getValue('processing_fee_percent', 0);

        $summary = $parts ? implode(' + ', $parts) : 'no parking/incidentals/early check-in';
        if ($capCents > 0) {
            $summary .= ', capped at $' . number_format($capCents / 100, 2);
        }
        if ($feePercent > 0) {
            $summary .= ", plus {$feePercent}% processing fee";
        }

        return $summary;
    }

    public function calculatePreCheckinChargeCents(): int
    {
        $parkingCents = (int) round(($this->effectiveParkingCharge() ?? 0) * 100);
        $incidentalsCents = (int) round(($this->incidentals_charge ?? 0) * 100);
        $earlyCheckinCents = (int) round(($this->earlyCheckinCharge() ?? 0) * 100);

        $capCents = $this->property && $this->property->deposit_cap_cents !== null
            ? $this->property->deposit_cap_cents
            : (int) \App\Models\Setting::getValue('default_deposit_cap_cents', 0);

        $subtotal = $parkingCents + $incidentalsCents + $earlyCheckinCents;
        if ($capCents > 0) {
            $subtotal = min($subtotal, $capCents);
        }

        return $this->applyProcessingFeeCents($subtotal);
    }

    /**
     * Adds the global % processing fee on top of a subtotal, in cents.
     * Shared by the combined pre-checkin charge and every standalone
     * charge type (parking, early check-in, late checkout, incidentals)
     * so the fee is consistently the same % of whatever is actually being
     * charged in that request, grouped or individual.
     */
    public function applyProcessingFeeCents(int $subtotalCents): int
    {
        $feePercent = (float) \App\Models\Setting::getValue('processing_fee_percent', 0);
        $fee = (int) round($subtotalCents * ($feePercent / 100));

        return $subtotalCents + $fee;
    }

    /**
     * incidentals_charge minus whatever's already been billed (via the
     * combined pre-checkin charge or a prior standalone incidentals
     * charge). Never negative — if admin lowers incidentals_charge after
     * some was already billed, this is just 0, not a refund trigger.
     */
    public function unbilledIncidentalsCents(): int
    {
        $currentCents = (int) round(($this->incidentals_charge ?? 0) * 100);

        return max(0, $currentCents - ($this->incidentals_billed_cents ?? 0));
    }

    /**
     * effectiveParkingCharge() minus whatever's already been billed (via
     * the combined pre-checkin charge or a prior standalone parking
     * charge). Same idea as unbilledIncidentalsCents() — prevents the
     * standalone "pay now" card from re-charging parking that was already
     * paid as part of the deposit.
     */
    public function unbilledParkingCents(): int
    {
        $currentCents = (int) round(($this->effectiveParkingCharge() ?? 0) * 100);

        return max(0, $currentCents - ($this->parking_billed_cents ?? 0));
    }

    /**
     * earlyCheckinCharge() minus whatever's already been billed (via the
     * combined pre-checkin charge or a prior standalone early check-in
     * charge). Same idea as unbilledIncidentalsCents().
     */
    public function unbilledEarlyCheckinCents(): int
    {
        $currentCents = (int) round(($this->earlyCheckinCharge() ?? 0) * 100);

        return max(0, $currentCents - ($this->early_checkin_billed_cents ?? 0));
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
            $range = $in->format('M j').'-'.$out->format('j');
        } elseif ($in->format('Y') === $out->format('Y')) {
            $range = $in->format('M j').' - '.$out->format('M j');
        } else {
            $range = $in->format('M j, Y').' - '.$out->format('M j, Y');
        }

        return $range.' '.$this->nightsLabel();
    }

    /**
     * Number of nights for this stay, based on check_in_date/check_out_date.
     * Returns null if either date is missing (task 27).
     */
    public function nightsCount(): ?int
    {
        if (!$this->check_in_date || !$this->check_out_date) {
            return null;
        }

        return (int) $this->check_in_date->diffInDays($this->check_out_date);
    }

    /**
     * "(X night)" / "(X nights)" bracketed label for display next to dates,
     * per the client's request to show nights count next to dates everywhere
     * (task 27). Returns an empty string if nights can't be determined.
     */
    public function nightsLabel(): string
    {
        $nights = $this->nightsCount();

        if ($nights === null) {
            return '';
        }

        return '('.$nights.' '.Str::plural('night', $nights).')';
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

    /**
     * The charge for a granted early check-in exception, looked up flat
     * from the property's rate for whichever time-window tier was granted
     * (task 26, updated per client clarification to three explicit
     * windows: 8am-12pm, 12pm-2pm, 2pm-4pm — each a distinct flat charge,
     * different per property). Returns null if no tier was granted or the
     * property hasn't set that window's rate yet.
     */
    public function earlyCheckinCharge(): ?float
    {
        if (!$this->early_checkin_tier || !$this->property) {
            return null;
        }

        $rate = match ($this->early_checkin_tier) {
            '8am_12pm', '8am' => $this->property->early_checkin_rate_8am_12pm ?? $this->property->early_checkin_rate_8am,
            '12pm_2pm', '12pm' => $this->property->early_checkin_rate_12pm_2pm ?? $this->property->early_checkin_rate_12pm,
            '2pm_4pm' => $this->property->early_checkin_rate_2pm_4pm,
            default => null,
        };

        return $rate !== null ? (float) $rate : null;
    }

    /**
     * The property's standard checkout instant for this booking's checkout
     * day, respecting the guest's chosen checkout time preference if set.
     * Used only for the unauthorized late-checkout hour calculation below —
     * deliberately independent of checked_out_at and the auto-checkout
     * scheduled command (task 23), so the two features never interact.
     */
    protected function standardCheckoutInstant(): ?CarbonInterface
    {
        if (!$this->check_out_date || !$this->property) {
            return null;
        }

        [$hour, $minute] = array_map('intval', explode(':', $this->effectiveCheckoutTime()));

        return $this->check_out_date->copy()->setTime($hour, $minute);
    }

    /**
     * Hours late for an unauthorized late checkout, computed from the
     * admin-entered actual checkout time vs. the standard checkout time.
     * This is intentionally separate from checked_out_at (which may be set
     * by the auto-checkout command and doesn't reflect when the guest
     * actually left) — task 26 explicitly calls for a manually recorded
     * actual time for the unauthorized case.
     */
    public function lateCheckoutHoursUnauthorized(): ?float
    {
        if ($this->late_checkout_type !== 'unauthorized' || !$this->late_checkout_actual_time) {
            return null;
        }

        $standard = $this->standardCheckoutInstant();
        if (!$standard) {
            return null;
        }

        $minutesLate = $standard->diffInMinutes($this->late_checkout_actual_time, false);

        return $minutesLate > 0 ? round($minutesLate / 60, 2) : 0.0;
    }

    /**
     * The late-checkout charge, either authorized (admin-entered hours ×
     * property's authorized hourly rate) or unauthorized (hours computed
     * from the admin-entered actual checkout time × unauthorized hourly
     * rate). Returns null if no late-checkout type is set, or the needed
     * rate/hours aren't available yet.
     */
    /**
     * The late-checkout charge, billed per half-hour (30 minute) block —
     * per client clarification, not a raw hourly multiplication. Any
     * partial block is rounded up (a guest 10 minutes into a new block
     * still owes for that whole block). Authorized uses the
     * admin-entered hours × the property's authorized per-30-min rate;
     * unauthorized uses hours computed from the admin-entered actual
     * checkout time × the (higher) unauthorized per-30-min rate. Returns
     * null if no late-checkout type is set, or the needed rate/hours
     * aren't available yet.
     */
    public function lateCheckoutCharge(): ?float
    {
        if (!$this->late_checkout_type || !$this->property) {
            return null;
        }

        if ($this->late_checkout_type === 'authorized') {
            $rate = $this->property->late_checkout_rate_authorized_per_30min ?? $this->property->late_checkout_rate_authorized_hourly;
            if ($this->late_checkout_hours === null || $rate === null) {
                return null;
            }

            return $this->chargeForHoursInHalfHourBlocks((float) $this->late_checkout_hours, (float) $rate);
        }

        if ($this->late_checkout_type === 'unauthorized') {
            $hours = $this->lateCheckoutHoursUnauthorized();
            $rate = $this->property->late_checkout_rate_unauthorized_per_30min ?? $this->property->late_checkout_rate_unauthorized_hourly;
            if ($hours === null || $rate === null) {
                return null;
            }

            return $this->chargeForHoursInHalfHourBlocks($hours, (float) $rate);
        }

        return null;
    }

    /**
     * Converts a number of hours into whole half-hour blocks (rounding any
     * partial block up) and multiplies by the given per-block rate.
     */
    private function chargeForHoursInHalfHourBlocks(float $hours, float $ratePer30Min): float
    {
        $blocks = (int) ceil(($hours * 60) / 30);

        return round($blocks * $ratePer30Min, 2);
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

    public function daysUntilCheckIn(?CarbonInterface $date = null): int
    {
        $date ??= now();

        return (int) $date->copy()->startOfDay()->diffInDays($this->check_in_date->copy()->startOfDay(), false);
    }

    public function daysUntilCheckOut(?CarbonInterface $date = null): int
    {
        $date ??= now();

        return (int) $date->copy()->startOfDay()->diffInDays($this->check_out_date->copy()->startOfDay(), false);
    }

    /**
     * Dynamic status line for the admin "This Week" guest card (task 8):
     * checked-in/checking-in-today guests show a countdown to checkout,
     * recently checked-out guests show how long ago they left, everyone
     * else falls back to the plain nights-of-stay label.
     */
    public function weekCardDynamicLabel(): string
    {
        if (! $this->checked_out_at && ($this->isCheckedIn() || $this->manually_checked_in || $this->status === 'currently_hosting' || $this->isCheckinDay())) {
            $daysLeft = $this->daysUntilCheckOut();

            return $daysLeft <= 0 ? 'Checks out today' : 'Checks out in '.$daysLeft.' '.Str::plural('day', $daysLeft);
        }

        return $this->nightsLabel();
    }

    /**
     * Countdown label for the admin "Next Week" guest card (task 9),
     * e.g. "Arriving in 5 days".
     */
    public function arrivalCountdownLabel(): string
    {
        $daysUntil = $this->daysUntilCheckIn();

        return $daysUntil <= 0 ? 'Arriving today' : 'Arriving in '.$daysUntil.' '.Str::plural('day', $daysUntil);
    }

    /**
     * Which "week card" bucket this booking currently falls into, per the
     * client's requested sort order (task 8):
     * 1 = currently checked in / checking in today, 2 = recently checked out,
     * 3 = upcoming stay.
     */
    public function weekCardSortTier(): int
    {
        if (! $this->checked_out_at && ($this->isCheckedIn() || $this->manually_checked_in || $this->status === 'currently_hosting' || $this->isCheckinDay())) {
            return 1;
        }

        if ($this->checked_out_at) {
            return 2;
        }

        return 3;
    }

    public function isSameDayBooking(): bool
    {
        return $this->created_at->toDateString() === $this->check_in_date->toDateString();
    }

    public function needsVehicleInfoPrompt(): bool
    {
        if (! $this->parking_needed) {
            return false;
        }

        if ($this->vehicle_info_bypassed_at) {
            return false;
        }

        if ($this->vehicle_make_model && $this->license_plate_photo_path) {
            return false;
        }

        if ($this->isSameDayBooking()) {
            return true;
        }

        return $this->daysUntilCheckIn() <= 1;
    }

    /**
     * The property's standard check-in time. Falls back to '16:00' in code
     * only (no DB default, no global Setting) when the property doesn't
     * have one configured.
     */
    public function standardCheckinTime(): string
    {
        return $this->property?->checkin_time ?: '16:00';
    }

    public function standardCheckinTimeFormatted(): string
    {
        return $this->safeFormatTime($this->standardCheckinTime());
    }

    /**
     * The property's standard check-out time. Falls back to '10:00' in code
     * only — never reached for any existing property, since those already
     * have a real stored value from the original column default.
     */
    public function standardCheckoutTime(): string
    {
        return $this->property?->checkout_time ?: '10:00';
    }

    public function standardCheckoutTimeFormatted(): string
    {
        return $this->safeFormatTime($this->standardCheckoutTime());
    }

    /**
     * The check-in time actually in effect for this booking. A guest's
     * requested preference is only honored once admin-approved; otherwise
     * this falls back to the property's standard time. A preference request
     * is a *request*, not an automatic override — approval may carry a
     * charge (see early_checkin_tier / task 26 billing).
     */
    public function effectiveCheckinTime(): string
    {
        if ($this->checkin_time_preference && $this->checkin_time_status === 'approved') {
            return $this->checkin_time_preference;
        }

        return $this->standardCheckinTime();
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

    /**
     * The check-out time actually in effect for this booking. Same approval
     * gate as effectiveCheckinTime() — a guest's requested preference only
     * applies once admin-approved, otherwise falls back to the property's
     * standard checkout time.
     */
    public function effectiveCheckoutTime(): string
    {
        if ($this->checkout_time_preference && $this->checkout_time_status === 'approved') {
            return $this->checkout_time_preference;
        }

        return $this->standardCheckoutTime();
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

    public function getFormattedPhoneAttribute(): ?string
    {
        return PhoneFormatter::format($this->phone);
    }
}
