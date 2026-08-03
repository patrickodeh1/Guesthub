<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Booking;

class CategoryPage extends Model
{
    protected $fillable = [
        'property_id', 'category_id', 'title', 'content', 'image_1', 'image_2', 'image_3', 'sort_order', 'active',
    ];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function renderContent(Booking $booking): string
    {
        if (!$this->content) return '';

        return str_replace(
            [
                '[[guest_name]]',
                '[[guest_first_name]]',
                '[[guest_last_name]]',
                '[[guest_phone]]',
                '[[booking_id]]',
                '[[check_in_date]]',
                '[[check_out_date]]',
                '[[property_name]]',
                '[[property_address]]',
            ],
            [
                $booking->guest_name,
                str($booking->guest_name)->before(' ')->toString(),
                str($booking->guest_name)->after(' ')->toString(),
                $booking->phone,
                $booking->booking_id,
                $booking->check_in_date->format('M d, Y'),
                $booking->check_out_date->format('M d, Y'),
                $booking->property->name,
                $booking->property->address,
            ],
            $this->content
        );
    }

    public function images(): array
    {
        return collect([$this->image_1, $this->image_2, $this->image_3])->filter()->values()->all();
    }
}
