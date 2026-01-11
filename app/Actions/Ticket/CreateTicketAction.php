<?php

namespace App\Actions\Ticket;

use App\Models\Ticket;
use App\Models\User;

final class CreateTicketAction
{
    /**
     * Create a new ticket.
     *
     * @param  array<string, mixed>  $data
     * @return Ticket
     */
    public function execute(array $data, ?User $user = null): Ticket
    {
        return Ticket::create([
            'tenant_id' => $data['tenant_id'],
            'subject' => $data['subject'],
            'summary' => $data['summary'],
            'channel' => $data['channel'] ?? 'voice',
            'priority' => $data['priority'] ?? 'medium',
            'category' => $data['category'] ?? 'general',
            'call_session_id' => $data['call_session_id'] ?? null,
            'created_by_type' => $user ? 'agent' : 'system',
            'created_by_id' => $user?->id ?? 'system',
            'status' => 'open',
        ]);
    }
}

