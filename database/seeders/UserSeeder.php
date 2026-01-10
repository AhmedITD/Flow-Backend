<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate(
            ['phone_number' => '9647716418740'],
            [
                'name' => 'Admin User',
                'phone_number' => '9647716418740',
                'password' => Hash::make('password'),
                'phone_verified_at' => now(),
            ]
        );

        // Create demo user
        User::updateOrCreate(
            ['phone_number' => '9647501234567'],
            [
                'name' => 'Demo User',
                'phone_number' => '9647501234567',
                'password' => Hash::make('password'),
                'phone_verified_at' => now(),
            ]
        );

        // Create additional test users
        User::factory(5)->create();

        $this->command->info('Users seeded successfully.');
    }
}

