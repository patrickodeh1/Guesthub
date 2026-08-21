<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class VehicleInfoCollectionTest extends TestCase
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
        ], $overrides));
    }

    protected function loginPayload(Booking $booking, array $overrides = []): array
    {
        return array_merge([
            'guest_name' => 'Jane Doe',
            'phone' => '+15555550123',
            'email' => 'jane@example.com',
            'checkin_time_preference' => '15:00',
        ], $overrides);
    }

    public function test_vehicle_fields_required_when_parking_needed_yes(): void
    {
        $booking = $this->makeBooking();

        $response = $this->postJson(
            route('guest.login', [$booking->booking_id, $booking->token]),
            $this->loginPayload($booking, ['parking_needed' => '1'])
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['vehicle_make_model', 'license_plate_photo']);
    }

    public function test_vehicle_fields_not_required_when_parking_needed_no(): void
    {
        $booking = $this->makeBooking();

        $response = $this->postJson(
            route('guest.login', [$booking->booking_id, $booking->token]),
            $this->loginPayload($booking, ['parking_needed' => '0'])
        );

        $response->assertOk();
        $booking->refresh();
        $this->assertFalse((bool) $booking->parking_needed);
        $this->assertNull($booking->vehicle_make_model);
        $this->assertNull($booking->license_plate_photo_path);
    }

    public function test_vehicle_info_saved_and_photo_stored_when_parking_needed_yes(): void
    {
        Storage::fake('local');

        $booking = $this->makeBooking();
        $photo = UploadedFile::fake()->image('plate.jpg');

        $response = $this->post(
            route('guest.login', [$booking->booking_id, $booking->token]),
            $this->loginPayload($booking, [
                'parking_needed' => '1',
                'vehicle_make_model' => 'Toyota Camry',
                'license_plate_photo' => $photo,
            ])
        );

        $response->assertOk();
        $booking->refresh();
        $this->assertTrue((bool) $booking->parking_needed);
        $this->assertSame('Toyota Camry', $booking->vehicle_make_model);
        $this->assertNotNull($booking->license_plate_photo_path);
        Storage::disk('local')->assertExists($booking->license_plate_photo_path);
    }

    public function test_photo_not_required_again_if_already_on_file(): void
    {
        Storage::fake('local');

        $booking = $this->makeBooking([
            'parking_needed' => true,
            'vehicle_make_model' => 'Honda Civic',
            'license_plate_photo_path' => 'license-plates/existing.jpg',
        ]);
        Storage::disk('local')->put('license-plates/existing.jpg', 'fake-image-bytes');

        // Guest re-saves step 1 (e.g. edits check-in time) without re-uploading a plate photo.
        $response = $this->postJson(
            route('guest.login', [$booking->booking_id, $booking->token]),
            $this->loginPayload($booking, ['checkin_time_preference' => '16:00'])
        );

        $response->assertOk();
        $booking->refresh();
        $this->assertSame('license-plates/existing.jpg', $booking->license_plate_photo_path);
    }

    public function test_uploading_new_photo_replaces_existing_path(): void
    {
        Storage::fake('local');

        $booking = $this->makeBooking([
            'parking_needed' => true,
            'vehicle_make_model' => 'Honda Civic',
            'license_plate_photo_path' => 'license-plates/existing.jpg',
        ]);
        $newPhoto = UploadedFile::fake()->image('new-plate.jpg');

        $response = $this->post(
            route('guest.login', [$booking->booking_id, $booking->token]),
            $this->loginPayload($booking, ['license_plate_photo' => $newPhoto])
        );

        $response->assertOk();
        $booking->refresh();
        $this->assertNotSame('license-plates/existing.jpg', $booking->license_plate_photo_path);
        Storage::disk('local')->assertExists($booking->license_plate_photo_path);
    }
}
