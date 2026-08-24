<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Charge;
use Stripe\StripeClient;

/**
 * Single point of contact with Stripe. Deliberately not deposit-specific in
 * name/shape — deposit is just the first consumer — so a future
 * registration-fee charge (task 2) or a second processor can reuse this
 * without a redesign.
 *
 * For now: every charge type, including the deposit, is captured
 * immediately (capture_method=automatic) rather than authorized-as-a-hold.
 * This is a deliberate simplification — Stripe auto-cancels an uncaptured
 * hold after 7 days by default and a guest stay can easily exceed that, so
 * a hold-based deposit isn't reliable without more infrastructure (renewing
 * the authorization, handling expiry mid-stay, etc.) than is worth building
 * right now. Refunds for a deposit that turns out not to be owed are a
 * follow-up decision, not handled by this service yet.
 */
class PaymentService
{
    private ?StripeClient $client = null;

    public function isConfigured(): bool
    {
        return filled(config('services.stripe.secret'));
    }

    private function client(): StripeClient
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('Stripe is not configured (STRIPE_SECRET missing).');
        }

        return $this->client ??= new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Charge a guest immediately for the given amount/type. Used for the
     * deposit and every other charge type (parking, early check-in, late
     * checkout, incidentals) — all captured right away, at whichever moment
     * the amount becomes due/known.
     */
    public function charge(Booking $booking, string $type, int $amountCents, string $paymentMethodId, string $billingMoment, ?string $description = null): Charge
    {
        $intent = $this->client()->paymentIntents->create([
            'amount' => $amountCents,
            'currency' => 'usd',
            'payment_method' => $paymentMethodId,
            'capture_method' => 'automatic',
            'confirm' => true,
            'description' => $description ?: "{$type} charge for booking {$booking->booking_id}",
            'metadata' => ['booking_id' => $booking->id, 'type' => $type],
        ]);

        $charge = $booking->charges()->create([
            'type' => $type,
            'amount_cents' => $amountCents,
            'status' => $intent->status === 'succeeded' ? Charge::STATUS_CAPTURED : Charge::STATUS_FAILED,
            'stripe_payment_intent_id' => $intent->id,
            'billing_moment' => $billingMoment,
            'captured_at' => $intent->status === 'succeeded' ? now() : null,
            'description' => $description,
        ]);

        if ($type === Charge::TYPE_DEPOSIT) {
            $booking->update([
                'deposit_payment_status' => $charge->status,
                'deposit_stripe_payment_intent_id' => $intent->id,
                'deposit_amount_cents' => $amountCents,
            ]);
        }

        return $charge;
    }
}
