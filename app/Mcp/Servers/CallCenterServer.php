<?php

namespace App\Mcp\Servers;

use App\Mcp\Prompts\CallCenter\EscalateTicketPrompt;
use App\Mcp\Prompts\CallCenter\HandleCustomerInquiryPrompt;
use App\Mcp\Resources\CallCenter\TicketResource;
use App\Mcp\Tools\ToolRegistry;
use Laravel\Mcp\Server;

class CallCenterServer extends Server
{
    /**
     * The server key in config/mcp.php
     */
    public const SERVER_KEY = 'call-center';

    /**
     * The MCP server's name.
     */
    protected string $name = 'Call Center Server';

    /**
     * The MCP server's version.
     */
    protected string $version = '1.0.0';

    /**
     * Boot the server from config.
     */
    public function __construct()
    {
        $config = ToolRegistry::getServerConfig(self::SERVER_KEY);

        $this->name = $config['name'] ?? $this->name;
        $this->version = $config['version'] ?? $this->version;
        $this->instructions = ToolRegistry::getSystemPrompt(self::SERVER_KEY);
        $this->tools = ToolRegistry::getMcpToolClasses(self::SERVER_KEY);
    }

    /**
     * The tools registered with this MCP server.
     * Auto-populated from ToolRegistry based on config.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Tool>>
     */
    protected array $tools = [];

    /**
     * The resources registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Resource>>
     */
    protected array $resources = [
        TicketResource::class,
    ];

    /**
     * The prompts registered with this MCP server.
     *
     * @var array<int, class-string<\Laravel\Mcp\Server\Prompt>>
     */
    protected array $prompts = [
        HandleCustomerInquiryPrompt::class,
        EscalateTicketPrompt::class,
    ];
}
