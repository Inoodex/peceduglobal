<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RefreshPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks to allow truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Detach all user-permission relationships
        DB::table('permission_user')->truncate();

        // Truncate permissions table
        DB::table('permissions')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Re-seed from PermissionSeeder
        $this->call(PermissionSeeder::class);

        $this->command->info('Permissions refreshed successfully: old permissions removed, fresh 15 permissions seeded.');
    }
}
