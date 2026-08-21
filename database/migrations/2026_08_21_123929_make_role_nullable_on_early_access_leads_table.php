<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE early_access_leads MODIFY role VARCHAR(255) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE early_access_leads MODIFY role VARCHAR(255) NOT NULL");
    }
};
