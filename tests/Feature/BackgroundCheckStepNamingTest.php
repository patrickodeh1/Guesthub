<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BackgroundCheckStepNamingTest extends TestCase
{
    use RefreshDatabase;

    protected function makeBookingAwaitingBackgroundCheck(): Booking
    {
        $property = Property::create([
            'name' => 'Test Property',
            'slug' => 'test-property-'.Str::random(6),
            'checkout_time' => '11:00',
        ]);

        return Booking::create([
            'booking_id' => 'TEST-'.Str::random(6),
            'guest_name' => 'Jane Doe',
            'phone' => '+15555550123',
            'email' => 'jane@example.com',
            'check_in_date' => now()->addDay(),
            'check_out_date' => now()->addDays(3),
            'property_id' => $property->id,
            'id_type' => 'license',
            'token' => Str::random(32),
            'photo_id_path' => 'photo-ids/front.jpg',
            'photo_id_received' => true,
            'approved_at' => now(),
            'identity_confirmed_at' => now(),
            'photo_id_front_approved_at' => now(),
            'status' => 'pre_checkin_complete',
        ]);
    }

    public function test_default_step_name_and_instructions_shown_when_unconfigured(): void
    {
        $booking = $this->makeBookingAwaitingBackgroundCheck();

        $response = $this->get(route('guest.show', [$booking->booking_id, $booking->token]));

        $response->assertOk();
        $response->assertSee('Background Check');
        $response->assertSee('Please be on the lookout for an email from Airbnb', false);
    }

    public function test_custom_step_name_and_instructions_are_shown_instead(): void
    {
        Setting::putValue('background_check_step_name', 'ID Verification');
        Setting::putValue('background_check_step_instructions', 'We are verifying your ID now.');

        $booking = $this->makeBookingAwaitingBackgroundCheck();

        $response = $this->get(route('guest.show', [$booking->booking_id, $booking->token]));

        $response->assertOk();
        $response->assertSee('ID Verification');
        $response->assertSee('We are verifying your ID now.');
        $response->assertDontSee('Please be on the lookout for an email from Airbnb', false);
    }
}
