<?php

namespace Database\Seeders;

use App\Models\Pricing;
use App\Models\ServiceAccount;
use App\Models\UsageRecord;
use Illuminate\Database\Seeder;

class UsageRecordSeeder extends Seeder
{
    /**
     * Seed sample usage records.
     */
    public function run(): void
    {
        $this->command->info('Seeding usage records...');

        // Get admin service account (phone numbers stored without + prefix)
        $adminAccount = ServiceAccount::whereHas('user', function ($q) {
            $q->where('phone_number', '9647716418740');
        })->first();

        if (!$adminAccount) {
            $this->command->warn('  Admin service account not found. Skipping usage seeding.');
            return;
        }

        // Get pricing
        $callCenterPricing = Pricing::getCurrentPricing('call_center');
        $hrPricing = Pricing::getCurrentPricing('hr');

        // Create sample usage records for the past 7 days
        $usageData = [
            // Day 1
            ['service_type' => 'call_center', 'tokens' => 1500, 'action' => 'chat', 'days_ago' => 7],
            ['service_type' => 'call_center', 'tokens' => 2200, 'action' => 'chat_with_tools', 'days_ago' => 7],
            // Day 2
            ['service_type' => 'call_center', 'tokens' => 800, 'action' => 'chat', 'days_ago' => 6],
            ['service_type' => 'hr', 'tokens' => 1200, 'action' => 'chat', 'days_ago' => 6],
            // Day 3
            ['service_type' => 'call_center', 'tokens' => 3500, 'action' => 'chat_with_tools', 'days_ago' => 5],
            // Day 4
            ['service_type' => 'hr', 'tokens' => 2000, 'action' => 'chat', 'days_ago' => 4],
            ['service_type' => 'call_center', 'tokens' => 1800, 'action' => 'chat', 'days_ago' => 4],
            // Day 5
            ['service_type' => 'call_center', 'tokens' => 2500, 'action' => 'chat_with_tools', 'days_ago' => 3],
            ['service_type' => 'hr', 'tokens' => 900, 'action' => 'chat', 'days_ago' => 3],
            // Day 6
            ['service_type' => 'call_center', 'tokens' => 1200, 'action' => 'chat', 'days_ago' => 2],
            // Today
            ['service_type' => 'call_center', 'tokens' => 600, 'action' => 'chat', 'days_ago' => 0],
        ];

        $totalCost = 0;

        foreach ($usageData as $data) {
            $pricing = $data['service_type'] === 'call_center' ? $callCenterPricing : $hrPricing;
            $cost = $pricing ? $pricing->calculateCost($data['tokens']) : 0;
            $totalCost += $cost;

            UsageRecord::create([
                'service_account_id' => $adminAccount->id,
                'service_type' => $data['service_type'],
                'tokens_used' => $data['tokens'],
                'cost' => $cost,
                'action_type' => $data['action'],
                'metadata' => [
                    'seeded' => true,
                ],
                'recorded_at' => now()->subDays($data['days_ago'])->subHours(rand(1, 12)),
            ]);
        }

        // Update admin account balance to reflect usage
        $adminAccount->balance = max(0, $adminAccount->balance - $totalCost);
        $adminAccount->save();

        $this->command->info("  Created " . count($usageData) . " usage records");
        $this->command->info("  Total usage cost: {$totalCost} IQD");
    }
}
