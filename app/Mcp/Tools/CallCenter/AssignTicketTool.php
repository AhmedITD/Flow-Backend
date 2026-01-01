<?php

namespace App\Mcp\Tools\CallCenter;

use App\Mcp\Tools\CallCenter\Definitions\AssignTicketDefinition;
use App\Mcp\Tools\CallCenter\Requests\AssignTicketRequest;
use App\Mcp\Tools\ToolRegistry;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class AssignTicketTool extends Tool
{
    private AssignTicketDefinition $definition;

    protected string $description = '';
    
    public function __construct()
    {
        $this->definition = new AssignTicketDefinition();
        $this->description = $this->definition->description();
        $this->name = 'assign_ticket';
    }

    /**
     * Handle the tool request.
     */
    public function handle(AssignTicketRequest $request): Response
    {
        $result = ToolRegistry::execute('assign_ticket', $request);

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
