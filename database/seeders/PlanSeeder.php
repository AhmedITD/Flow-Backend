<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Create predefined plans
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Get started with basic features',
                'price' => 0,
                'currency' => 'IQD',
                'billing_period' => 'monthly',
                'trial_days' => 0,
                'is_active' => true,
                'features' => [
                    'ai_chat' => true,
                    'tool_calling' => false,
                    'priority_support' => false,
                ],
                'limits' => [
                    'tokens_per_month' => 1000,
                    'api_keys' => 1,
                ],
            ],
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Perfect for small teams',
                'price' => 9900,
                'currency' => 'IQD',
                'billing_period' => 'monthly',
                'trial_days' => 7,
                'is_active' => true,
                'features' => [
                    'ai_chat' => true,
                    'tool_calling' => true,
                    'priority_support' => false,
                ],
                'limits' => [
                    'tokens_per_month' => 50000,
                    'api_keys' => 3,
                ],
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'For growing businesses',
                'price' => 29900,
                'currency' => 'IQD',
                'billing_period' => 'monthly',
                'trial_days' => 14,
                'is_active' => true,
                'features' => [
                    'ai_chat' => true,
                    'tool_calling' => true,
                    'priority_support' => true,
                ],
                'limits' => [
                    'tokens_per_month' => 200000,
                    'api_keys' => 10,
                ],
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For large organizations with custom needs',
                'price' => 99900,
                'currency' => 'IQD',
                'billing_period' => 'monthly',
                'trial_days' => 30,
                'is_active' => true,
                'features' => [
                    'ai_chat' => true,
                    'tool_calling' => true,
                    'priority_support' => true,
                    'custom_integrations' => true,
                    'dedicated_support' => true,
                ],
                'limits' => [
                    'tokens_per_month' => 1000000,
                    'api_keys' => 50,
                ],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('Plans seeded successfully.');
    }
}

