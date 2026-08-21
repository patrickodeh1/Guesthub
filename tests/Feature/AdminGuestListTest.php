<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminGuestListTest extends TestCase
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
            'reservation_id' => 'RID-'.Str::random(6),
            'guest_name' => 'Jane Doe',
            'phone' => '+15555550123',
            'email' => 'jane@example.com',
            'check_in_date' => now()->addDay(),
            'check_out_date' => now()->addDays(3),
            'property_id' => $property->id,
            'id_type' => 'license',
            'token' => Str::random(32),
            'status' => 'pending',
        ], $overrides));
    }

    public function test_guest_list_has_no_stat_cards_and_a_single_search_field(): void
    {
        $this->makeBooking();

        $response = $this->actingAs($this->admin())->get(route('admin.guests.index'));

        $response->assertOk();
        $response->assertDontSee('Total Guests');
        $response->assertDontSee('Today\'s Arrivals');
        $response->assertDontSee('Waiting Approval');
        $response->assertSee('name="search"', false);
        $response->assertDontSee('name="status"', false);
        $response->assertDontSee('name="property_id"', false);
    }

    public function test_guest_list_row_has_no_standalone_view_or_edit_buttons(): void
    {
        $this->makeBooking();

        $response = $this->actingAs($this->admin())->get(route('admin.guests.index'));

        $response->assertOk();
        // "View Details" and "Edit" should only exist inside the action menu now,
        // not as their own top-level buttons.
        $response->assertDontSee('btn-secondary gap-2" href', false);
    }

    public function test_guest_list_search_matches_reservation_id(): void
    {
        $booking = $this->makeBooking(['reservation_id' => 'AIRBNB-UNIQUE-123']);
        $this->makeBooking(['guest_name' => 'Someone Else']);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.guests.index', ['search' => 'AIRBNB-UNIQUE-123']));

        $response->assertOk();
        $response->assertSee('Jane Doe');
        $response->assertDontSee('Someone Else');
    }

    public function test_needs_attention_shows_bookings_with_id_pending_approval(): void
    {
        $waiting = $this->makeBooking([
            'guest_name' => 'Pending Review',
            'photo_id_path' => 'photo-ids/front.jpg',
            'photo_id_received' => false,
        ]);
        $this->makeBooking([
            'guest_name' => 'No ID Yet',
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.guests.index'));

        $response->assertOk();
        $response->assertSee('Needs Attention');
        $response->assertSee('Pending Review');
    }

    public function test_needs_attention_omits_far_out_booking_once_approved(): void
    {
        $this->makeBooking([
            'guest_name' => 'Already Approved',
            'photo_id_path' => 'photo-ids/front.jpg',
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.guests.index'));

        $response->assertOk();
        $response->assertDontSee('Needs Attention');
    }

    public function test_action_menu_includes_quick_actions_from_edit_screen(): void
    {
        $booking = $this->makeBooking([
            'photo_id_path' => 'photo-ids/front.jpg',
            'photo_id_received' => false,
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.guests.index'));

        $response->assertOk();
        $response->assertSee('Mark Photo ID Received');
        $response->assertSee('Approve for Check-In');
        $response->assertSee('Manually Mark Checked In');
    }
}
