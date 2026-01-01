<?php

namespace App\Mcp\Tools\CallCenter\Definitions;

use App\Actions\Ticket\ViewTicketAction;
use App\Mcp\Tools\ToolDefinition;

class ViewTicketDefinition extends ToolDefinition
{
    public function name(): string
    {
        return 'view_ticket';
    }

    public function description(): string
    {
        return 'Retrieves detailed information about a specific ticket by its ID. Use this to view ticket details, status, and history.';
    }

    public function parameters(): array
    {
        return [
            'ticket_id' => [
                'type' => 'string',
                'format' => 'uuid',
                'description' => 'The unique ticket ID (UUID) to retrieve',
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
            $action = new ViewTicketAction();
            $ticket = $action->execute($args['ticket_id']);

            if (!$ticket) {
                return ['success' => false, 'error' => 'Ticket not found'];
            }

            $content = $action->formatDetails($ticket);

            return [
                'success' => true,
                'content' => $content,
                'ticket' => $ticket->toArray(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

