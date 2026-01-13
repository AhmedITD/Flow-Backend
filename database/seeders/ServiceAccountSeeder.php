<?php

namespace Database\Seeders;

use App\Models\ServiceAccount;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServiceAccountSeeder extends Seeder
{
    /**
     * Seed service accounts for users.
     */
    public function run(): void
    {
        $this->command->info('Seeding service accounts...');

        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Check if user already has a service account
            if (!ServiceAccount::where('user_id', $user->id)->exists()) {
                $balance = match ($user->phone_number) {
                    '+9647716418740' => 100000.00, // Admin test account - 100,000 IQD
                    '+9647501234567' => 50000.00,  // Demo account - 50,000 IQD
                    default => 10000.00,           // Other accounts - 10,000 IQD
                };

                ServiceAccount::create([
                    'user_id' => $user->id,
                    'status' => 'active',
                    'balance' => $balance,
                    'currency' => 'IQD',
                    'credit_limit' => 0, // Prepaid only
                    'metadata' => [
                        'initial_balance' => $balance,
                        'seeded_at' => now()->toIso8601String(),
                    ],
                ]);

                $this->command->info("  Created service account for {$user->phone_number} with balance {$balance} IQD");
            }
        }
    }
}
