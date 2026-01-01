<?php

namespace App\Mcp\Tools\CallCenter\Definitions;

use App\Actions\Ticket\AssignTicketAction;
use App\Mcp\Tools\ToolDefinition;

class AssignTicketDefinition extends ToolDefinition
{
    public function name(): string
    {
        return 'assign_ticket';
    }

    public function description(): string
    {
        return 'Assigns a ticket to a specific agent. Use this to route tickets to the appropriate team member or specialist.';
    }

    public function parameters(): array
    {
        return [
            'ticket_id' => [
                'type' => 'string',
                'format' => 'uuid',
                'description' => 'The ticket ID (UUID) to assign',
            ],
            'agent_id' => [
                'type' => 'string',
                'format' => 'uuid',
                'description' => 'The agent/user ID (UUID) to assign the ticket to',
            ],
        ];
    }

    public function required(): array
    {
        return ['ticket_id', 'agent_id'];
    }

    public function execute(array $args): array
    {
        try {
            $action = new AssignTicketAction();
            $ticket = $action->execute($args['ticket_id'], $args['agent_id']);

            if (!$ticket) {
                return ['success' => false, 'error' => 'Ticket not found'];
            }

            return [
                'success' => true,
                'content' => "Ticket {$args['ticket_id']} assigned to agent {$args['agent_id']} successfully!",
                'ticket' => $ticket->toArray(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

