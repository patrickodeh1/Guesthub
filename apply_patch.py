import re, sys

def edit(path, replacements, allow_missing=False):
    with open(path) as f:
        content = f.read()
    for old, new, expected_count in replacements:
        count = content.count(old)
        if count == 0 and not allow_missing:
            print(f"FAIL {path}: anchor not found:\n{old[:200]}")
            sys.exit(1)
        if expected_count is not None and count != expected_count:
            print(f"WARN {path}: expected {expected_count} occurrences, found {count} for:\n{old[:120]}")
        content = content.replace(old, new)
    with open(path, "w") as f:
        f.write(content)
    print(f"OK {path}")

# 1. GuestController.php -----------------------------------------------
edit("app/Http/Controllers/GuestController.php", [
    (
        "        $result = $service->createPendingIntent(\n"
        "            $booking,\n"
        "            \\App\\Models\\Charge::TYPE_DEPOSIT,\n"
        "            $amountCents,\n"
        "            'precheckin_approval',\n"
        "            $booking->preCheckinChargeBreakdown()\n"
        "        );\n",

        "        // Avoid creating a duplicate PaymentIntent on page reload/refresh —\n"
        "        // reuse the existing pending one if there is one, re-fetching its\n"
        "        // client_secret from Stripe so the guest can still complete payment.\n"
        "        $existing = $booking->charges()->where('type', \\App\\Models\\Charge::TYPE_DEPOSIT)->where('status', \\App\\Models\\Charge::STATUS_PENDING)->latest()->first();\n"
        "        if ($existing) {\n"
        "            return response()->json([\n"
        "                'ok' => true,\n"
        "                'client_secret' => $service->retrieveClientSecret($existing->stripe_payment_intent_id),\n"
        "                'publishable_key' => config('services.stripe.key'),\n"
        "                'amount_cents' => $existing->amount_cents,\n"
        "            ]);\n"
        "        }\n\n"
        "        $result = $service->createPendingIntent(\n"
        "            $booking,\n"
        "            \\App\\Models\\Charge::TYPE_DEPOSIT,\n"
        "            $amountCents,\n"
        "            'precheckin_approval',\n"
        "            $booking->preCheckinChargeBreakdown()\n"
        "        );\n",
        1,
    ),
    ("\\App\\Models\\Charge::STATUS_CAPTURED", "\\App\\Models\\Charge::STATUS_SUCCESS", 2),
])

# 2. PaymentService.php --------------------------------------------------
edit("app/Services/Payments/PaymentService.php", [
    (
        "        return $this->client ??= new StripeClient(config('services.stripe.secret'));\n    }\n",
        "        return $this->client ??= new StripeClient(config('services.stripe.secret'));\n    }\n\n"
        "    /**\n"
        "     * Re-fetches a PaymentIntent's client_secret from Stripe. Used when we\n"
        "     * reuse an existing pending intent (e.g. on guest page reload) instead\n"
        "     * of creating a new one.\n"
        "     */\n"
        "    public function retrieveClientSecret(string $paymentIntentId): ?string\n"
        "    {\n"
        "        return $this->client()->paymentIntents->retrieve($paymentIntentId)->client_secret;\n"
        "    }\n",
        1,
    ),
    ("Charge::STATUS_CAPTURED", "Charge::STATUS_SUCCESS", 5),
])

# 3. Charge.php -----------------------------------------------------------
edit("app/Models/Charge.php", [
    ("public const STATUS_CAPTURED = 'captured';", "public const STATUS_SUCCESS = 'success';", 1),
])

# 4. Booking.php ------------------------------------------------------------
edit("app/Models/Booking.php", [
    ("$this->deposit_payment_status === 'captured';", "$this->deposit_payment_status === 'success';", 1),
])

# 5. Admin/PaymentController.php --------------------------------------------
edit("app/Http/Controllers/Admin/PaymentController.php", [
    ("'captured_cents' => Charge::where('status', Charge::STATUS_CAPTURED)->sum('amount_cents'),",
     "'success_cents' => Charge::where('status', Charge::STATUS_SUCCESS)->sum('amount_cents'),", 1),
    ("Charge::STATUS_CAPTURED => 'Captured',", "Charge::STATUS_SUCCESS => 'Success',", 1),
])

# 6. resources/views/guest/show.blade.php ------------------------------------
edit("resources/views/guest/show.blade.php", [
    ("\\App\\Models\\Charge::STATUS_CAPTURED", "\\App\\Models\\Charge::STATUS_SUCCESS", 4),
])

# 7. resources/views/admin/payments/index.blade.php --------------------------
edit("resources/views/admin/payments/index.blade.php", [
    ("'captured' => 'badge-active',", "'success' => 'badge-active',", 1),
])

# 8. Stats strip card (captured_cents -> success_cents, label) --------------
edit("resources/views/admin/payments/index.blade.php", [
    ("$totals['captured_cents']", "$totals['success_cents']", 1),
    (">Captured</p>", ">Success</p>", 1),
])

print("ALL DONE")
