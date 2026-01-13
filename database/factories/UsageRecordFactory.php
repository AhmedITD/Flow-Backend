<?php

namespace Database\Factories;

use App\Models\ServiceAccount;
use App\Models\UsageRecord;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UsageRecordFactory extends Factory
{
    protected $model = UsageRecord::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'service_account_id' => ServiceAccount::factory(),
            'service_type' => fake()->randomElement(['call_center', 'hr']),
            'tokens_used' => fake()->numberBetween(50, 2000),
            'cost' => fake()->randomFloat(4, 1, 100),
            'action_type' => fake()->randomElement(['chat', 'chat_with_tools', 'tool_call']),
            'resource_id' => null,
            'metadata' => [
                'tool_calls' => fake()->numberBetween(0, 5),
            ],
            'recorded_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function callCenter(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => 'call_center',
        ]);
    }

    public function hr(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => 'hr',
        ]);
    }

    public function chat(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => 'chat',
            'tokens_used' => fake()->numberBetween(100, 500),
            'metadata' => ['tool_calls' => 0],
        ]);
    }

    public function chatWithTools(): static
    {
        return $this->state(fn (array $attributes) => [
            'action_type' => 'chat_with_tools',
            'tokens_used' => fake()->numberBetween(500, 2000),
            'metadata' => ['tool_calls' => fake()->numberBetween(1, 5)],
        ]);
    }

    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'recorded_at' => now(),
        ]);
    }
}
