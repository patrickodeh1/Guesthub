<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\Pms\BookingImportService;
use App\Services\Pms\PmsProviderInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChannexWebhookController extends Controller
{
    public function handle(Request $request, PmsProviderInterface $provider, BookingImportService $importer)
    {
        $secret = config('services.channex.webhook_secret');

        if ($secret) {
            // ASSUMPTION: Channex signs webhooks via an
            // X-Channex-Signature header containing an HMAC-SHA256 of the
            // raw body using the webhook secret. Confirm exact header name
            // and signing scheme against live docs before relying on this
            // in production — left permissive (skips verification) when no
            // secret is configured so local/dev testing via ngrok isn't
            // blocked before that's set up.
            $signature = $request->header('X-Channex-Signature');
            $expected = hash_hmac('sha256', $request->getContent(), $secret);

            if (! $signature || ! hash_equals($expected, $signature)) {
                ActivityLogService::security('channex_webhook_invalid_signature', 'Rejected a Channex webhook with an invalid signature.', [
                    'severity' => 'warning',
                ]);
                return response()->json(['ok' => false, 'error' => 'Invalid signature'], 401);
            }
        }

        $payload = $request->json()->all();
        Log::info('Channex webhook received', ['payload' => $payload]);

        $pmsBooking = $provider->handleWebhookPayload($payload);

        if (! $pmsBooking) {
            return response()->json(['ok' => true, 'skipped' => true]);
        }

        $booking = $importer->import($pmsBooking);

        if ($booking) {
            $provider->acknowledgeBooking($pmsBooking->externalBookingId);
        }

        return response()->json(['ok' => true]);
    }
}
