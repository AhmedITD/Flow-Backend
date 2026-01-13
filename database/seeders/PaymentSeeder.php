<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\ServiceAccount;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Seed sample payment records.
     */
    public function run(): void
    {
        $this->command->info('Seeding payments...');

        // Get admin user and service account (phone numbers stored without + prefix)
        $adminUser = User::where('phone_number', '9647716418740')->first();
        $adminAccount = $adminUser ? ServiceAccount::where('user_id', $adminUser->id)->first() : null;

        if (!$adminUser || !$adminAccount) {
            $this->command->warn('  Admin user/account not found. Skipping payment seeding.');
            return;
        }

        // Create sample top-up payments
        $payments = [
            [
                'amount' => 50000.00,
                'status' => 'completed',
                'description' => 'Initial top-up',
                'days_ago' => 30,
            ],
            [
                'amount' => 25000.00,
                'status' => 'completed',
                'description' => 'Balance top-up',
                'days_ago' => 15,
            ],
            [
                'amount' => 25000.00,
                'status' => 'completed',
                'description' => 'Balance top-up',
                'days_ago' => 5,
            ],
            [
                'amount' => 10000.00,
                'status' => 'pending',
                'description' => 'Pending top-up',
                'days_ago' => 0,
            ],
        ];

        foreach ($payments as $data) {
            $createdAt = now()->subDays($data['days_ago']);
            
            Payment::create([
                'user_id' => $adminUser->id,
                'service_account_id' => $adminAccount->id,
                'amount' => $data['amount'],
                'currency' => 'IQD',
                'type' => 'topup',
                'status' => $data['status'],
                'description' => $data['description'],
                'paid_at' => $data['status'] === 'completed' ? $createdAt : null,
                'metadata' => [
                    'seeded' => true,
                ],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $this->command->info("  Created " . count($payments) . " payment records");
    }
}
