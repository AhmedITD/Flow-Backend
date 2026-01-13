<?php

namespace Database\Factories;

use App\Models\Pricing;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PricingFactory extends Factory
{
    protected $model = Pricing::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'service_type' => fake()->randomElement(['call_center', 'hr']),
            'price_per_1k_tokens' => fake()->randomFloat(4, 10, 100),
            'min_tokens' => 1,
            'currency' => 'IQD',
            'effective_from' => now()->subDays(30),
            'effective_until' => null,
        ];
    }

    public function callCenter(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => 'call_center',
            'price_per_1k_tokens' => 50.00,
        ]);
    }

    public function hr(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => 'hr',
            'price_per_1k_tokens' => 75.00,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'effective_until' => null,
        ]);
    }
}
