<?php

namespace Tests\Feature;

use App\Mail\PhotoIdDeclinedMail;
use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class PhotoIdSideApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function makeBookingWithBothSides(): Booking
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
            'photo_id_back_path' => 'photo-ids/back.jpg',
            'photo_id_received' => false,
            'status' => 'pre_checkin_complete',
        ]);
    }

    protected function admin(): User
    {
        return User::factory()->create();
    }

    public function test_declining_front_only_notifies_guest_and_leaves_back_untouched(): void
    {
        Mail::fake();

        $booking = $this->makeBookingWithBothSides();

        // Approve the back first, so we can confirm it survives a front decline.
        $this->actingAs($this->admin())
            ->post(route('admin.guests.id.approve', [$booking, 'back']))
            ->assertRedirect();

        $booking->refresh();
        $this->assertTrue($booking->isBackIdApproved());
        $this->assertFalse($booking->isIdFullyApproved()); // front still pending

        $this->actingAs($this->admin())
            ->post(route('admin.guests.id.decline', [$booking, 'front']), [
                'decline_reason' => 'Photo is blurry',
            ])
            ->assertRedirect();

        $booking->refresh();

        // Front cleared and forced to re-upload; back untouched.
        $this->assertNull($booking->photo_id_path);
        $this->assertEquals('Photo is blurry', $booking->photo_id_front_declined_reason);
        $this->assertNotNull($booking->photo_id_back_path);
        $this->assertTrue($booking->isBackIdApproved());

        Mail::assertSent(PhotoIdDeclinedMail::class, function ($mail) use ($booking) {
            return $mail->hasTo($booking->email)
                && $mail->side === 'front'
                && $mail->reason === 'Photo is blurry';
        });
    }

    public function test_approving_both_sides_independently_sets_overall_approval(): void
    {
        $booking = $this->makeBookingWithBothSides();

        $this->actingAs($this->admin())
            ->post(route('admin.guests.id.approve', [$booking, 'front']));
        $booking->refresh();
        $this->assertFalse($booking->isApproved());

        $this->actingAs($this->admin())
            ->post(route('admin.guests.id.approve', [$booking, 'back']));
        $booking->refresh();

        $this->assertTrue($booking->isFrontIdApproved());
        $this->assertTrue($booking->isBackIdApproved());
        $this->assertTrue($booking->isIdFullyApproved());
        $this->assertTrue($booking->isApproved());
        $this->assertTrue($booking->photo_id_received);
    }

    public function test_resubmitting_only_the_declined_side_does_not_require_the_other_side(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        $booking = $this->makeBookingWithBothSides();

        $this->actingAs($this->admin())
            ->post(route('admin.guests.id.approve', [$booking, 'back']));

        $this->actingAs($this->admin())
            ->post(route('admin.guests.id.decline', [$booking, 'front']), [
                'decline_reason' => 'Too dark',
            ]);

        $booking->refresh();

        $response = $this->post(route('guest.identity', [$booking->booking_id, $booking->token]), [
            'photo_id' => \Illuminate\Http\UploadedFile::fake()->image('front.jpg'),
        ]);

        $response->assertSessionDoesntHaveErrors('photo_id_back');

        $booking->refresh();
        $this->assertNotNull($booking->photo_id_path);
        $this->assertNull($booking->photo_id_front_declined_reason);
        $this->assertNotNull($booking->photo_id_back_path); // still on file, untouched
    }
}
