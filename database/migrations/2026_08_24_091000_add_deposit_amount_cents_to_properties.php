<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Nullable — falls back to the global default_deposit_amount_cents
            // setting when unset. Existing properties are unaffected until
            // an admin sets one explicitly.
            $table->unsignedInteger('deposit_amount_cents')->nullable()->after('channex_property_id');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('deposit_amount_cents');
        });
    }
};
