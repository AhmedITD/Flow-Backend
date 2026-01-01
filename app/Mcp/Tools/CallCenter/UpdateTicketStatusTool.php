<?php

namespace App\Mcp\Tools\CallCenter;

use App\Mcp\Tools\CallCenter\Definitions\UpdateTicketStatusDefinition;
use App\Mcp\Tools\CallCenter\Requests\UpdateTicketStatusRequest;
use App\Mcp\Tools\ToolRegistry;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class UpdateTicketStatusTool extends Tool
{
    private UpdateTicketStatusDefinition $definition;
    
    protected string $description = '';

    public function __construct()
    {
        $this->definition = new UpdateTicketStatusDefinition();
        $this->description = $this->definition->description();
        $this->name = 'update_ticket_status';
    }

    /**
     * Handle the tool request.
     */
    public function handle(UpdateTicketStatusRequest $request): Response
    {
        $result = ToolRegistry::execute('update_ticket_status', $request);

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
