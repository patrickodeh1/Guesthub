<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Deliberately separate from deposit_verified_at, which stays
            // exactly as-is (manual admin action, untouched by this work).
            // This tracks the actual Stripe PaymentIntent hold lifecycle for
            // new, Stripe-enabled bookings only.
            //
            // Values: null (no hold), 'held' (authorized, not captured),
            // 'partially_captured', 'captured', 'released', 'failed'.
            $table->string('deposit_payment_status')->nullable()->after('contract_accepted_at');
            $table->string('deposit_stripe_payment_intent_id')->nullable()->after('deposit_payment_status');
            $table->unsignedInteger('deposit_amount_cents')->nullable()->after('deposit_stripe_payment_intent_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['deposit_payment_status', 'deposit_stripe_payment_intent_id', 'deposit_amount_cents']);
        });
    }
};
