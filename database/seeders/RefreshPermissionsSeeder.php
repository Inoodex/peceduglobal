<?php

namespace Database\Seeders;

use App\Models\User;
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

        // Re-seed fresh 15 permissions
        $this->call(PermissionSeeder::class);

        // Re-assign default permissions to all consultant users
        $consultantSlugs = ['manage_chat', 'manage_students', 'manage_applications', 'manage_inquiries', 'edit_student'];
        $consultantPermIds = \App\Models\Permission::whereIn('slug', $consultantSlugs)->pluck('id');

        User::where('role', 'consultant')->each(function ($user) use ($consultantPermIds) {
            $user->permissions()->sync($consultantPermIds);
        });

        // Re-assign default permissions to all editor users (content management)
        $editorSlugs = ['manage_pages', 'manage_countries', 'manage_team_members', 'manage_education', 'manage_blogs'];
        $editorPermIds = \App\Models\Permission::whereIn('slug', $editorSlugs)->pluck('id');

        User::where('role', 'editor')->each(function ($user) use ($editorPermIds) {
            $user->permissions()->sync($editorPermIds);
        });

        $this->command->info('Permissions refreshed successfully. Default permissions re-assigned to all consultants and editors.');
    }
}
