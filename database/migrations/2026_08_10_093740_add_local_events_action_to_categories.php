<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE categories MODIFY action ENUM('content', 'door_lock', 'local_events') NOT NULL DEFAULT 'content'");
        }

        DB::table('categories')->updateOrInsert(
            ['slug' => 'local-events'],
            [
                'title' => 'Local Events',
                'icon' => 'Local Events',
                'action' => 'local_events',
                'sort_order' => 999,
                'active' => true,
                'is_global' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('categories')->where('slug', 'local-events')->delete();

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE categories MODIFY action ENUM('content', 'door_lock') NOT NULL DEFAULT 'content'");
        }
    }
};
