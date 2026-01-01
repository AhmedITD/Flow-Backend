<?php

namespace App\Mcp\Tools\CallCenter;

use App\Mcp\Tools\CallCenter\Definitions\SearchTicketsDefinition;
use App\Mcp\Tools\CallCenter\Requests\SearchTicketsRequest;
use App\Mcp\Tools\ToolRegistry;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class SearchTicketsTool extends Tool
{
    private SearchTicketsDefinition $definition;
    protected string $description = '';

    public function __construct()
    {
        $this->definition = new SearchTicketsDefinition();
        $this->description = $this->definition->description();
        $this->name = 'search_tickets';
    }

    /**
     * Handle the tool request.
     */
    public function handle(SearchTicketsRequest $request): Response
    {
        $result = ToolRegistry::execute('search_tickets', $request);


        if ($result['success']) {
            return Response::text($result['content']);
        }

        return Response::text("Error: " . ($result['error'] ?? 'Unknown error'));
    }

    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return $this->definition->toMcpSchema($schema);
    }
}
