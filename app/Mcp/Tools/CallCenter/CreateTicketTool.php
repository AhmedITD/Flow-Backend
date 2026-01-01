<?php

namespace App\Mcp\Tools\CallCenter;

use App\Mcp\Tools\CallCenter\Definitions\CreateTicketDefinition;
use App\Mcp\Tools\CallCenter\Requests\CreateTicketRequest;
use App\Mcp\Tools\ToolRegistry;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class CreateTicketTool extends Tool
{
    private CreateTicketDefinition $definition;

    protected string $description = '';

    public function __construct()
    {
        $this->definition = new CreateTicketDefinition();
        $this->description = $this->definition->description();
        $this->name = 'create_ticket';
    }

    /**
     * Handle the tool request.
     */
    public function handle(CreateTicketRequest $request): Response
    {

        $result = ToolRegistry::execute('create_ticket', $request);

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
