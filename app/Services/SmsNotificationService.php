<?php
namespace App\Services;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SmsNotificationService
{
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

    /**
     * Send an already-rendered lifecycle alert message (task 30) to an
     * arbitrary number — the guest's phone, or the admin's notify number.
     */
    public static function guestAlert(string $to, string $message): void
    {
        self::sendTo($to, $message, 'guest_alert');
    }
}
