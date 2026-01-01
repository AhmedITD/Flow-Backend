<?php

namespace App\Mcp\Tools\CallCenter\Definitions;

use App\Actions\Ticket\UpdateTicketAction;
use App\Mcp\Tools\ToolDefinition;

class UpdateTicketDefinition extends ToolDefinition
{
    public function name(): string
    {
        return 'update_ticket';
    }

    public function description(): string
    {
        return 'Updates ticket information such as subject, summary, priority, or category. Use this to modify ticket details during a call.';
    }

    public function parameters(): array
    {
        return [
            'ticket_id' => [
                'type' => 'string',
                'format' => 'uuid',
                'description' => 'The ticket ID (UUID) to update',
            ],
            'subject' => [
                'type' => 'string',
                'description' => 'New subject line',
            ],
            'summary' => [
                'type' => 'string',
                'description' => 'Updated summary/description',
            ],
            'priority' => [
                'type' => 'string',
                'enum' => ['low', 'medium', 'high', 'urgent'],
                'description' => 'Updated priority level',
            ],
            'category' => [
                'type' => 'string',
                'enum' => ['billing', 'technical', 'shipping', 'account', 'general', 'other'],
                'description' => 'Updated category',
            ],
        ];
    }

    public function required(): array
    {
        return ['ticket_id'];
    }

    public function execute(array $args): array
    {
        try {
            $ticketId = $args['ticket_id'];
            unset($args['ticket_id']);

            $action = new UpdateTicketAction();
            $ticket = $action->execute($ticketId, $args);

            if (!$ticket) {
                return ['success' => false, 'error' => 'Ticket not found'];
            }

            return [
                'success' => true,
                'content' => "Ticket {$ticketId} updated successfully!\n\nSubject: {$ticket->subject}\nPriority: {$ticket->priority}\nCategory: {$ticket->category}",
                'ticket' => $ticket->toArray(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

