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
        $this->call([
            PermissionSeeder::class,
        ]);

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
        // ...existing code...
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
