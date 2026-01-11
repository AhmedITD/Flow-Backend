<?php

namespace App\Actions\Ticket;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class UpdateTicketAction
{
    /**
     * Update ticket information.
     *
     * @param  array<string, mixed>  $data
     * @return Ticket
     *
     * @throws ModelNotFoundException
     */
    public function execute(string $ticketId, array $data): Ticket
    {
        $ticket = Ticket::find($ticketId);

        if (! $ticket) {
            throw new ModelNotFoundException("Ticket with ID {$ticketId} not found.");
        }

        $updates = array_filter([
            'subject' => $data['subject'] ?? null,
            'summary' => $data['summary'] ?? null,
            'priority' => $data['priority'] ?? null,
            'category' => $data['category'] ?? null,
        ], fn ($value) => $value !== null);

        if (empty($updates)) {
            throw new \InvalidArgumentException('No fields provided to update.');
        }

        $ticket->update($updates);
        $ticket->refresh();

        return $ticket;
    }
}

