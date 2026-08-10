<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'booking_id', 'guest_name', 'phone', 'email', 'check_in_date', 'check_out_date',
        'property_id', 'id_type', 'token', 'photo_id_path', 'photo_id_back_path', 'photo_id_received', 'parking_needed', 'early_checkin', 'checkin_time_preference', 'gps_verified',
        'manually_checked_in', 'checked_in_at', 'checked_out_at', 'gps_overridden', 'status', 'notes', 'welcome_message', 'identity_confirmed_at',
        'approved_at', 'decline_reason', 'archived_at', 'background_check_completed_at', 'deposit_verified_at',
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
            'approved_at' => 'datetime',
            'identity_confirmed_at' => 'datetime',
            'archived_at' => 'datetime',
            'background_check_completed_at' => 'datetime',
            'deposit_verified_at' => 'datetime',
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

    public function stayRangeLabel(): string
    {
        $in = $this->check_in_date;
        $out = $this->check_out_date;

        if ($in->format('Y-m') === $out->format('Y-m')) {
            return $in->format('M j').' through the '.$out->format('jS');
        }

        if ($in->format('Y') === $out->format('Y')) {
            return $in->format('M j').' through '.$out->format('M j');
        }

        return $in->format('M j, Y').' through '.$out->format('M j, Y');
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
        return \Carbon\Carbon::createFromFormat('H:i', $this->effectiveCheckinTime())->format('g:i A');
    }

    public function canViewAddress(?CarbonInterface $now = null): bool
    {
        $timezone = $this->property?->timezone ?? 'America/New_York';
        $now = ($now ?? now())->setTimezone($timezone);

        if ($this->early_checkin) return true;

        $checkinDate = $this->check_in_date->toDateString();

        if ($now->toDateString() < $checkinDate) return false;
        if ($now->toDateString() > $checkinDate) return true;

        [$hour, $minute] = array_map('intval', explode(':', $this->effectiveCheckinTime()));
        return $now->hour > $hour || ($now->hour === $hour && $now->minute >= $minute);
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
        return $this->property?->checkout_time ?: '11:00';
    }

    public function effectiveCheckoutTimeFormatted(): string
    {
        return \Carbon\Carbon::createFromFormat('H:i', $this->effectiveCheckoutTime())->format('g:i A');
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

    public function publicUrl(): string
    {
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
