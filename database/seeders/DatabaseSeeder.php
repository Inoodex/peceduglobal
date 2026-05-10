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
        // Create Admin Account
        User::create([
            'full_name' => 'Super Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_verified' => true,
        ]);

        // Create a Test Counselor
        User::create([
            'full_name' => 'Test Counselor',
            'email' => 'counselor@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'counselor',
            'is_verified' => true,
            'permissions' => ['pages.view', 'blogs.manage'] // Example permissions
        ]);

        // Create a Test Student
        User::create([
            'full_name' => 'Test Student',
            'email' => 'student@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'is_verified' => true,
        ]);
    }
}
