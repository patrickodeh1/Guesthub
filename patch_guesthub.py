#!/usr/bin/env python3
"""
Patches Guesthub with:
  1. Processing fee applied to ALL payments (grouped deposit AND standalone
     parking/early_checkin/late_checkout/incidentals), fee = subtotal * fee%.
  2. A confirm() prompt on "Mark Deposit Verified" telling the admin to
     verify the guest has paid incidentals + parking + early check-in
     outside the platform before proceeding.

Run from your repo root:
    python3 patch_guesthub.py
"""
import re
import sys
from pathlib import Path

REPO = Path.cwd()

def edit(path: str, old: str, new: str, label: str):
    p = REPO / path
    if not p.exists():
        sys.exit(f"[FAIL] {path} not found. Run this from the Guesthub repo root.")
    text = p.read_text()
    if new in text:
        print(f"[SKIP] {label} — already applied.")
        return
    count = text.count(old)
    if count != 1:
        sys.exit(f"[FAIL] {label} — expected 1 match in {path}, found {count}. "
                  f"File may have changed since this script was generated.")
    p.write_text(text.replace(old, new, 1))
    print(f"[OK]   {label}")


# ---------------------------------------------------------------------------
# 1. app/Models/Booking.php — extract a reusable fee helper, use it in the
#    combined pre-checkin charge too (no behavior change there).
# ---------------------------------------------------------------------------
edit(
    "app/Models/Booking.php",
    old="""        $feePercent = (float) \\App\\Models\\Setting::getValue('processing_fee_percent', 0);
        $fee = (int) round($subtotal * ($feePercent / 100));

        return $subtotal + $fee;
    }""",
    new="""        return $this->applyProcessingFeeCents($subtotal);
    }

    /**
     * Adds the global % processing fee on top of a subtotal, in cents.
     * Shared by the combined pre-checkin charge and every standalone
     * charge type (parking, early check-in, late checkout, incidentals)
     * so the fee is consistently the same % of whatever is actually being
     * charged in that request, grouped or individual.
     */
    public function applyProcessingFeeCents(int $subtotalCents): int
    {
        $feePercent = (float) \\App\\Models\\Setting::getValue('processing_fee_percent', 0);
        $fee = (int) round($subtotalCents * ($feePercent / 100));

        return $subtotalCents + $fee;
    }""",
    label="Booking.php — add applyProcessingFeeCents() helper",
)

# ---------------------------------------------------------------------------
# 2. app/Http/Controllers/GuestController.php — apply the fee to standalone
#    charge types (parking, early_checkin, late_checkout, incidentals).
# ---------------------------------------------------------------------------
edit(
    "app/Http/Controllers/GuestController.php",
    old="""        $amountCents = match ($type) {
            \\App\\Models\\Charge::TYPE_PARKING => (int) round(($booking->effectiveParkingCharge() ?? 0) * 100),
            \\App\\Models\\Charge::TYPE_EARLY_CHECKIN => (int) round(($booking->earlyCheckinCharge() ?? 0) * 100),
            \\App\\Models\\Charge::TYPE_LATE_CHECKOUT => (int) round(($booking->lateCheckoutCharge() ?? 0) * 100),
            \\App\\Models\\Charge::TYPE_INCIDENTALS => $booking->unbilledIncidentalsCents(),
            default => 0,
        };

        if ($amountCents <= 0) {
            return response()->json(['ok' => false, 'error' => 'Nothing is currently due for this.'], 422);
        }""",
    new="""        $subtotalCents = match ($type) {
            \\App\\Models\\Charge::TYPE_PARKING => (int) round(($booking->effectiveParkingCharge() ?? 0) * 100),
            \\App\\Models\\Charge::TYPE_EARLY_CHECKIN => (int) round(($booking->earlyCheckinCharge() ?? 0) * 100),
            \\App\\Models\\Charge::TYPE_LATE_CHECKOUT => (int) round(($booking->lateCheckoutCharge() ?? 0) * 100),
            \\App\\Models\\Charge::TYPE_INCIDENTALS => $booking->unbilledIncidentalsCents(),
            default => 0,
        };

        if ($subtotalCents <= 0) {
            return response()->json(['ok' => false, 'error' => 'Nothing is currently due for this.'], 422);
        }

        // Same % processing fee as the combined pre-checkin charge, applied
        // to this individual charge's subtotal so the fee is consistent
        // whether the guest pays grouped or one item at a time.
        $amountCents = $booking->applyProcessingFeeCents($subtotalCents);""",
    label="GuestController.php — apply fee to standalone charges",
)

