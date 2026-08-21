<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingEarlyCheckinLateCheckoutChargeTest extends TestCase
{
    use RefreshDatabase;

    protected function makeProperty(array $overrides = []): Property
    {
        return Property::create(array_merge([
            'name' => 'Test Property',
            'slug' => 'test-property-'.Str::random(6),
            'checkout_time' => '11:00',
            'timezone' => 'America/New_York',
        ], $overrides));
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

    // --- Early check-in tiers ---

    public function test_early_checkin_charge_looks_up_flat_rate_for_8am_tier(): void
    {
        $property = $this->makeProperty([
            'early_checkin_rate_8am' => 50.00,
            'early_checkin_rate_12pm' => 25.00,
        ]);
        $booking = $this->makeBooking($property, ['early_checkin_tier' => '8am']);

        $this->assertEquals(50.00, $booking->earlyCheckinCharge());
    }

    public function test_early_checkin_charge_looks_up_flat_rate_for_12pm_tier(): void
    {
        $property = $this->makeProperty([
            'early_checkin_rate_8am' => 50.00,
            'early_checkin_rate_12pm' => 25.00,
        ]);
        $booking = $this->makeBooking($property, ['early_checkin_tier' => '12pm']);

        $this->assertEquals(25.00, $booking->earlyCheckinCharge());
    }

    public function test_early_checkin_charge_is_null_when_no_tier_set(): void
    {
        $property = $this->makeProperty(['early_checkin_rate_8am' => 50.00]);
        $booking = $this->makeBooking($property, ['early_checkin_tier' => null]);

        $this->assertNull($booking->earlyCheckinCharge());
    }

    public function test_early_checkin_tier_is_independent_of_early_checkin_boolean(): void
    {
        // The address-visibility exception (early_checkin) and the billing
        // tier (early_checkin_tier) must not depend on each other.
        $property = $this->makeProperty(['early_checkin_rate_8am' => 50.00]);
        $booking = $this->makeBooking($property, [
            'early_checkin' => false,
            'early_checkin_tier' => '8am',
        ]);

        $this->assertFalse($booking->early_checkin);
        $this->assertEquals(50.00, $booking->earlyCheckinCharge());
    }

    // --- Late checkout: authorized ---

    public function test_authorized_late_checkout_charge_uses_admin_entered_hours(): void
    {
        $property = $this->makeProperty(['late_checkout_rate_authorized_hourly' => 20.00]);
        $booking = $this->makeBooking($property, [
            'late_checkout_type' => 'authorized',
            'late_checkout_hours' => 2.5,
        ]);

        $this->assertEquals(50.00, $booking->lateCheckoutCharge());
    }

    public function test_authorized_late_checkout_charge_null_without_hours(): void
    {
        $property = $this->makeProperty(['late_checkout_rate_authorized_hourly' => 20.00]);
        $booking = $this->makeBooking($property, [
            'late_checkout_type' => 'authorized',
            'late_checkout_hours' => null,
        ]);

        $this->assertNull($booking->lateCheckoutCharge());
    }

    // --- Late checkout: unauthorized ---

    public function test_unauthorized_late_checkout_hours_computed_from_actual_time(): void
    {
        $property = $this->makeProperty(['checkout_time' => '11:00']);
        $booking = $this->makeBooking($property, [
            'check_out_date' => '2026-08-25',
            'late_checkout_type' => 'unauthorized',
            'late_checkout_actual_time' => '2026-08-25 13:30:00',
        ]);

        $this->assertEquals(2.5, $booking->lateCheckoutHoursUnauthorized());
    }

    public function test_unauthorized_late_checkout_charge_multiplies_hours_by_rate(): void
    {
        $property = $this->makeProperty([
            'checkout_time' => '11:00',
            'late_checkout_rate_unauthorized_hourly' => 30.00,
        ]);
        $booking = $this->makeBooking($property, [
            'check_out_date' => '2026-08-25',
            'late_checkout_type' => 'unauthorized',
            'late_checkout_actual_time' => '2026-08-25 13:00:00',
        ]);

        $this->assertEquals(60.00, $booking->lateCheckoutCharge());
    }

    public function test_unauthorized_late_checkout_hours_zero_when_actual_time_not_late(): void
    {
        $property = $this->makeProperty(['checkout_time' => '11:00']);
        $booking = $this->makeBooking($property, [
            'check_out_date' => '2026-08-25',
            'late_checkout_type' => 'unauthorized',
            'late_checkout_actual_time' => '2026-08-25 10:00:00',
        ]);

        $this->assertEquals(0.0, $booking->lateCheckoutHoursUnauthorized());
    }

    public function test_unauthorized_late_checkout_charge_null_without_actual_time(): void
    {
        $property = $this->makeProperty(['late_checkout_rate_unauthorized_hourly' => 30.00]);
        $booking = $this->makeBooking($property, [
            'late_checkout_type' => 'unauthorized',
            'late_checkout_actual_time' => null,
        ]);

        $this->assertNull($booking->lateCheckoutCharge());
    }

    // --- Decoupling from auto-checkout / task 23 ---

    public function test_late_checkout_billing_does_not_read_or_write_checked_out_at(): void
    {
        // Simulate the auto-checkout command's effect: checked_out_at gets
        // set independently of any late-checkout billing fields.
        $property = $this->makeProperty([
            'checkout_time' => '11:00',
            'late_checkout_rate_unauthorized_hourly' => 30.00,
        ]);
        $booking = $this->makeBooking($property, [
            'check_out_date' => '2026-08-25',
            'checked_out_at' => '2026-08-25 11:30:00', // auto-checkout grace-period timestamp
            'late_checkout_type' => 'unauthorized',
            'late_checkout_actual_time' => '2026-08-25 15:00:00', // admin's real recorded time
        ]);

        // The charge should be driven entirely by late_checkout_actual_time,
        // not by checked_out_at, even though both are set.
        $this->assertEquals(4.0, $booking->lateCheckoutHoursUnauthorized());
        $this->assertEquals(120.00, $booking->lateCheckoutCharge());

        // And setting/reading late-checkout billing fields must never mutate
        // checked_out_at.
        $this->assertEquals('2026-08-25 11:30:00', $booking->checked_out_at->format('Y-m-d H:i:s'));
    }

    public function test_admin_can_set_early_checkin_tier_and_late_checkout_fields(): void
    {
        $admin = User::factory()->create();
        $property = $this->makeProperty([
            'early_checkin_rate_8am' => 40.00,
            'late_checkout_rate_authorized_hourly' => 15.00,
        ]);
        $booking = $this->makeBooking($property);

        $response = $this->actingAs($admin)->put(route('admin.guests.update', $booking), [
            'reservation_id' => $booking->reservation_id,
            'guest_name' => $booking->guest_name,
            'check_in_date' => '2026-08-24',
            'check_out_date' => '2026-08-25',
            'property_id' => $property->id,
            'id_type' => 'state_id',
            'status' => 'guest_approved',
            'early_checkin_tier' => '8am',
            'late_checkout_type' => 'authorized',
            'late_checkout_hours' => '3',
        ]);

        $response->assertRedirect();
        $booking->refresh();
        $this->assertEquals('8am', $booking->early_checkin_tier);
        $this->assertEquals(40.00, $booking->earlyCheckinCharge());
        $this->assertEquals('authorized', $booking->late_checkout_type);
        $this->assertEquals(45.00, $booking->lateCheckoutCharge());
    }
}
