<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE instruction_steps MODIFY action ENUM('content', 'unlock_door', 'lock_door', 'door_lock') NOT NULL DEFAULT 'content'");
        }

        DB::statement("UPDATE instruction_steps SET action = 'door_lock' WHERE action IN ('unlock_door', 'lock_door')");

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE instruction_steps MODIFY action ENUM('content', 'door_lock') NOT NULL DEFAULT 'content'");
        }
    }
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE instruction_steps MODIFY action ENUM('content', 'unlock_door', 'lock_door') NOT NULL DEFAULT 'content'");
        }
    }
};
