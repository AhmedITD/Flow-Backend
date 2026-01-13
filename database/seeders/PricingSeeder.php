<?php

namespace Database\Seeders;

use App\Models\Pricing;
use App\Models\PricingTier;
use Illuminate\Database\Seeder;

class PricingSeeder extends Seeder
{
    /**
     * Seed pricing and volume discount tiers.
     */
    public function run(): void
    {
        $this->command->info('Seeding pricing...');

        // Call Center pricing
        Pricing::create([
            'service_type' => 'call_center',
            'price_per_1k_tokens' => 50.00, // 50 IQD per 1000 tokens
            'min_tokens' => 100, // Minimum 100 tokens per request
            'currency' => 'IQD',
            'effective_from' => now(),
            'effective_until' => null,
        ]);

        // HR pricing
        Pricing::create([
            'service_type' => 'hr',
            'price_per_1k_tokens' => 75.00, // 75 IQD per 1000 tokens
            'min_tokens' => 100,
            'currency' => 'IQD',
            'effective_from' => now(),
            'effective_until' => null,
        ]);

        $this->command->info('  Created pricing for call_center and hr services');

        // Volume discount tiers for Call Center
        $callCenterTiers = [
            ['min_tokens' => 100000, 'discount_percent' => 5.00],    // 5% off after 100k tokens
            ['min_tokens' => 500000, 'discount_percent' => 10.00],   // 10% off after 500k tokens
            ['min_tokens' => 1000000, 'discount_percent' => 15.00],  // 15% off after 1M tokens
            ['min_tokens' => 5000000, 'discount_percent' => 20.00],  // 20% off after 5M tokens
        ];

        foreach ($callCenterTiers as $tier) {
            PricingTier::create([
                'service_type' => 'call_center',
                'min_tokens' => $tier['min_tokens'],
                'discount_percent' => $tier['discount_percent'],
                'price_per_1k_tokens' => null, // Use discount
            ]);
        }

        // Volume discount tiers for HR
        $hrTiers = [
            ['min_tokens' => 100000, 'discount_percent' => 5.00],
            ['min_tokens' => 500000, 'discount_percent' => 10.00],
            ['min_tokens' => 1000000, 'discount_percent' => 15.00],
        ];

        foreach ($hrTiers as $tier) {
            PricingTier::create([
                'service_type' => 'hr',
                'min_tokens' => $tier['min_tokens'],
                'discount_percent' => $tier['discount_percent'],
                'price_per_1k_tokens' => null,
            ]);
        }

        $this->command->info('  Created volume discount tiers');
    }
}
