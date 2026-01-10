<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('-3 months', 'now');
        $status = fake()->randomElement(['active', 'trial', 'cancelled', 'expired']);
        
        return [
            'id' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'status' => $status,
            'starts_at' => $startsAt,
            'ends_at' => $status === 'active' ? fake()->dateTimeBetween('now', '+1 year') : null,
            'trial_ends_at' => $status === 'trial' ? fake()->dateTimeBetween('now', '+30 days') : null,
            'cancelled_at' => $status === 'cancelled' ? fake()->dateTimeBetween($startsAt, 'now') : null,
            'cancel_reason' => $status === 'cancelled' ? fake()->sentence() : null,
            'auto_renew' => fake()->boolean(80),
            'metadata' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addYear(),
            'trial_ends_at' => null,
            'cancelled_at' => null,
            'cancel_reason' => null,
            'auto_renew' => true,
        ]);
    }

    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'trial',
            'starts_at' => now(),
            'ends_at' => null,
            'trial_ends_at' => now()->addDays(14),
            'cancelled_at' => null,
            'cancel_reason' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_reason' => fake()->sentence(),
            'auto_renew' => false,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'starts_at' => now()->subYear(),
            'ends_at' => now()->subMonth(),
            'auto_renew' => false,
        ]);
    }
}

