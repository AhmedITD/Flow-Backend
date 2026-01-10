<?php

namespace Database\Factories;

use App\Models\BillingCycle;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BillingCycleFactory extends Factory
{
    protected $model = BillingCycle::class;

    public function definition(): array
    {
        $periodStart = fake()->dateTimeBetween('-3 months', 'now');
        $periodEnd = (clone $periodStart)->modify('+1 month');
        $status = fake()->randomElement(['pending', 'paid', 'failed']);
        
        return [
            'id' => (string) Str::uuid(),
            'subscription_id' => Subscription::factory(),
            'payment_id' => null,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'status' => $status,
            'amount' => fake()->randomElement([9900, 29900, 99900]),
            'currency' => 'IQD',
            'paid_at' => $status === 'paid' ? fake()->dateTimeBetween($periodStart, 'now') : null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => now(),
            'payment_id' => Payment::factory(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_at' => null,
            'payment_id' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'paid_at' => null,
        ]);
    }

    public function currentMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
        ]);
    }
}

