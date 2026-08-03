<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'booking_id', 'guest_name', 'phone', 'email', 'check_in_date', 'check_out_date',
        'property_id', 'token', 'photo_id_path', 'photo_id_back_path', 'photo_id_received', 'parking_needed', 'early_checkin', 'checkin_time_preference', 'gps_verified',
        'manually_checked_in', 'checked_in_at', 'gps_overridden', 'status', 'notes', 'welcome_message', 'identity_confirmed_at',
        'approved_at', 'decline_reason',
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
            'approved_at' => 'datetime',
            'identity_confirmed_at' => 'datetime',
                    ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function isIdentityComplete(): bool
    {
        return filled($this->identity_confirmed_at);
    }

    public function isCheckedIn(): bool
    {
        return $this->gps_verified || $this->manually_checked_in || $this->status === 'checked_in';
    }

    public function isApproved(): bool
    {
        return filled($this->approved_at);
    }

    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    public function needsIdApproval(): bool
    {
        return filled($this->photo_id_path) && ! $this->isApproved();
    }

    public function instructionsCompleted(): bool
    {
        return $this->manually_checked_in || $this->status === 'checked_in';
    }

    public function isCheckinDay(?CarbonInterface $date = null): bool
    {
        $date ??= now();

        return $date->toDateString() >= $this->check_in_date->toDateString();
    }

    public function canViewAddress(?CarbonInterface $now = null): bool
    {
        $timezone = $this->property?->timezone ?? 'America/New_York';
        $now = ($now ?? now())->setTimezone($timezone);

        if ($this->early_checkin) return true;

        $checkinDate = $this->check_in_date->toDateString();

        if ($now->toDateString() < $checkinDate) return false;
        if ($now->toDateString() > $checkinDate) return true;

        return $now->hour >= 15;
    }

    public function isCheckoutDay(?CarbonInterface $date = null): bool
    {
        $date ??= now();

        return $date->toDateString() >= $this->check_out_date->toDateString();
    }

    public function publicUrl(): string
    {
        return route('guest.show', [$this->booking_id, $this->token]);
    }

    public function statusLabel(): string
    {
        if ($this->status === 'id_uploaded' && $this->isApproved()) {
            return 'Approved';
        }
        if ($this->status === 'id_uploaded') {
            return 'Ready for Screening';
        }
        return str($this->status ?: 'pending')->replace('_', ' ')->title()->toString();
    }
}
