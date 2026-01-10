<?php

namespace App\Enums;

enum ServiceType: string
{
    case CallCenter = 'call_center';
    case HR = 'hr';

    /**
     * Get the display name for the service.
     */
    public function label(): string
    {
        return match ($this) {
            self::CallCenter => 'Call Center',
            self::HR => 'HR',
        };
    }

    /**
     * Get the description for the service.
     */
    public function description(): string
    {
        return match ($this) {
            self::CallCenter => 'AI-powered call center with ticket management and customer support tools',
            self::HR => 'Human resources management with employee support and HR tools',
        };
    }

    /**
     * Get the unit type for tracking usage.
     */
    public function unitType(): string
    {
        return 'tokens'; // All services use tokens for now
    }

    /**
     * Get the MCP server key for this service.
     */
    public function mcpServerKey(): string
    {
        return match ($this) {
            self::CallCenter => 'call-center',
            self::HR => 'hr',
        };
    }

    /**
     * Get all active services.
     */
    public static function active(): array
    {
        return [
            self::CallCenter,
            self::HR,
        ];
    }

    /**
     * Get service by MCP server key.
     */
    public static function fromMcpServerKey(string $key): ?self
    {
        return match ($key) {
            'call-center' => self::CallCenter,
            'hr' => self::HR,
            default => null,
        };
    }
}

