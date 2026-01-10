<?php

namespace Database\Factories;

use App\Models\ApiKey;
use App\Models\ApiKeyUsage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ApiKeyUsageFactory extends Factory
{
    protected $model = ApiKeyUsage::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'api_key_id' => ApiKey::factory(),
            'endpoint' => fake()->randomElement([
                'api/chat',
                'api/tickets',
                'api/tickets/create',
                'api/tickets/search',
            ]),
            'method' => fake()->randomElement(['GET', 'POST', 'PUT', 'DELETE']),
            'status_code' => fake()->randomElement([200, 201, 400, 401, 404, 500]),
            'response_time_ms' => fake()->numberBetween(50, 2000),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'metadata' => null,
            'used_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_code' => fake()->randomElement([200, 201]),
            'response_time_ms' => fake()->numberBetween(50, 500),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_code' => fake()->randomElement([400, 401, 404, 500]),
        ]);
    }

    public function chatEndpoint(): static
    {
        return $this->state(fn (array $attributes) => [
            'endpoint' => 'api/chat',
            'method' => 'POST',
        ]);
    }

    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'used_at' => now(),
        ]);
    }
}

