<?php

namespace App\Mcp\Tools\CallCenter\Definitions;

use App\Actions\Ticket\CreateTicketAction;
use App\Mcp\Tools\ToolDefinition;

class CreateTicketDefinition extends ToolDefinition
{
    public function name(): string
    {
        return 'create_ticket';
    }

    public function description(): string
    {
        return 'Creates a new support ticket for a customer inquiry. Use this when a customer calls in with a new issue or request.';
    }

    public function parameters(): array
    {
        return [
            'tenant_id' => [
                'type' => 'string',
                'description' => 'The tenant ID (UUID) for the customer. Use "00000000-0000-0000-0000-000000000001" if not specified.',
            ],
            'subject' => [
                'type' => 'string',
                'description' => 'Brief subject line describing the issue',
            ],
            'summary' => [
                'type' => 'string',
                'description' => 'Detailed description of the customer inquiry or issue',
            ],
            'channel' => [
                'type' => 'string',
                'enum' => ['voice', 'chat', 'email'],
                'description' => 'The communication channel (default: voice)',
            ],
            'priority' => [
                'type' => 'string',
                'enum' => ['low', 'medium', 'high', 'urgent'],
                'description' => 'Priority level (default: medium)',
            ],
            'category' => [
                'type' => 'string',
                'enum' => ['billing', 'technical', 'shipping', 'account', 'general', 'other'],
                'description' => 'Ticket category (default: general)',
            ],
        ];
    }

    public function required(): array
    {
        return ['subject', 'summary'];
    }

    public function execute(array $args): array
    {
        try {
            $action = new CreateTicketAction();
            $ticket = $action->execute([
                'tenant_id' => $args['tenant_id'] ?? '00000000-0000-0000-0000-000000000001',
                'subject' => $args['subject'],
                'summary' => $args['summary'],
                'channel' => $args['channel'] ?? 'chat',
                'priority' => $args['priority'] ?? 'medium',
                'category' => $args['category'] ?? 'general',
            ]);

            return [
                'success' => true,
                'content' => "Ticket created successfully!\n\nTicket ID: {$ticket->ticket_id}\nSubject: {$ticket->subject}\nStatus: {$ticket->status}\nPriority: {$ticket->priority}\nCategory: {$ticket->category}",
                'ticket' => $ticket->toArray(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

