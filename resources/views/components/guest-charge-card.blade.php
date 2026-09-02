@props(['type', 'label', 'description', 'amountCents', 'booking'])

<div class="guest-portal-card mt-4" data-charge-card
     data-intent-url="{{ route('guest.charge.intent', [$booking->booking_id, $booking->token]) }}"
     data-confirm-url="{{ route('guest.charge.confirm', [$booking->booking_id, $booking->token]) }}">
    <div class="p-6 md:p-8 text-center">
        <p class="text-base font-bold text-slate-950">{{ $label }}</p>
        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $description }} Total due: <strong>${{ number_format($amountCents / 100, 2) }}</strong>.</p>
        <div id="{{ $type }}-payment-error" class="mt-3 hidden rounded-lg bg-red-50 p-3 text-sm text-red-700"></div>
        <div class="guest-card-wizard mt-4 text-left" id="{{ $type }}-card-wizard">
            <div class="guest-card-summary-list" id="{{ $type }}-summary-list"></div>
            <div class="guest-card-step" data-step="number" id="{{ $type }}-step-number">
                <label class="guest-card-label" for="{{ $type }}-payment-card-number">Card number</label>
                <div id="{{ $type }}-payment-card-number" class="guest-card-field"></div>
            </div>
            <div class="guest-card-step hidden" data-step="expiry" id="{{ $type }}-step-expiry">
                <label class="guest-card-label" for="{{ $type }}-payment-card-expiry">Expiry date</label>
                <div id="{{ $type }}-payment-card-expiry" class="guest-card-field"></div>
            </div>
            <div class="guest-card-step hidden" data-step="cvc" id="{{ $type }}-step-cvc">
                <label class="guest-card-label" for="{{ $type }}-payment-card-cvc">Security code (CVC)</label>
                <div id="{{ $type }}-payment-card-cvc" class="guest-card-field"></div>
            </div>
            <div class="guest-card-step hidden" data-step="postal" id="{{ $type }}-step-postal">
                <label class="guest-card-label" for="{{ $type }}-payment-card-postal">Billing ZIP code</label>
                <input id="{{ $type }}-payment-card-postal" type="text" inputmode="numeric" maxlength="10" placeholder="Billing ZIP" class="guest-card-postal" aria-label="Billing ZIP code">
            </div>
        </div>
        <button type="button" id="{{ $type }}-pay-btn" class="guest-primary-btn mt-4 w-full hidden" disabled>Pay ${{ number_format($amountCents / 100, 2) }}</button>
    </div>
</div>