# ---------------------------------------------------------------------------
# 3. resources/views/admin/bookings/show.blade.php — confirm() on Mark
#    Deposit Verified.
# ---------------------------------------------------------------------------
edit(
    "resources/views/admin/bookings/show.blade.php",
    old="""<form method="post" action="{{ route('admin.guests.deposit-verified', $booking) }}">@csrf<button class="btn-secondary w-full gap-2"><x-icon name="lock" class="h-4 w-4" />Mark Deposit Verified</button></form>""",
    new="""<form method="post" action="{{ route('admin.guests.deposit-verified', $booking) }}" onsubmit="return confirm('Mark Deposit Verified?\\n\\nThis will fully approve the guest. Before continuing, verify the guest has actually paid everything owed outside the platform: incidentals, parking, and early check-in (if applicable). This action does not check or record any of those payments itself.')">@csrf<button class="btn-secondary w-full gap-2"><x-icon name="lock" class="h-4 w-4" />Mark Deposit Verified</button></form>""",
    label="show.blade.php — confirm() on Mark Deposit Verified",
)

# ---------------------------------------------------------------------------
# 4. resources/views/guest/show.blade.php — fix misleading "refundable hold"
#    copy (this is an immediate capture, not a hold, and refundability is
#    not something the code actually guarantees), remove the em dash, and
#    show an itemized breakdown (incidentals / parking / early check-in /
#    processing fee / total) instead of a single opaque number.
# ---------------------------------------------------------------------------
edit(
    "resources/views/guest/show.blade.php",
    old="""                        <div class="text-center">
                            <h2 class="text-xl font-extrabold text-slate-950">Incidentals hold</h2>
                            <p class="mt-3 text-sm leading-6 text-slate-600">A refundable hold of <strong>${{ number_format($depositAmountCents / 100, 2) }}</strong> is required before check-in. Enter your card below — this stays on our site, nothing is shared with a third party.</p>
                        </div>""",
    new="""                        <div class="text-center">
                            <h2 class="text-xl font-extrabold text-slate-950">Incidentals payment</h2>
                            <p class="mt-3 text-sm leading-6 text-slate-600">A payment of <strong>${{ number_format($depositAmountCents / 100, 2) }}</strong> is required before check-in. Enter your card below. Payment details stay on our site and are not shared with a third party.</p>
                            @php
                                $capCents = $property->deposit_cap_cents !== null
                                    ? $property->deposit_cap_cents
                                    : (int) \\App\\Models\\Setting::getValue('default_deposit_cap_cents', 0);
                                $feePercent = (float) \\App\\Models\\Setting::getValue('processing_fee_percent', 0);
                                $parkingAmt = $booking->effectiveParkingCharge() ?? 0;
                                $incidentalsAmt = $booking->incidentals_charge ?? 0;
                                $earlyCheckinAmt = $booking->earlyCheckinCharge() ?? 0;
                                $breakdownSubtotalCents = min(
                                    (int) round(($parkingAmt + $incidentalsAmt + $earlyCheckinAmt) * 100),
                                    $capCents > 0 ? $capCents : PHP_INT_MAX
                                );
                                $feeCents = $depositAmountCents - $breakdownSubtotalCents;
                            @endphp
                            <div class="mt-4 rounded-xl border border-slate-200 p-4 text-left text-sm text-slate-700">
                                @if($incidentalsAmt > 0)
                                    <div class="flex justify-between py-1"><span>Incidentals</span><span>${{ number_format($incidentalsAmt, 2) }}</span></div>
                                @endif
                                @if($parkingAmt > 0)
                                    <div class="flex justify-between py-1"><span>Parking</span><span>${{ number_format($parkingAmt, 2) }}</span></div>
                                @endif
                                @if($earlyCheckinAmt > 0)
                                    <div class="flex justify-between py-1"><span>Early check-in</span><span>${{ number_format($earlyCheckinAmt, 2) }}</span></div>
                                @endif
                                @if($feeCents > 0)
                                    <div class="flex justify-between py-1"><span>Processing fee{{ $feePercent > 0 ? ' ('.rtrim(rtrim(number_format($feePercent, 2), '0'), '.').'%)' : '' }}</span><span>${{ number_format($feeCents / 100, 2) }}</span></div>
                                @endif
                                <div class="mt-1 flex justify-between border-t border-slate-200 pt-2 font-semibold text-slate-950"><span>Total</span><span>${{ number_format($depositAmountCents / 100, 2) }}</span></div>
                            </div>
                        </div>""",
    label="show.blade.php — fix misleading deposit copy + itemized breakdown",
)

print("\nDone. Review with: git diff")
