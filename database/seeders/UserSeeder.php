<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user (ONLY admins come from seeder)
        User::updateOrCreate(
            ['phone_number' => '9647716418740'],
            [
                'name' => 'Admin User',
                'phone_number' => '9647716418740',
                'password' => Hash::make('password'),
                'phone_verified_at' => now(),
                'role' => UserRole::ADMIN,
            ]
        );

        // Create demo user (regular user)
        User::updateOrCreate(
            ['phone_number' => '9647501234567'],
            [
                'name' => 'Demo User',
                'phone_number' => '9647501234567',
                'password' => Hash::make('password'),
                'phone_verified_at' => now(),
                'role' => UserRole::USER,
            ]
        );

        // Create additional test users (all regular users)
        User::factory(5)->create(['role' => UserRole::USER]);

        $this->command->info('Users seeded successfully.');
    }
}

