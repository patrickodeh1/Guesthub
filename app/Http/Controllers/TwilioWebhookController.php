<?php

namespace App\Http\Controllers;

use App\Services\SmsConsentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Twilio\Security\RequestValidator;

class TwilioWebhookController extends Controller
{
    public function handle(Request $request)
    {
        if (! $this->hasValidSignature($request)) {
            Log::warning('Twilio webhook rejected: invalid or missing signature.');
            return response()->json(['ok' => false], 403);
        }

        $phone = $request->input('From') ?? $request->input('phone');
        $body = (string) ($request->input('Body') ?? '');

        if (! $phone) {
            return response()->json(['ok' => false], 400);
        }

        SmsConsentService::handleTwilioKeyword($phone, $body);

        return response()->json(['ok' => true]);
    }

    /**
     * Verifies the request actually came from Twilio using the account auth
     * token, per Twilio's signature scheme. If no auth token is configured
     * (local/dev), validation is skipped rather than hard-failing every
     * request. NOTE: if this app sits behind a proxy/load balancer that
     * rewrites the scheme/host, ensure config('app.url') and the request's
     * resolved fullUrl() match what Twilio actually POSTed to, or set
     * trusted proxies in bootstrap/app.php — otherwise valid requests can
     * be rejected here.
     */
    protected function hasValidSignature(Request $request): bool
    {
        $authToken = config('services.twilio.auth_token');

        if (! $authToken) {
            return true;
        }

        $signature = $request->header('X-Twilio-Signature', '');
        if (! $signature) {
            return false;
        }

        $validator = new RequestValidator($authToken);

        return $validator->validate($signature, $request->fullUrl(), $request->all());
    }
}
