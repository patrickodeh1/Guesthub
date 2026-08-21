<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GuestWelcomeGuideCardsTest extends TestCase
{
    use RefreshDatabase;

    protected function makeGuideBooking(array $overrides = []): Booking
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
            'check_in_date' => now()->subDay(),
            'check_out_date' => now()->addDays(2),
            'property_id' => $property->id,
            'id_type' => 'license',
            'token' => Str::random(32),
            'status' => 'currently_hosting',
            'gps_verified' => true,
            'identity_confirmed_at' => now()->subDay(),
            'photo_id_received' => true,
            'checked_in_at' => now()->subDay(),
        ], $overrides));
    }

    public function test_welcome_guide_heading_and_intro_text_are_removed(): void
    {
        $booking = $this->makeGuideBooking();

        $response = $this->get(route('guest.show', [$booking->booking_id, $booking->token]));
        $content = $response->getContent();

        $this->assertStringNotContainsString('Everything you need during your stay is ready below.', $content);
        $this->assertStringNotContainsString('Explore information about your stay.', $content);
    }

    public function test_welcome_guide_shows_guest_name_and_checkout_date_time_card(): void
    {
        $booking = $this->makeGuideBooking();

        $response = $this->get(route('guest.show', [$booking->booking_id, $booking->token]));
        $response->assertOk();
        $response->assertSee('Jane Doe');
        $response->assertSee($booking->check_out_date->format('M d, Y'));
        $response->assertSee($booking->effectiveCheckoutTimeFormatted());
    }

    public function test_welcome_guide_status_card_and_icon_grid_still_render(): void
    {
        $booking = $this->makeGuideBooking();

        $response = $this->get(route('guest.show', [$booking->booking_id, $booking->token]));
        $content = $response->getContent();

        $this->assertStringContainsString('Checked in', $content);
        $this->assertStringContainsString('id="guide-grid"', $content);
        $this->assertStringContainsString('id="guest-guide-section"', $content);
    }
}
