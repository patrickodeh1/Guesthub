<?php
namespace App\Services;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SmsNotificationService
{
    protected static function send(string $message): void
    {
        $to = config('services.twilio.admin_notify_number');
        self::sendTo($to, $message, 'admin');
    }

    /**
     * Send an SMS to an arbitrary number (e.g. the guest's phone), as opposed to
     * the fixed admin-notify number used by the existing admin-facing alerts.
     */
    protected static function sendTo(?string $to, string $message, string $context = 'guest'): void
    {
        $sid = config('services.twilio.sid');
        $authToken = config('services.twilio.auth_token');
        $from = config('services.twilio.from_number');

        if (! $sid || ! $authToken || ! $from || ! $to) {
            Log::warning("SMS notification skipped ({$context}): Twilio not fully configured or recipient missing.");
            return;
        }

        try {
            $client = new Client($sid, $authToken);
            $client->messages->create($to, [
                'from' => $from,
                'body' => $message,
            ]);
        } catch (\Throwable $e) {
            Log::error("SMS notification failed ({$context}): ".$e->getMessage());
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

    /**
     * Text the GUEST (not the admin) that a side of their ID was declined.
     */
    public static function photoIdDeclinedToGuest(Booking $booking, string $side, string $reason): void
    {
        $sideLabel = $side === 'back' ? 'back' : 'front';
        self::sendTo(
            $booking->phone,
            "GuestHub: The {$sideLabel} of your ID was not approved. Reason: {$reason}. Please log back in to re-upload it.",
            'guest'
        );
    }
}
