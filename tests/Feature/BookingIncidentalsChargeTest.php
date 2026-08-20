<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingIncidentalsChargeTest extends TestCase
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

    public function test_incidentals_charge_defaults_to_null(): void
    {
        $booking = $this->makeBooking($this->makeProperty());

        $this->assertNull($booking->incidentals_charge);
    }

    public function test_admin_can_set_incidentals_charge(): void
    {
        $admin = User::factory()->create();
        $property = $this->makeProperty();
        $booking = $this->makeBooking($property);

        $response = $this->actingAs($admin)->put(route('admin.guests.update', $booking), [
            'reservation_id' => $booking->reservation_id,
            'guest_name' => $booking->guest_name,
            'check_in_date' => '2026-08-24',
            'check_out_date' => '2026-08-25',
            'property_id' => $property->id,
            'id_type' => 'state_id',
            'status' => 'guest_approved',
            'incidentals_charge' => '75.50',
        ]);

        $response->assertRedirect();
        $this->assertEquals(75.50, $booking->fresh()->incidentals_charge);
    }

    public function test_incidentals_charge_is_independent_per_guest(): void
    {
        $property = $this->makeProperty();
        $bookingA = $this->makeBooking($property, ['incidentals_charge' => 20.00]);
        $bookingB = $this->makeBooking($property, ['incidentals_charge' => 150.00]);

        $this->assertEquals(20.00, $bookingA->fresh()->incidentals_charge);
        $this->assertEquals(150.00, $bookingB->fresh()->incidentals_charge);
    }

    public function test_incidentals_charge_not_exposed_on_guest_facing_routes(): void
    {
        $property = $this->makeProperty();
        $booking = $this->makeBooking($property, ['incidentals_charge' => 42.00]);

        $response = $this->get(route('guest.show', ['booking_id' => $booking->booking_id, 'token' => $booking->token]));

        $response->assertOk();
        $response->assertDontSee('42.00');
        $response->assertDontSee('incidentals', false);
    }
}
