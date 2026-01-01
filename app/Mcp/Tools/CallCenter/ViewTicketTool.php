<?php

namespace App\Mcp\Tools\CallCenter;

use App\Mcp\Tools\CallCenter\Definitions\ViewTicketDefinition;
use App\Mcp\Tools\CallCenter\Requests\ViewTicketRequest;
use App\Mcp\Tools\ToolRegistry;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

class ViewTicketTool extends Tool
{
    private ViewTicketDefinition $definition;
    
    protected string $description = '';

    public function __construct()
    {
        $this->definition = new ViewTicketDefinition();
        $this->description = $this->definition->description();
        $this->name = 'view_ticket';
    }

    /**
     * Handle the tool request.
     */
    public function handle(ViewTicketRequest $request): Response
    {
        $result = ToolRegistry::execute('view_ticket', $request);


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
