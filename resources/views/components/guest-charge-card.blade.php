@props(['type', 'label', 'description', 'amountCents', 'booking'])

<div class="guest-portal-card mt-4" data-charge-card
     data-intent-url="{{ route('guest.charge.intent', [$booking->booking_id, $booking->token]) }}"
     data-confirm-url="{{ route('guest.charge.confirm', [$booking->booking_id, $booking->token]) }}">
    <div class="p-6 md:p-8 text-center">
        <p class="text-base font-bold text-slate-950">{{ $label }}</p>
        <p class="mt-2 text-sm leading-6 text-slate-600">{{ $description }} Total due: <strong>${{ number_format($amountCents / 100, 2) }}</strong>.</p>
        <div id="{{ $type }}-payment-error" class="mt-3 hidden rounded-lg bg-red-50 p-3 text-sm text-red-700"></div>
        <div id="{{ $type }}-payment-element" class="mt-4 rounded-xl border border-slate-200 p-4 text-left"></div>
        <button type="button" id="{{ $type }}-pay-btn" class="guest-primary-btn mt-4 w-full" disabled>Pay ${{ number_format($amountCents / 100, 2) }}</button>
    </div>
</div>
