<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Database\Seeders\WelcomeGuideSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomeGuideTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_requires_auth_and_renders_for_admin(): void
    {
        $this->seed(WelcomeGuideSeeder::class);

        $this->get('/admin')->assertRedirect('/login');

        $this->actingAs(User::where('email', 'admin@example.com')->first())
            ->get('/admin')
            ->assertOk()
            ->assertSee('Recent guests');
    }

    public function test_seeded_guest_url_renders_identity_step(): void
    {
        $this->seed(WelcomeGuideSeeder::class);

        $booking = Booking::where('booking_id', 'LUMINA-DEMO')->firstOrFail();

        $this->get(route('guest.show', [$booking->booking_id, $booking->token]))
            ->assertOk()
            ->assertSee('Complete your pre-arrival details');
    }
}
