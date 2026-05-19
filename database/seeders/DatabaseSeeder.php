<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Essential Permissions
        $permissions = [
            ['name' => 'Manage Users', 'slug' => 'manage_users', 'description' => 'Can create, edit and delete users'],
            ['name' => 'Manage Pages', 'slug' => 'manage_pages', 'description' => 'Can manage website pages'],
            ['name' => 'Manage Blogs', 'slug' => 'manage_blogs', 'description' => 'Can manage blog posts'],
            ['name' => 'Manage Countries', 'slug' => 'manage_countries', 'description' => 'Can manage countries'],
            ['name' => 'Manage Education', 'slug' => 'manage_education', 'description' => 'Can manage universities and courses'],
            ['name' => 'View Applications', 'slug' => 'view_applications', 'description' => 'Can view student applications'],
            ['name' => 'Edit Student Info', 'slug' => 'edit_student', 'description' => 'Can update student profiles'],
            ['name' => 'Manage Settings', 'slug' => 'manage_settings', 'description' => 'Can manage site settings'],
            ['name' => 'Manage Footer', 'slug' => 'manage_footer', 'description' => 'Can manage website footer info and social links'],
        ];

        foreach ($permissions as $perm) {
            \App\Models\Permission::updateOrCreate(
                ['slug' => $perm['slug']],
                ['name' => $perm['name'], 'description' => $perm['description']]
            );
        }

        // 2. Create Admin Account
        $admin = User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'full_name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_verified' => true,
            ]
        );

        // 3. Create a Test Consultant
        $consultant = User::updateOrCreate(
            ['email' => 'consultant@gmail.com'],
            [
                'full_name' => 'Test Consultant',
                'password' => Hash::make('password'),
                'role' => 'consultant',
                'is_verified' => true,
            ]
        );

        // Assign some permissions to Consultant
        $consultantPerms = \App\Models\Permission::whereIn('slug', ['view_applications', 'edit_student'])->pluck('id');
        $consultant->permissions()->sync($consultantPerms);

        // 4. Create a Test Student
        User::updateOrCreate(
            ['email' => 'student@gmail.com'],
            [
                'full_name' => 'Test Student',
                'password' => Hash::make('password'),
                'role' => 'student',
                'is_verified' => true,
            ]
        );
    }
}
