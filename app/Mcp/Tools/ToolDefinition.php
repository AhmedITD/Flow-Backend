<?php

namespace App\Mcp\Tools;

/**
 * Base class for tool definitions.
 * Defines the contract for tools used by both MCP and OpenAI.
 */
abstract class ToolDefinition
{
    /**
     * The tool's unique name (snake_case).
     */
    abstract public function name(): string;

    /**
     * Human-readable description of what the tool does.
     */
    abstract public function description(): string;

    /**
     * Define the tool's parameters schema.
     *
     * @return array OpenAI-compatible JSON Schema
     */
    abstract public function parameters(): array;

    /**
     * Execute the tool with given arguments.
     *
     * @param array $arguments The validated arguments
     * @return array ['success' => bool, 'content' => string, 'error' => string|null, ...]
     */
    abstract public function execute(array $arguments): array;

    /**
     * Get required parameter names.
     *
     * @return array<string>
     */
    public function required(): array
    {
        return [];
    }

    /**
     * Convert to OpenAI function calling format.
     */
    public function toOpenAIFormat(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => $this->description(),
                'parameters' => [
                    'type' => 'object',
                    'properties' => $this->parameters(),
                    'required' => $this->required(),
                ],
            ],
        ];
    }

    /**
     * Convert to Laravel MCP schema format.
     * Used by MCP Tool classes.
     */
    public function toMcpSchema($schema): array
    {
        $mcpSchema = [];
        $params = $this->parameters();

        foreach ($params as $name => $definition) {
            $type = $definition['type'] ?? 'string';
            $desc = $definition['description'] ?? '';

            // Start with base type
            $field = match ($type) {
                'integer' => $schema->integer(),
                'boolean' => $schema->boolean(),
                'array' => $schema->array($schema->string()),
                default => $schema->string(),
            };

            // Add enum if present
            if (isset($definition['enum'])) {
                $field = $schema->string()->enum($definition['enum']);
            }

            // Add format if present
            if (isset($definition['format'])) {
                $field = $field->format($definition['format']);
            }

            // Add description
            $field = $field->description($desc);

            // Mark as nullable if not required
            if (!in_array($name, $this->required())) {
                $field = $field->nullable();
            }

            $mcpSchema[$name] = $field;
        }

        return $mcpSchema;
    }
}
