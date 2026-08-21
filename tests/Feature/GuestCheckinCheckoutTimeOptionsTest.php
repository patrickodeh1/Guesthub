<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GuestCheckinCheckoutTimeOptionsTest extends TestCase
{
    use RefreshDatabase;

    protected function makeBooking(): Booking
    {
        $property = Property::create([
            'name' => 'Test Property',
            'slug' => 'test-property-'.Str::random(6),
            'checkout_time' => '10:00',
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
            'status' => 'pending',
        ]);
    }

    public function test_checkin_time_recommends_4pm_and_lists_it_first(): void
    {
        $booking = $this->makeBooking();

        $response = $this->get(route('guest.show', [$booking->booking_id, $booking->token]));

        $response->assertOk();
        $content = $response->getContent();

        // 4:00 PM should be marked recommended.
        $this->assertStringContainsString('4:00 PM (Recommended)', $content);
        // The helper text should reference 4pm, not 10am.
        $this->assertStringContainsString('Check in time is 4pm', $content);
        $this->assertStringNotContainsString('Check in time is 10am', $content);

        // 4:00 PM's <option> should appear before 8:00 AM's <option> in the check-in select.
        $selectStart = strpos($content, 'id="checkin_time_preference_select"');
        $selectEnd = strpos($content, '</select>', $selectStart);
        $selectHtml = substr($content, $selectStart, $selectEnd - $selectStart);

        $posRecommended = strpos($selectHtml, '4:00 PM');
        $posEarliest = strpos($selectHtml, '8:00 AM');

        $this->assertNotFalse($posRecommended);
        $this->assertNotFalse($posEarliest);
        $this->assertLessThan($posEarliest, $posRecommended, 'Recommended 4:00 PM check-in time should be listed before 8:00 AM.');
    }

    public function test_checkout_time_range_is_7am_to_2pm_with_10am_recommended_first(): void
    {
        $booking = $this->makeBooking();

        $response = $this->get(route('guest.show', [$booking->booking_id, $booking->token]));
        $content = $response->getContent();

        $selectStart = strpos($content, 'id="checkout_time_preference_select"');
        $selectEnd = strpos($content, '</select>', $selectStart);
        $selectHtml = substr($content, $selectStart, $selectEnd - $selectStart);

        // In range.
        $this->assertStringContainsString('7:00 AM', $selectHtml);
        $this->assertStringContainsString('10:00 AM (Recommended)', $selectHtml);
        $this->assertStringContainsString('2:00 PM', $selectHtml);

        // Out of range (old 10am-8pm range should no longer include hours past 2pm).
        $this->assertStringNotContainsString('3:00 PM', $selectHtml);
        $this->assertStringNotContainsString('8:00 PM', $selectHtml);
        $this->assertStringNotContainsString('6:00 AM', $selectHtml);

        // Recommended (10am) listed before 7am.
        $posRecommended = strpos($selectHtml, '10:00 AM');
        $posEarliest = strpos($selectHtml, '7:00 AM');
        $this->assertLessThan($posEarliest, $posRecommended, 'Recommended 10:00 AM checkout time should be listed before 7:00 AM.');
    }
}
