<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GuestCheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function makeCheckedInBooking(array $overrides = []): Booking
    {
        $property = Property::create([
            'name' => 'Test Property',
            'slug' => 'test-property-'.Str::random(6),
            'checkout_time' => '10:00',
            'timezone' => 'America/New_York',
        ]);

        return Booking::create(array_merge([
            'booking_id' => 'TEST-'.Str::random(6),
            'guest_name' => 'Jane Doe',
            'phone' => '+15555550123',
            'email' => 'jane@example.com',
            'check_in_date' => now()->subDay(),
            'check_out_date' => now(),
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

    public function test_visiting_guest_link_past_checkout_time_does_not_silently_flip_status(): void
    {
        // Guest is 1 hour past checkout time but never pressed "All Done".
        $booking = $this->makeCheckedInBooking([
            'check_out_date' => now()->subHours(1)->startOfDay(),
        ]);

        $response = $this->get(route('guest.show', [$booking->booking_id, $booking->token]));
        $response->assertOk();

        $booking->refresh();
        $this->assertNotEquals('checked_out', $booking->status, 'Merely loading the guest page must never silently mark a booking checked out.');
    }

    public function test_checkout_wizard_shows_confirmation_modal_before_finalizing(): void
    {
        $booking = $this->makeCheckedInBooking();

        $response = $this->get(route('guest.show', [$booking->booking_id, $booking->token]));
        $content = $response->getContent();

        if (str_contains($content, 'id="checkout-wizard-wrapper"')) {
            $this->assertStringContainsString('checkout-confirm-modal', $content);
            $this->assertStringContainsString("Ready to check out?", $content);
            $this->assertStringContainsString('checkout-confirm-proceed', $content);
        } else {
            $this->markTestSkipped('No checkout steps configured for this property; confirmation modal only renders alongside the checkout wizard.');
        }
    }

    public function test_reloading_after_checkout_confirmation_shows_locked_page_not_guide(): void
    {
        // Regression test for task 29: after the guest confirms checkout, the
        // very next page load must show the locked "all checked out" page
        // (no guide/menu), not the guide the wizard used to fall back into.
        $booking = $this->makeCheckedInBooking();

        $this->post(route('guest.confirm-checkout', [$booking->booking_id, $booking->token]))
            ->assertOk();

        $booking->refresh();
        $this->assertEquals('checked_out', $booking->status);

        $response = $this->get(route('guest.show', [$booking->booking_id, $booking->token]));
        $response->assertOk();
        $response->assertSee("You're all checked out");
        $response->assertDontSee('id="guide-grid"', false);
    }


    {
        // 10 minutes past checkout time — inside the default 30-minute grace period.
        $withinGrace = $this->makeCheckedInBooking([
            'check_out_date' => now()->toDateString(),
        ]);
        $withinGrace->update(['check_out_date' => now()->subMinutes(10)->startOfDay()]);

        $pastGrace = $this->makeCheckedInBooking([
            'check_out_date' => now()->subDay()->startOfDay(),
        ]);

        $this->artisan('bookings:auto-checkout')->assertSuccessful();

        $this->assertNotEquals('checked_out', $withinGrace->fresh()->status, 'Booking still within its grace period should not be auto-checked-out yet.');
        $this->assertEquals('checked_out', $pastGrace->fresh()->status, 'Booking well past its grace period should be auto-checked-out.');
    }

    public function test_lock_control_hidden_outside_checkin_checkout_window(): void
    {
        $property = Property::create([
            'name' => 'Lock Test Property',
            'slug' => 'lock-test-property-'.Str::random(6),
            'checkout_time' => '10:00',
            'timezone' => 'America/New_York',
        ]);

        $category = \App\Models\Category::create([
            'title' => 'Door Lock',
            'slug' => 'door-lock-'.Str::random(6),
            'action' => 'door_lock',
            'active' => true,
            'is_global' => true,
            'sort_order' => 1,
        ]);

        // Guest not yet checked in (waiting on check-in day, GPS not verified).
        $waitingBooking = $this->makeCheckedInBooking([
            'property_id' => $property->id,
            'status' => 'guest_approved',
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(2),
            'gps_verified' => false,
            'checked_in_at' => null,
        ]);

        $response = $this->get(route('guest.category', [$waitingBooking->booking_id, $waitingBooking->token, $category]));
        $response->assertOk();
        $response->assertSee("Door controls are available once you're checked in, and until check-out.");
        $response->assertDontSee('lock-toggle-btn', false);
    }
}
