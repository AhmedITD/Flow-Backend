<?php

namespace App\Actions\Ticket;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AssignTicketAction
{
    /**
     * Assign a ticket to an agent.
     *
     * @return Ticket
     *
     * @throws ModelNotFoundException
     */
    public function execute(string $ticketId, string $agentId): Ticket
    {
        $ticket = Ticket::find($ticketId);

        if (! $ticket) {
            throw new ModelNotFoundException("Ticket with ID {$ticketId} not found.");
        }

        $agent = User::find($agentId);

        if (! $agent) {
            throw new ModelNotFoundException("Agent with ID {$agentId} not found.");
        }

        $ticket->update(['assigned_to' => $agentId]);
        $ticket->refresh();

        return $ticket;
    }

    /**
     * Get the assigned agent for a ticket.
     */
    public function getAssignedAgent(Ticket $ticket): ?User
    {
        return $ticket->assignedAgent;
    }
}

