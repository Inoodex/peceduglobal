<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\User;

class BookingPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $p1 = Permission::firstOrCreate(['slug' => 'manage_inquiries'], ['name' => 'Manage Inquiries']);
        $p2 = Permission::firstOrCreate(['slug' => 'manage_bookings'], ['name' => 'Manage Bookings']);

        // Assign to all consultants
        $consultants = User::where('role', 'consultant')->get();
        foreach ($consultants as $consultant) {
            $consultant->permissions()->syncWithoutDetaching([$p1->id, $p2->id]);
        }
        
        $this->command->info('Permissions manage_inquiries and manage_bookings created and assigned to consultants.');
    }
}
