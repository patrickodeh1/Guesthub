<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Property extends Model
{
    protected $fillable = [
        'name', 'unit_number', 'slug', 'address', 'city', 'state', 'zip', 'latitude', 'longitude', 'events_radius_miles', 'timezone', 'checkout_time',
        'map_embed_url', 'map_directions_url', 'contact_phone', 'contact_email',
        'welcome_intro', 'checkin_instructions', 'parking_instructions',
        'checkout_instructions', 'header_image', 'active',
    ];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7'];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'property_category')
            ->withPivot(['custom_title', 'custom_description', 'header_image', 'active'])
            ->withTimestamps()
            ->orderBy('categories.sort_order');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(CategoryPage::class);
    }

    public function amenities(): HasMany
    {
        return $this->hasMany(Amenity::class);
    }

    public function fullAddress(): string
    {
        return collect([$this->address, $this->city, $this->state, $this->zip])->filter()->join(', ');
    }

    public function instructionSteps(): HasMany
    {
        return $this->hasMany(InstructionStep::class)->orderBy('sort_order');
    }

    public function locks(): HasMany
    {
        return $this->hasMany(PropertyLock::class);
    }

    public function heroImageUrl(): string
    {
        return $this->header_image
            ? url('/img/'.$this->header_image)
            : 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1400&q=80';
    }
}
