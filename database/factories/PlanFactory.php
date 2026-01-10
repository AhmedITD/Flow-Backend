<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Basic', 'Pro', 'Enterprise', 'Starter', 'Business']);
        
        return [
            'id' => (string) Str::uuid(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'price' => fake()->randomElement([9900, 29900, 99900, 199900]), // In IQD
            'currency' => 'IQD',
            'billing_period' => fake()->randomElement(['monthly', 'yearly']),
            'trial_days' => fake()->randomElement([0, 7, 14, 30]),
            'is_active' => true,
            'features' => [
                'ai_chat' => true,
                'tool_calling' => true,
                'priority_support' => fake()->boolean(),
            ],
            'limits' => [
                'tokens_per_month' => fake()->randomElement([10000, 50000, 200000, 1000000]),
                'api_keys' => fake()->randomElement([1, 5, 10, 50]),
            ],
        ];
    }

    public function basic(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Basic',
            'slug' => 'basic',
            'price' => 9900,
            'trial_days' => 7,
            'limits' => [
                'tokens_per_month' => 10000,
                'api_keys' => 1,
            ],
        ]);
    }

    public function pro(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Pro',
            'slug' => 'pro',
            'price' => 29900,
            'trial_days' => 14,
            'limits' => [
                'tokens_per_month' => 100000,
                'api_keys' => 5,
            ],
        ]);
    }

    public function enterprise(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'price' => 99900,
            'trial_days' => 30,
            'limits' => [
                'tokens_per_month' => 1000000,
                'api_keys' => 50,
            ],
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}

