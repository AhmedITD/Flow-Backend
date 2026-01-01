<?php

namespace App\Mcp\Tools\CallCenter\Definitions;

use App\Actions\Ticket\SearchTicketsAction;
use App\Mcp\Tools\ToolDefinition;

class SearchTicketsDefinition extends ToolDefinition
{
    public function name(): string
    {
        return 'search_tickets';
    }

    public function description(): string
    {
        return 'Searches for tickets based on various criteria like status, priority, category, tenant ID, or assigned agent. Use this to find related tickets or check ticket history.';
    }

    public function parameters(): array
    {
        return [
            'tenant_id' => [
                'type' => 'string',
                'format' => 'uuid',
                'description' => 'Filter by tenant ID (UUID)',
            ],
            'status' => [
                'type' => 'string',
                'enum' => ['open', 'pending', 'resolved', 'closed'],
                'description' => 'Filter by status',
            ],
            'priority' => [
                'type' => 'string',
                'enum' => ['low', 'medium', 'high', 'urgent'],
                'description' => 'Filter by priority',
            ],
            'category' => [
                'type' => 'string',
                'enum' => ['billing', 'technical', 'shipping', 'account', 'general', 'other'],
                'description' => 'Filter by category',
            ],
            'assigned_to' => [
                'type' => 'string',
                'format' => 'uuid',
                'description' => 'Filter by assigned agent ID (UUID)',
            ],
            'limit' => [
                'type' => 'integer',
                'description' => 'Maximum number of results (default: 10, max: 50)',
            ],
        ];
    }

    public function required(): array
    {
        return [];
    }

    public function execute(array $args): array
    {
        try {
            $action = new SearchTicketsAction();
            $tickets = $action->execute($args);
            $results = $action->formatResults($tickets);

            return [
                'success' => true,
                'content' => $results,
                'count' => $tickets->count(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

