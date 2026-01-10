<?php

namespace Database\Seeders;

use App\Enums\ServiceType;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionService;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::where('phone_number', '9647716418740')->first();
        $demoUser = User::where('phone_number', '9647501234567')->first();
        
        $proPlan = Plan::where('slug', 'pro')->first();
        $basicPlan = Plan::where('slug', 'basic')->first();

        if (!$proPlan || !$basicPlan) {
            $this->command->warn('Plans not found. Run PlanSeeder first.');
            return;
        }

        // Admin gets Pro plan (active)
        if ($adminUser) {
            $adminSubscription = Subscription::updateOrCreate(
                ['user_id' => $adminUser->id, 'plan_id' => $proPlan->id],
                [
                    'status' => 'active',
                    'starts_at' => now()->subMonth(),
                    'ends_at' => now()->addYear(),
                    'auto_renew' => true,
                ]
            );

            // Allocate all service types
            foreach (ServiceType::cases() as $serviceType) {
                SubscriptionService::updateOrCreate(
                    [
                        'subscription_id' => $adminSubscription->id,
                        'service_type' => $serviceType,
                    ],
                    [
                        'allocated_tokens' => $proPlan->limits['tokens_per_month'] ?? 200000,
                        'tokens_used' => 0,
                        'reset_at' => now()->addMonth()->startOfMonth(),
                    ]
                );
            }
        }

        // Demo gets Basic plan (trial)
        if ($demoUser) {
            $demoSubscription = Subscription::updateOrCreate(
                ['user_id' => $demoUser->id, 'plan_id' => $basicPlan->id],
                [
                    'status' => 'trial',
                    'starts_at' => now(),
                    'trial_ends_at' => now()->addDays(14),
                    'auto_renew' => true,
                ]
            );

            // Allocate all service types
            foreach (ServiceType::cases() as $serviceType) {
                SubscriptionService::updateOrCreate(
                    [
                        'subscription_id' => $demoSubscription->id,
                        'service_type' => $serviceType,
                    ],
                    [
                        'allocated_tokens' => $basicPlan->limits['tokens_per_month'] ?? 50000,
                        'tokens_used' => 1500, // Some usage
                        'reset_at' => now()->addMonth()->startOfMonth(),
                    ]
                );
            }
        }

        // Create some random subscriptions for other users
        $otherUsers = User::whereNotIn('phone_number', ['9647716418740', '9647501234567'])->get();
        $plans = Plan::where('is_active', true)->get();

        foreach ($otherUsers as $user) {
            $plan = $plans->random();
            
            $subscription = Subscription::factory()
                ->for($user)
                ->for($plan)
                ->active()
                ->create();

            // Allocate all service types
            foreach (ServiceType::cases() as $serviceType) {
                SubscriptionService::factory()
                    ->for($subscription)
                    ->create([
                        'service_type' => $serviceType,
                        'allocated_tokens' => $plan->limits['tokens_per_month'] ?? 10000,
                    ]);
            }
        }

        $this->command->info('Subscriptions seeded successfully.');
    }
}
