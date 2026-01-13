<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\ServiceAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'completed', 'failed']);
        
        return [
            'id' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'service_account_id' => ServiceAccount::factory(),
            'transaction_id' => 'TXN_' . strtoupper(Str::random(12)),
            'qicard_payment_id' => 'QI_' . strtoupper(Str::random(16)),
            'amount' => fake()->randomElement([9900, 29900, 99900, 199900]),
            'currency' => 'IQD',
            'type' => 'topup',
            'status' => $status,
            'description' => 'Balance top-up',
            'payment_method' => 'qicard',
            'metadata' => null,
            'qicard_response' => $status === 'completed' ? [
                'status' => 'success',
                'reference' => Str::random(20),
            ] : null,
            'paid_at' => $status === 'completed' ? now() : null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'paid_at' => now(),
            'qicard_response' => [
                'status' => 'success',
                'reference' => Str::random(20),
            ],
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_at' => null,
            'qicard_response' => null,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'paid_at' => null,
            'qicard_response' => [
                'status' => 'failed',
                'error' => 'Insufficient funds',
            ],
        ]);
    }

    public function forServiceAccount(ServiceAccount $account): static
    {
        return $this->state(fn (array $attributes) => [
            'service_account_id' => $account->id,
            'user_id' => $account->user_id,
        ]);
    }
}

