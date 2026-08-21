<?php

namespace Tests\Feature;

use App\Models\EarlyAccessLead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EarlyAccessLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_domain_shows_landing_page_not_admin_login(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontRedirect();
        $response->assertSee('Request Early Access');
    }

    public function test_admin_login_still_reachable_directly(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_early_access_form_stores_a_lead(): void
    {
        $response = $this->post(route('early-access.store'), [
            'name' => 'Alex Host',
            'email' => 'alex@example.com',
            'phone' => '5551234567',
            'role' => 'host',
            'message' => 'Interested in early access.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('early_access_leads', [
            'email' => 'alex@example.com',
            'role' => 'host',
        ]);
    }

    public function test_early_access_form_requires_name_email_and_role(): void
    {
        $response = $this->post(route('early-access.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'role']);
        $this->assertEquals(0, EarlyAccessLead::count());
    }
}
