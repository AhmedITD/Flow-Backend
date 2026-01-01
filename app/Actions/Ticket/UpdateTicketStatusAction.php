<?php

namespace App\Actions\Ticket;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateTicketStatusAction
{
    /**
     * Update ticket status.
     *
     * @return array{Ticket, string} Returns the updated ticket and old status
     *
     * @throws ModelNotFoundException
     */
    public function execute(string $ticketId, string $status): array
    {
        $ticket = Ticket::find($ticketId);

        if (! $ticket) {
            throw new ModelNotFoundException("Ticket with ID {$ticketId} not found.");
        }

        $oldStatus = $ticket->status;
        $ticket->update(['status' => $status]);
        $ticket->refresh();

        return [$ticket, $oldStatus];
    }
}

