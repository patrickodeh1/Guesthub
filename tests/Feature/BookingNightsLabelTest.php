<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingNightsLabelTest extends TestCase
{
    use RefreshDatabase;

    protected function makeProperty(): Property
    {
        return Property::create([
            'name' => 'Test Property',
            'slug' => 'test-property-'.Str::random(6),
            'checkout_time' => '11:00',
        ]);
    }

    protected function makeBooking(Property $property, array $overrides = []): Booking
    {
        return Booking::create(array_merge([
            'booking_id' => 'TEST-'.Str::random(6),
            'reservation_id' => 'RES-'.Str::random(6),
            'guest_name' => 'Jane Doe',
            'phone' => '+15555550123',
            'email' => 'jane@example.com',
            'check_in_date' => '2026-08-24',
            'check_out_date' => '2026-08-25',
            'property_id' => $property->id,
            'token' => Str::random(40),
            'status' => 'guest_approved',
        ], $overrides));
    }

    public function test_nights_count_computes_difference_between_dates(): void
    {
        $property = $this->makeProperty();
        $booking = $this->makeBooking($property, [
            'check_in_date' => '2026-09-01',
            'check_out_date' => '2026-09-04',
        ]);

        $this->assertSame(3, $booking->nightsCount());
    }

    public function test_nights_label_uses_singular_for_one_night(): void
    {
        $property = $this->makeProperty();
        $booking = $this->makeBooking($property, [
            'check_in_date' => '2026-09-01',
            'check_out_date' => '2026-09-02',
        ]);

        $this->assertSame('(1 night)', $booking->nightsLabel());
    }

    public function test_nights_label_uses_plural_for_multiple_nights(): void
    {
        $property = $this->makeProperty();
        $booking = $this->makeBooking($property, [
            'check_in_date' => '2026-09-01',
            'check_out_date' => '2026-09-05',
        ]);

        $this->assertSame('(4 nights)', $booking->nightsLabel());
    }

    public function test_nights_label_is_blank_when_dates_missing(): void
    {
        $property = $this->makeProperty();
        $booking = $this->makeBooking($property);
        $booking->check_out_date = null;

        $this->assertSame('', $booking->nightsLabel());
    }

    public function test_stay_range_label_includes_nights_label(): void
    {
        $property = $this->makeProperty();
        $booking = $this->makeBooking($property, [
            'check_in_date' => '2026-09-01',
            'check_out_date' => '2026-09-04',
        ]);

        $this->assertSame('Sep 1-4 (3 nights)', $booking->stayRangeLabel());
    }
}
