<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $tenantId = (string) Str::uuid(); // Shared tenant for demo

        // Create sample tickets with various statuses
        $ticketData = [
            [
                'status' => 'open',
                'priority' => 'high',
                'category' => 'technical',
                'subject' => 'API integration not working',
                'summary' => 'The API returns 500 errors when trying to create tickets. Started happening after the last update.',
            ],
            [
                'status' => 'open',
                'priority' => 'urgent',
                'category' => 'billing',
                'subject' => 'Double charged for subscription',
                'summary' => 'I was charged twice for my Pro plan subscription this month. Please refund the duplicate charge.',
            ],
            [
                'status' => 'pending',
                'priority' => 'medium',
                'category' => 'account',
                'subject' => 'Cannot reset password',
                'summary' => 'Password reset email never arrives. Checked spam folder. Need help accessing my account.',
            ],
            [
                'status' => 'pending',
                'priority' => 'low',
                'category' => 'general',
                'subject' => 'Feature request: Dark mode',
                'summary' => 'Would love to see a dark mode option in the dashboard for late night work sessions.',
            ],
            [
                'status' => 'resolved',
                'priority' => 'high',
                'category' => 'technical',
                'subject' => 'Chat bot not responding',
                'summary' => 'The AI chat was unresponsive for about 2 hours. Issue seems resolved now.',
            ],
            [
                'status' => 'closed',
                'priority' => 'medium',
                'category' => 'billing',
                'subject' => 'Upgrade to Enterprise plan',
                'summary' => 'Need to upgrade from Pro to Enterprise. Please provide pricing details.',
            ],
        ];

        foreach ($ticketData as $data) {
            Ticket::factory()
                ->create(array_merge($data, [
                    'tenant_id' => $tenantId,
                    'assigned_to' => $users->isNotEmpty() ? $users->random()->id : null,
                ]));
        }

        // Create additional random tickets
        Ticket::factory(20)->create([
            'tenant_id' => $tenantId,
        ]);

        // Create some urgent open tickets
        Ticket::factory(3)
            ->open()
            ->urgent()
            ->create([
                'tenant_id' => $tenantId,
            ]);

        $this->command->info('Tickets seeded successfully.');
    }
}

