<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            ['name' => 'Manage Students', 'slug' => 'manage_students', 'description' => 'Can view and manage assigned students'],
            ['name' => 'View Student Applications', 'slug' => 'view_student_applications', 'description' => 'Can view applications of assigned students'],
            ['name' => 'Manage Own Profile', 'slug' => 'manage_own_profile', 'description' => 'Can update own student profile'],
            ['name' => 'Manage Own Applications', 'slug' => 'manage_own_applications', 'description' => 'Can view and manage own applications'],
        ];

        foreach ($permissions as $permission) {
            \Illuminate\Support\Facades\DB::table('permissions')->updateOrInsert(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('permissions')
            ->whereIn('slug', ['manage_students', 'view_student_applications', 'manage_own_profile', 'manage_own_applications'])
            ->delete();
    }
};
