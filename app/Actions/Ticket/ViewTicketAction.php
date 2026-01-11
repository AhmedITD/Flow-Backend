<?php

namespace App\Actions\Ticket;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class ViewTicketAction
{
    /**
     * Get ticket details by ID.
     *
     * @return Ticket
     *
     * @throws ModelNotFoundException
     */
    public function execute(string $ticketId): Ticket
    {
        $ticket = Ticket::with(['assignedAgent'])->find($ticketId);

        if (! $ticket) {
            throw new ModelNotFoundException("Ticket with ID {$ticketId} not found.");
        }

        // Load creator conditionally
        if ($ticket->created_by_type === 'agent' && $ticket->created_by_id) {
            $ticket->load('creator');
        }

        return $ticket;
    }

    /**
     * Format ticket details as a readable string.
     */
    public function formatDetails(Ticket $ticket): string
    {
        $assignedAgent = $ticket->assignedAgent ? $ticket->assignedAgent->name : 'Unassigned';
        $creator = $ticket->creator ? $ticket->creator->name : 'System';

        return <<<MARKDOWN
# Ticket Details

**Ticket ID:** {$ticket->ticket_id}
**Subject:** {$ticket->subject}
**Status:** {$ticket->status}
**Priority:** {$ticket->priority}
**Category:** {$ticket->category}
**Channel:** {$ticket->channel}

**Summary:**
{$ticket->summary}

**Created By:** {$creator}
**Assigned To:** {$assignedAgent}
**Created At:** {$ticket->created_at->format('Y-m-d H:i:s')}
**Updated At:** {$ticket->updated_at->format('Y-m-d H:i:s')}
MARKDOWN;
    }
}

