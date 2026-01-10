<?php

namespace Database\Seeders;

use App\Enums\ServiceType;
use App\Models\Subscription;
use App\Models\SubscriptionService;
use App\Models\UsageRecord;
use Illuminate\Database\Seeder;

class UsageRecordSeeder extends Seeder
{
    public function run(): void
    {
        $subscriptions = Subscription::whereIn('status', ['active', 'trial'])->get();

        foreach ($subscriptions as $subscription) {
            // Create usage records for each service type
            foreach (ServiceType::cases() as $serviceType) {
                // Get or create subscription service
                $subscriptionService = SubscriptionService::where('subscription_id', $subscription->id)
                    ->where('service_type', $serviceType)
                    ->first();

                if (!$subscriptionService) {
                    continue;
                }

                // Create usage records for the past 30 days
                $totalTokens = 0;
                $recordCount = rand(10, 50);

                for ($i = 0; $i < $recordCount; $i++) {
                    $tokensUsed = rand(100, 1500);
                    $totalTokens += $tokensUsed;

                    UsageRecord::factory()->create([
                        'subscription_id' => $subscription->id,
                        'service_type' => $serviceType,
                        'tokens_used' => $tokensUsed,
                        'action_type' => rand(0, 1) ? 'chat' : 'chat_with_tools',
                        'recorded_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
                    ]);
                }

                // Update subscription service with accumulated usage
                $subscriptionService->update([
                    'tokens_used' => min($totalTokens, $subscriptionService->allocated_tokens ?: PHP_INT_MAX),
                ]);
            }
        }

        $this->command->info('Usage records seeded successfully.');
    }
}
