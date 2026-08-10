<?php
namespace App\Services;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SmsNotificationService
{
    protected static function send(string $message): void
    {
        $sid = config('services.twilio.sid');
        $authToken = config('services.twilio.auth_token');
        $from = config('services.twilio.from_number');
        $to = config('services.twilio.admin_notify_number');

        if (! $sid || ! $authToken || ! $from || ! $to) {
            Log::warning('SMS notification skipped: Twilio not fully configured.');
            return;
        }

        try {
            $client = new Client($sid, $authToken);
            $client->messages->create($to, [
                'from' => $from,
                'body' => $message,
            ]);
        } catch (\Throwable $e) {
            Log::error('SMS notification failed: '.$e->getMessage());
        }
    }

    public static function preCheckinComplete(Booking $booking): void
    {
        self::send("GuestHub: {$booking->guest_name} completed pre-check-in and uploaded their ID for {$booking->property->name}.");
    }

    public static function guestCheckedIn(Booking $booking): void
    {
        self::send("GuestHub: {$booking->guest_name} has checked in at {$booking->property->name}.");
    }

    public static function guestCheckedOut(Booking $booking): void
    {
        self::send("GuestHub: {$booking->guest_name} has checked out of {$booking->property->name}.");
    }
}
