<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE instruction_steps MODIFY action ENUM('content', 'unlock_door', 'lock_door', 'door_lock') NOT NULL DEFAULT 'content'");
        } elseif (DB::getDriverName() === 'sqlite') {
            Schema::table('instruction_steps', function (Blueprint $table) {
                $table->string('action_tmp')->default('content')->after('action');
            });
            DB::statement('UPDATE instruction_steps SET action_tmp = action');
            Schema::table('instruction_steps', function (Blueprint $table) {
                $table->dropColumn('action');
            });
            Schema::table('instruction_steps', function (Blueprint $table) {
                $table->renameColumn('action_tmp', 'action');
            });
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
