<?php

namespace App\Mcp\Tools;

use App\Mcp\Tools\CallCenter\AssignTicketTool;
use App\Mcp\Tools\CallCenter\CreateTicketTool;
use App\Mcp\Tools\CallCenter\Definitions\AssignTicketDefinition;
use App\Mcp\Tools\CallCenter\Definitions\CreateTicketDefinition;
use App\Mcp\Tools\CallCenter\Definitions\SearchTicketsDefinition;
use App\Mcp\Tools\CallCenter\Definitions\UpdateTicketDefinition;
use App\Mcp\Tools\CallCenter\Definitions\UpdateTicketStatusDefinition;
use App\Mcp\Tools\CallCenter\Definitions\ViewTicketDefinition;
use App\Mcp\Tools\CallCenter\SearchTicketsTool;
use App\Mcp\Tools\CallCenter\UpdateTicketStatusTool;
use App\Mcp\Tools\CallCenter\UpdateTicketTool;
use App\Mcp\Tools\CallCenter\ViewTicketTool;

/**
 * Central registry for all tool definitions.
 * Supports multiple MCP servers with different tool sets.
 */
class ToolRegistry
{
    /**
     * All registered tool definitions.
     * Add new tools here - they become available to any server that references them.
     *
     * @var array<string, array{definition: class-string<ToolDefinition>, mcp: class-string|null}>
     */
    protected static array $tools = [
        // Call Center - Ticket Tools
        'create_ticket' => [
            'definition' => CreateTicketDefinition::class,
            'mcp' => CreateTicketTool::class,
        ],
        'view_ticket' => [
            'definition' => ViewTicketDefinition::class,
            'mcp' => ViewTicketTool::class,
        ],
        'update_ticket' => [
            'definition' => UpdateTicketDefinition::class,
            'mcp' => UpdateTicketTool::class,
        ],
        'search_tickets' => [
            'definition' => SearchTicketsDefinition::class,
            'mcp' => SearchTicketsTool::class,
        ],
        'assign_ticket' => [
            'definition' => AssignTicketDefinition::class,
            'mcp' => AssignTicketTool::class,
        ],
        'update_ticket_status' => [
            'definition' => UpdateTicketStatusDefinition::class,
            'mcp' => UpdateTicketStatusTool::class,
        ],

        // Add more tool groups here for other servers
    ];

    /**
     * Get all tool definitions.
     */
    public static function all(): array
    {
        return array_map(
            fn($config) => new $config['definition'](),
            static::$tools
        );
    }

    /**
     * Get tools for a specific server (from config).
     */
    public static function forServer(string $serverKey): array
    {
        $toolNames = config("mcp.servers.{$serverKey}.tools", []);

        return array_filter(
            static::all(),
            fn($def, $name) => in_array($name, $toolNames),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Get a specific tool definition.
     */
    public static function get(string $name): ?ToolDefinition
    {
        if (!isset(static::$tools[$name])) {
            return null;
        }

        return new static::$tools[$name]['definition']();
    }

    /**
     * Get all tools in OpenAI format.
     */
    public static function toOpenAIFormat(?string $serverKey = null): array
    {
        $tools = $serverKey ? static::forServer($serverKey) : static::all();

        return array_values(array_map(
            fn(ToolDefinition $tool) => $tool->toOpenAIFormat(),
            $tools
        ));
    }

    /**
     * Get MCP tool classes for a server.
     */
    public static function getMcpToolClasses(?string $serverKey = null): array
    {
        $toolNames = $serverKey
            ? config("mcp.servers.{$serverKey}.tools", [])
            : array_keys(static::$tools);

        return array_values(array_filter(array_map(
            fn($name) => static::$tools[$name]['mcp'] ?? null,
            $toolNames
        )));
    }

    /**
     * Execute a tool by name.
     */
    public static function execute(string $name, array $arguments): array
    {
        $tool = static::get($name);

        if (!$tool) {
            return ['success' => false, 'error' => "Unknown tool: {$name}"];
        }

        return $tool->execute($arguments);
    }

    /**
     * Get system prompt for a server.
     */
    public static function getSystemPrompt(string $serverKey): string
    {
        return config("mcp.servers.{$serverKey}.system_prompt", '');
    }

    /**
     * Get server config.
     */
    public static function getServerConfig(string $serverKey): array
    {
        return config("mcp.servers.{$serverKey}", []);
    }

    /**
     * List all registered tool names.
     */
    public static function listTools(): array
    {
        return array_keys(static::$tools);
    }

    /**
     * List all configured servers.
     */
    public static function listServers(): array
    {
        return array_keys(config('mcp.servers', []));
    }
}
