<?php
namespace App\Services;
use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsNotificationService
{
    /**
     * Send an SMS to an arbitrary number (e.g. the guest's phone), as opposed to
     * the fixed admin-notify number used by the existing admin-facing alerts.
     *
     * Uses Telnyx's plain REST API (POST /v2/messages) directly via Http,
     * rather than pulling in a dedicated SDK — same approach used for the
     * Channex integration, keeps composer surface small.
     */
    protected static function sendTo(?string $to, string $message, string $context = 'guest'): void
    {
        $apiKey = config('services.telnyx.api_key');
        $from = config('services.telnyx.from_number');

        if (! $apiKey || ! $from || ! $to) {
            Log::warning("SMS notification skipped ({$context}): Telnyx not fully configured or recipient missing.");
            return;
        }

        try {
            $payload = array_filter([
                'from' => $from,
                'to' => $to,
                'text' => $message,
                'messaging_profile_id' => config('services.telnyx.messaging_profile_id'),
            ]);

            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->post('https://api.telnyx.com/v2/messages', $payload);

            if (! $response->successful()) {
                Log::error("SMS notification failed ({$context}): ".$response->body());
            }
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
