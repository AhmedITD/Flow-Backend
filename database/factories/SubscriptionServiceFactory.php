<?php

namespace Database\Factories;

use App\Enums\ServiceType;
use App\Models\Subscription;
use App\Models\SubscriptionService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SubscriptionServiceFactory extends Factory
{
    protected $model = SubscriptionService::class;

    public function definition(): array
    {
        $allocatedTokens = fake()->randomElement([10000, 50000, 100000, 500000]);
        $tokensUsed = fake()->numberBetween(0, $allocatedTokens);
        
        return [
            'id' => (string) Str::uuid(),
            'subscription_id' => Subscription::factory(),
            'service_type' => fake()->randomElement(ServiceType::cases()),
            'allocated_tokens' => $allocatedTokens,
            'tokens_used' => $tokensUsed,
            'reset_at' => now()->addMonth()->startOfMonth(),
        ];
    }

    public function callCenter(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => ServiceType::CallCenter,
        ]);
    }

    public function hr(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => ServiceType::HR,
        ]);
    }

    public function withUsage(int $tokensUsed): static
    {
        return $this->state(fn (array $attributes) => [
            'tokens_used' => $tokensUsed,
        ]);
    }

    public function unlimited(): static
    {
        return $this->state(fn (array $attributes) => [
            'allocated_tokens' => 0, // 0 means unlimited
            'tokens_used' => fake()->numberBetween(0, 100000),
        ]);
    }

    public function exhausted(): static
    {
        return $this->state(function (array $attributes) {
            $allocated = $attributes['allocated_tokens'] ?? 10000;
            return [
                'tokens_used' => $allocated, // Fully used
            ];
        });
    }
}
