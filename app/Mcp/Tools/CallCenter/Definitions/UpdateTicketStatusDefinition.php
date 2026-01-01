<?php

namespace App\Mcp\Tools\CallCenter\Definitions;

use App\Actions\Ticket\UpdateTicketStatusAction;
use App\Mcp\Tools\ToolDefinition;

class UpdateTicketStatusDefinition extends ToolDefinition
{
    public function name(): string
    {
        return 'update_ticket_status';
    }

    public function description(): string
    {
        return 'Updates the status of a ticket (open, pending, resolved, closed). Use this to track ticket progress and mark tickets as resolved or closed.';
    }

    public function parameters(): array
    {
        return [
            'ticket_id' => [
                'type' => 'string',
                'format' => 'uuid',
                'description' => 'The ticket ID (UUID) to update',
            ],
            'status' => [
                'type' => 'string',
                'enum' => ['open', 'pending', 'resolved', 'closed'],
                'description' => 'The new status for the ticket',
            ],
        ];
    }

    public function required(): array
    {
        return ['ticket_id', 'status'];
    }

    public function execute(array $args): array
    {
        try {
            $action = new UpdateTicketStatusAction();
            [$ticket, $oldStatus] = $action->execute($args['ticket_id'], $args['status']);

            if (!$ticket) {
                return ['success' => false, 'error' => 'Ticket not found'];
            }

            return [
                'success' => true,
                'content' => "Ticket {$args['ticket_id']} status updated from '{$oldStatus}' to '{$args['status']}' successfully!",
                'ticket' => $ticket->toArray(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

