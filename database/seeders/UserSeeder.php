<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@gtautomotives.com',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'phone' => '+1-555-0123',
            'address' => '123 Main St, City, State 12345',
            'is_active' => true,
        ]);

        // Create manager user
        User::create([
            'name' => 'Manager User',
            'email' => 'manager@gtautomotives.com',
            'username' => 'manager',
            'password' => Hash::make('manager123'),
            'role' => 'manager',
            'phone' => '+1-555-0124',
            'address' => '456 Oak Ave, City, State 12345',
            'is_active' => true,
        ]);

        // Create staff user
        User::create([
            'name' => 'Staff User',
            'email' => 'staff@gtautomotives.com',
            'username' => 'staff',
            'password' => Hash::make('staff123'),
            'role' => 'staff',
            'phone' => '+1-555-0125',
            'address' => '789 Pine Rd, City, State 12345',
            'is_active' => true,
        ]);
    }
} 