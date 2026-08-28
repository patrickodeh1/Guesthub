@php
    $stripeConfiguredForCharges = filled(config('services.stripe.key')) && filled(config('services.stripe.secret'));
    $lateCheckoutAmountCents = (int) round(($booking->lateCheckoutCharge() ?? 0) * 100);
    $lateCheckoutTotalCents = $booking->applyProcessingFeeCents($lateCheckoutAmountCents);
    $lateCheckoutProcessingFeeCents = $lateCheckoutTotalCents - $lateCheckoutAmountCents;
    $lateCheckoutPaid = $booking->charges()
        ->where('type', \App\Models\Charge::TYPE_LATE_CHECKOUT)
        ->where('status', \App\Models\Charge::STATUS_SUCCESS)
        ->exists();
    $showLateCheckoutCharge = $booking->pay_by_cc
        && $lateCheckoutAmountCents > 0
        && ! $lateCheckoutPaid
        && $stripeConfiguredForCharges;
@endphp

@if($showLateCheckoutCharge)
    <x-guest-charge-card
        type="late_checkout"
        label="Pay late checkout fee"
        description="Late checkout fee: ${{ number_format($lateCheckoutAmountCents / 100, 2) }}. Processing fee: ${{ number_format($lateCheckoutProcessingFeeCents / 100, 2) }}."
        :amount-cents="$lateCheckoutTotalCents"
        :booking="$booking"
    />
    @include('guest.partials.charge-card-script')
    <script>
    (function() {
        initGuestChargeCard("late_checkout");
    })();
    </script>
@endif
