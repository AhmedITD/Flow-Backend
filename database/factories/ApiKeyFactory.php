<?php

namespace Database\Factories;

use App\Models\ApiKey;
use App\Models\ServiceAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ApiKeyFactory extends Factory
{
    protected $model = ApiKey::class;

    public function definition(): array
    {
        $plainKey = 'flw_' . Str::random(32);
        
        return [
            'id' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'service_account_id' => ServiceAccount::factory(),
            'name' => fake()->randomElement(['Production', 'Development', 'Testing', 'Staging']) . ' API Key',
            'key_hash' => hash('sha256', $plainKey),
            'key_prefix' => substr($plainKey, 0, 20),
            'status' => 'active',
            'last_used_at' => fake()->optional(0.7)->dateTimeBetween('-30 days', 'now'),
            'expires_at' => fake()->optional(0.3)->dateTimeBetween('now', '+1 year'),
            'revoked_at' => null,
            'metadata' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'revoked_at' => null,
            'revoke_reason' => null,
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'revoked',
            'revoked_at' => now(),
            'revoke_reason' => fake()->sentence(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => now()->subDay(),
        ]);
    }

    public function withServiceAccount(ServiceAccount $account): static
    {
        return $this->state(fn (array $attributes) => [
            'service_account_id' => $account->id,
            'user_id' => $account->user_id,
        ]);
    }

    public function withKnownKey(string $plainKey): static
    {
        return $this->state(fn (array $attributes) => [
            'key_hash' => hash('sha256', $plainKey),
            'key_prefix' => substr($plainKey, 0, 12),
        ]);
    }
}

