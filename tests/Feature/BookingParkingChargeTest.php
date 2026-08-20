<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingParkingChargeTest extends TestCase
{
    use RefreshDatabase;

    protected function makeProperty(array $overrides = []): Property
    {
        return Property::create(array_merge([
            'name' => 'Test Property',
            'slug' => 'test-property-'.Str::random(6),
            'checkout_time' => '11:00',
        ], $overrides));
    }

    protected function makeBooking(Property $property, array $overrides = []): Booking
    {
        return Booking::create(array_merge([
            'booking_id' => 'TEST-'.Str::random(6),
            'guest_name' => 'Jane Doe',
            'phone' => '+15555550123',
            'email' => 'jane@example.com',
            'property_id' => $property->id,
            'token' => Str::random(40),
            'status' => 'guest_approved',
        ], $overrides));
    }

    public function test_parking_charge_sums_per_weekday_rates_across_stay(): void
    {
        // A known week: Mon 2026-08-24 through Wed 2026-08-26 (2 nights: Mon, Tue)
        $property = $this->makeProperty([
            'parking_rate_monday' => 10.00,
            'parking_rate_tuesday' => 15.00,
            'parking_rate_wednesday' => 20.00,
        ]);

        $booking = $this->makeBooking($property, [
            'check_in_date' => '2026-08-24',
            'check_out_date' => '2026-08-26',
            'parking_needed' => true,
        ]);

        $this->assertEquals(25.00, $booking->calculateParkingCharge());
    }

    public function test_parking_charge_is_null_when_parking_not_needed(): void
    {
        $property = $this->makeProperty(['parking_rate_monday' => 10.00]);

        $booking = $this->makeBooking($property, [
            'check_in_date' => '2026-08-24',
            'check_out_date' => '2026-08-25',
            'parking_needed' => false,
        ]);

        $this->assertNull($booking->calculateParkingCharge());
    }

    public function test_unconfigured_weekday_rate_counts_as_zero_not_an_error(): void
    {
        $property = $this->makeProperty([
            'parking_rate_monday' => 10.00,
            // tuesday left unconfigured (null)
        ]);

        $booking = $this->makeBooking($property, [
            'check_in_date' => '2026-08-24', // Monday
            'check_out_date' => '2026-08-26', // Wednesday (2 nights: Mon, Tue)
            'parking_needed' => true,
        ]);

        $this->assertEquals(10.00, $booking->calculateParkingCharge());
    }

    public function test_recalculate_persists_parking_charge(): void
    {
        $property = $this->makeProperty(['parking_rate_monday' => 12.50]);

        $booking = $this->makeBooking($property, [
            'check_in_date' => '2026-08-24',
            'check_out_date' => '2026-08-25',
            'parking_needed' => true,
        ]);

        $booking->recalculateParkingCharge();

        $this->assertEquals(12.50, $booking->fresh()->parking_charge);
    }

    public function test_effective_parking_charge_prefers_admin_override(): void
    {
        $property = $this->makeProperty(['parking_rate_monday' => 10.00]);

        $booking = $this->makeBooking($property, [
            'check_in_date' => '2026-08-24',
            'check_out_date' => '2026-08-25',
            'parking_needed' => true,
            'parking_charge' => 10.00,
            'parking_charge_override' => 45.00,
        ]);

        $this->assertEquals(45.00, $booking->effectiveParkingCharge());
    }

    public function test_effective_parking_charge_falls_back_to_auto_calculated_when_no_override(): void
    {
        $property = $this->makeProperty(['parking_rate_monday' => 10.00]);

        $booking = $this->makeBooking($property, [
            'check_in_date' => '2026-08-24',
            'check_out_date' => '2026-08-25',
            'parking_needed' => true,
            'parking_charge' => 10.00,
            'parking_charge_override' => null,
        ]);

        $this->assertEquals(10.00, $booking->effectiveParkingCharge());
    }

    public function test_guest_answering_parking_question_triggers_recalculation(): void
    {
        $property = $this->makeProperty(['parking_rate_monday' => 20.00]);

        $booking = $this->makeBooking($property, [
            'check_in_date' => '2026-08-24',
            'check_out_date' => '2026-08-25',
            'parking_needed' => null,
        ]);

        $response = $this->post(route('guest.parking', ['booking_id' => $booking->booking_id, 'token' => $booking->token]), [
            'parking_needed' => true,
        ]);

        $response->assertRedirect();
        $this->assertEquals(20.00, $booking->fresh()->parking_charge);
    }

    public function test_admin_can_set_parking_charge_override(): void
    {
        $admin = User::factory()->create();
        $property = $this->makeProperty(['parking_rate_monday' => 10.00]);
        $booking = $this->makeBooking($property, [
            'check_in_date' => '2026-08-24',
            'check_out_date' => '2026-08-25',
            'parking_needed' => true,
            'reservation_id' => 'RES-'.Str::random(6),
        ]);

        $response = $this->actingAs($admin)->put(route('admin.guests.update', $booking), [
            'reservation_id' => $booking->reservation_id,
            'guest_name' => $booking->guest_name,
            'check_in_date' => '2026-08-24',
            'check_out_date' => '2026-08-25',
            'property_id' => $property->id,
            'id_type' => 'state_id',
            'status' => 'guest_approved',
            'parking_needed' => '1',
            'parking_charge_override' => '99.99',
        ]);

        $response->assertRedirect();
        $booking->refresh();
        $this->assertEquals(99.99, $booking->parking_charge_override);
        $this->assertEquals(99.99, $booking->effectiveParkingCharge());
    }
}
