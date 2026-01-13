<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'ticket_id' => (string) Str::uuid(),
            'tenant_id' => (string) Str::uuid(),
            'channel' => fake()->randomElement(['voice', 'chat']),
            'status' => fake()->randomElement(['open', 'pending', 'resolved', 'closed']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'category' => fake()->randomElement(['billing', 'technical', 'shipping', 'account', 'general', 'other']),
            'subject' => fake()->sentence(6),
            'summary' => fake()->paragraph(),
            'created_by_type' => fake()->randomElement(['agent', 'system']),
            'created_by_id' => null,
            'assigned_to' => null,
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $user->id,
        ]);
    }

    public function billing(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'billing',
            'subject' => fake()->randomElement([
                'Payment not processed',
                'Refund request',
                'Invoice discrepancy',
                'Subscription billing issue',
            ]),
        ]);
    }

    public function technical(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'technical',
            'subject' => fake()->randomElement([
                'API not responding',
                'Integration error',
                'Feature not working',
                'Performance issue',
            ]),
        ]);
    }
}

