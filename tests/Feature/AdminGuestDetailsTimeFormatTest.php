<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminGuestDetailsTimeFormatTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create();
    }

    protected function makeBooking(array $overrides = []): Booking
    {
        $property = Property::create([
            'name' => 'Test Property',
            'slug' => 'test-property-'.Str::random(6),
            'checkout_time' => '11:00',
        ]);

        return Booking::create(array_merge([
            'booking_id' => 'TEST-'.Str::random(6),
            'guest_name' => 'Jane Doe',
            'phone' => '+15555550123',
            'email' => 'jane@example.com',
            'check_in_date' => now()->addDay(),
            'check_out_date' => now()->addDays(3),
            'property_id' => $property->id,
            'id_type' => 'license',
            'token' => Str::random(32),
            'status' => 'pending',
            'checkin_time_preference' => '16:00',
            'checkout_time_preference' => '10:00',
        ], $overrides));
    }

    public function test_requested_times_show_as_12_hour_am_pm_not_24_hour(): void
    {
        $booking = $this->makeBooking();

        $response = $this->actingAs($this->admin())->get(route('admin.guests.show', $booking));

        $response->assertOk();
        $response->assertSee('4:00 PM');
        $response->assertSee('10:00 AM');
        // Raw 24-hour strings should not appear anywhere as the displayed value.
        $response->assertDontSee('>16:00<', false);
        $response->assertDontSee('>10:00<', false);
    }

    public function test_unset_time_preference_still_shows_not_specified(): void
    {
        $booking = $this->makeBooking([
            'checkin_time_preference' => null,
            'checkout_time_preference' => null,
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.guests.show', $booking));

        $response->assertOk();
        $response->assertSeeInOrder(['Requested Check-in Time', 'Not specified']);
        $response->assertSeeInOrder(['Requested Check-out Time', 'Not specified']);
    }
}
