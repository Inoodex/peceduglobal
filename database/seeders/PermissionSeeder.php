<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the seed command.
     */
    public function run(): void
    {
        $permissions = [
            // Content
            ['name' => 'Manage Pages', 'slug' => 'manage_pages', 'description' => 'Can manage pages, blocks, elements, and hero sliders'],
            ['name' => 'Manage Countries', 'slug' => 'manage_countries', 'description' => 'Can manage countries'],
            ['name' => 'Manage Team Members', 'slug' => 'manage_team_members', 'description' => 'Can manage team members and staff'],
            ['name' => 'Manage Education', 'slug' => 'manage_education', 'description' => 'Can manage universities, courses, intakes and levels'],

            // Consultancy
            ['name' => 'Manage Chat', 'slug' => 'manage_chat', 'description' => 'Can access and manage chat inbox'],
            ['name' => 'Manage Students', 'slug' => 'manage_students', 'description' => 'Can manage student records and profiles'],
            ['name' => 'Edit Student Info', 'slug' => 'edit_student', 'description' => 'Can update student profiles'],
            ['name' => 'Manage Inquiries', 'slug' => 'manage_inquiries', 'description' => 'Can manage student inquiries'],
            ['name' => 'Manage Bookings', 'slug' => 'manage_bookings', 'description' => 'Can manage appointment bookings'],
            ['name' => 'View Applications', 'slug' => 'view_applications', 'description' => 'Can view student applications'],

            // Management
            ['name' => 'Manage Users', 'slug' => 'manage_users', 'description' => 'Can create, edit and delete users'],
            ['name' => 'View Activity Log', 'slug' => 'view_activity_log', 'description' => 'Can view user activity logs'],
            ['name' => 'Manage Settings', 'slug' => 'manage_settings', 'description' => 'Can manage site settings'],
            ['name' => 'Manage Footer', 'slug' => 'manage_footer', 'description' => 'Can manage website footer info and social links'],

            // Blog
            ['name' => 'Manage Blogs', 'slug' => 'manage_blogs', 'description' => 'Can manage blog posts and categories'],
        ];

        foreach ($permissions as $perm) {
            \App\Models\Permission::updateOrCreate(
                ['slug' => $perm['slug']],
                ['name' => $perm['name'], 'description' => $perm['description']]
            );
        }
    }
}
