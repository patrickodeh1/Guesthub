<?php

namespace Tests\Feature;

use App\Mail\GuestAlertMail;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Setting;
use App\Services\GuestAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class GuestAlertServiceTest extends TestCase
{
    use RefreshDatabase;

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
            'token' => Str::random(32),
            'status' => 'pending',
            'parking_needed' => true,
        ], $overrides));
    }

    public function test_default_config_has_all_six_events_with_defaults(): void
    {
        $config = GuestAlertService::config();

        $this->assertCount(6, $config);
        foreach (array_keys(GuestAlertService::EVENTS) as $key) {
            $this->assertArrayHasKey($key, $config);
            $this->assertTrue($config[$key]['guest_sms']);
        }
    }

    public function test_send_emails_guest_when_guest_email_enabled(): void
    {
        Mail::fake();

        $config = GuestAlertService::config();
        $config['registration_received']['guest_email'] = true;
        $config['registration_received']['guest_sms'] = false;
        $config['registration_received']['admin_sms'] = false;
        GuestAlertService::putConfig($config);

        $booking = $this->makeBooking();
        GuestAlertService::send('registration_received', $booking);

        Mail::assertSent(GuestAlertMail::class, function (GuestAlertMail $mail) use ($booking) {
            return $mail->hasTo($booking->email) && str_contains($mail->message, 'Jane Doe');
        });
    }

    public function test_send_does_not_email_guest_when_guest_email_disabled(): void
    {
        Mail::fake();

        $booking = $this->makeBooking();
        GuestAlertService::send('registration_received', $booking); // defaults: guest_email false

        Mail::assertNotSent(GuestAlertMail::class);
    }

    public function test_message_tokens_are_substituted(): void
    {
        Mail::fake();

        $config = GuestAlertService::config();
        $config['fully_approved']['message'] = 'Hi {guest_name}, parking is {parking_status} for {property_name}.';
        $config['fully_approved']['guest_email'] = true;
        GuestAlertService::putConfig($config);

        $booking = $this->makeBooking(['parking_needed' => true]);
        GuestAlertService::send('fully_approved', $booking);

        Mail::assertSent(GuestAlertMail::class, function (GuestAlertMail $mail) {
            return str_contains($mail->message, 'Hi Jane Doe, parking is confirmed for Test Property.');
        });
    }

    public function test_unknown_event_is_a_safe_no_op(): void
    {
        Mail::fake();

        $booking = $this->makeBooking();
        GuestAlertService::send('not_a_real_event', $booking);

        Mail::assertNothingSent();
        $this->assertTrue(true); // no exception thrown
    }
}
