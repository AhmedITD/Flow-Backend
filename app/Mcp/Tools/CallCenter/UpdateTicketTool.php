<?php

namespace App\Mcp\Tools\CallCenter;

use App\Mcp\Tools\CallCenter\Definitions\UpdateTicketDefinition;
use App\Mcp\Tools\CallCenter\Requests\UpdateTicketRequest;
use App\Mcp\Tools\ToolRegistry;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class UpdateTicketTool extends Tool
{
    private UpdateTicketDefinition $definition;

    protected string $description = '';

    public function __construct()
    {
        $this->definition = new UpdateTicketDefinition();
        $this->description = $this->definition->description();
        $this->name = 'update_ticket';
    }

    /**
     * Handle the tool request.
     */
    public function handle(UpdateTicketRequest $request): Response
    {   
        $result = ToolRegistry::execute('update_ticket', $request);

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
