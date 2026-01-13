<?php

namespace Database\Factories;

use App\Models\PricingTier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PricingTierFactory extends Factory
{
    protected $model = PricingTier::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'service_type' => fake()->randomElement(['call_center', 'hr']),
            'min_tokens' => fake()->numberBetween(100000, 1000000),
            'discount_percent' => fake()->randomFloat(2, 5, 25),
            'price_per_1k_tokens' => null,
        ];
    }
}
