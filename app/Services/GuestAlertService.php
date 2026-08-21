<?php

namespace App\Services;

use App\Mail\GuestAlertMail;
use App\Models\Booking;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the 6 guest lifecycle alerts requested in task 30, with globally
 * customizable message templates and per-event checkboxes (task 31) for
 * whether the guest and/or the admin/owner receive each one, over SMS
 * and/or email. Client confirmed: "text are preferred" but both channels
 * are needed and are owner-selectable per alert, not guest-selectable.
 *
 * All settings live under a single Setting key ('guest_alerts_config') as
 * one JSON blob, since this is really one coherent settings unit (a table
 * of 6 rows x several toggles) rather than several independent values.
 */
class GuestAlertService
{
    public const EVENTS = [
        'registration_received' => [
            'label' => 'Registration received',
            'default_message' => "GuestHub: Hi {guest_name}, we've received your registration for {property_name}. We'll be in touch as the next steps are ready.",
        ],
        'background_check_complete' => [
            'label' => 'Background check complete',
            'default_message' => 'GuestHub: Hi {guest_name}, your {step_name} for {property_name} is complete.',
        ],
        'fully_approved' => [
            'label' => 'Fully approved',
            'default_message' => "GuestHub: Hi {guest_name}, you're fully approved for {property_name}! Check-in: {check_in_time} on {check_in_date}. Check-out: {check_out_time} on {check_out_date}. Parking: {parking_status}.",
        ],
        'time_to_check_in' => [
            'label' => 'Time to check in',
            'default_message' => "GuestHub: Hi {guest_name}, today's the day! Check-in at {property_name} opens at {check_in_time}.",
        ],
        'checkin_completed' => [
            'label' => 'Check-in completed',
            'default_message' => "GuestHub: Hi {guest_name}, you're checked in at {property_name}. Enjoy your stay!",
        ],
        'checkout_completed' => [
            'label' => 'Check-out completed',
            'default_message' => 'GuestHub: Hi {guest_name}, thanks for staying at {property_name}. You are now checked out.',
        ],
        'photo_id_uploaded' => [
            'label' => 'Photo ID uploaded',
            'default_message' => 'GuestHub: {guest_name} uploaded a photo ID for {property_name}. Please review it.',
        ],
    ];

    /**
     * Default toggle state for an event when nothing has been configured yet.
     * Both SMS and email, for both guest and admin, are on by default so a
     * fresh install notifies both parties over both channels out of the box;
     * each can still be turned off per event from Settings.
     */
    public static function defaultToggles(): array
    {
        return [
            'guest_sms' => true,
            'guest_email' => true,
            'admin_sms' => true,
            'admin_email' => true,
        ];
    }

    public static function config(): array
    {
        $stored = json_decode(Setting::getValue('guest_alerts_config', '') ?: '', true) ?: [];
        $config = [];

        foreach (self::EVENTS as $key => $meta) {
            $config[$key] = array_merge(
                ['message' => $meta['default_message']],
                self::defaultToggles(),
                $stored[$key] ?? []
            );
        }

        return $config;
    }

    /**
     * Event labels for display, with the "background check" step's name
     * substituted in wherever it's admin-customizable (task 32: "whatever
     * the name for that step is for registration will need to appear in
     * the user settings under text alerts as that stepped name").
     */
    public static function labels(): array
    {
        $stepName = Setting::getValue('background_check_step_name', 'Background Check');
        $labels = [];

        foreach (self::EVENTS as $key => $meta) {
            $labels[$key] = $key === 'background_check_complete'
                ? "{$stepName} complete"
                : $meta['label'];
        }

        return $labels;
    }

    public static function putConfig(array $config): void
    {
        $clean = [];

        foreach (self::EVENTS as $key => $meta) {
            $row = $config[$key] ?? [];
            $clean[$key] = [
                'message' => trim((string) ($row['message'] ?? $meta['default_message'])) ?: $meta['default_message'],
                'guest_sms' => (bool) ($row['guest_sms'] ?? false),
                'guest_email' => (bool) ($row['guest_email'] ?? false),
                'admin_sms' => (bool) ($row['admin_sms'] ?? false),
                'admin_email' => (bool) ($row['admin_email'] ?? false),
            ];
        }

        Setting::putValue('guest_alerts_config', json_encode($clean));
    }

    /**
     * Send the alert for a given lifecycle event, per the current settings.
     * Safe to call even if the event key is unknown (no-op) so call sites
     * never need to guard against typos causing a hard failure.
     */
    public static function send(string $event, Booking $booking): void
    {
        if (! array_key_exists($event, self::EVENTS)) {
            return;
        }

        $row = self::config()[$event];
        $message = self::render($row['message'], $booking);

        if ($row['guest_sms'] && $booking->phone) {
            SmsNotificationService::guestAlert($booking->phone, $message);
        }

        if ($row['guest_email'] && $booking->email) {
            try {
                Mail::to($booking->email)->send(new GuestAlertMail(self::labels()[$event], $message));
            } catch (\Throwable $e) {
                Log::error("Guest alert email failed (guest, {$event}): ".$e->getMessage());
            }
        }

        $envAdminNumber = config('services.twilio.admin_notify_number');
        $settingsAdminNumber = self::normalizePhoneForSms(Setting::getValue('contact_phone'));
        $adminEmail = Setting::getValue('contact_email');

        if ($row['admin_sms']) {
            // Send to both the .env-configured number and the admin Settings number,
            // if both are present and different, so existing .env-based setups keep
            // working while the Settings page becomes the primary source going forward.
            $adminNumbers = collect([$envAdminNumber, $settingsAdminNumber])
                ->filter()
                ->unique()
                ->values();

            foreach ($adminNumbers as $adminNumber) {
                SmsNotificationService::guestAlert($adminNumber, "[{$booking->guest_name}] ".$message);
            }
        }

        if ($row['admin_email'] && $adminEmail) {
            try {
                Mail::to($adminEmail)->send(new GuestAlertMail(self::labels()[$event], "[{$booking->guest_name}] ".$message));
            } catch (\Throwable $e) {
                Log::error("Guest alert email failed (admin, {$event}): ".$e->getMessage());
            }
        }
    }

    /**
     * Strip formatting characters (spaces, dashes, parens) from a phone number
     * pulled from Settings so it's in a Twilio-friendly format. Returns null
     * if the input is empty so callers can skip sending cleanly.
     */
    protected static function normalizePhoneForSms(?string $number): ?string
    {
        if (! $number) {
            return null;
        }

        $normalized = preg_replace('/[^\d+]/', '', $number);

        return $normalized ?: null;
    }

    protected static function render(string $template, Booking $booking): string
    {
        $parkingStatus = is_null($booking->parking_needed)
            ? 'not yet specified'
            : ($booking->parking_needed ? 'confirmed' : 'not needed');

        return strtr($template, [
            '{guest_name}' => $booking->guest_name,
            '{property_name}' => $booking->property?->name ?? 'your property',
            '{check_in_date}' => $booking->check_in_date?->format('M j, Y') ?? '',
            '{check_out_date}' => $booking->check_out_date?->format('M j, Y') ?? '',
            '{check_in_time}' => $booking->effectiveCheckinTimeFormatted(),
            '{check_out_time}' => $booking->effectiveCheckoutTimeFormatted(),
            '{parking_status}' => $parkingStatus,
            '{step_name}' => Setting::getValue('background_check_step_name', 'Background Check'),
        ]);
    }
}
