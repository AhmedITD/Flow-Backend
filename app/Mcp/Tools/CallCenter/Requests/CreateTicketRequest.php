<?php

namespace App\Mcp\Tools\CallCenter\Requests;

class CreateTicketRequest
{
    public static function rules(): array
    {
        return [
            'tenant_id' => 'nullable|uuid',
            'subject' => 'required|string|max:255',
            'summary' => 'required|string',
            'channel' => 'nullable|in:voice,chat,email',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'category' => 'nullable|in:billing,technical,shipping,account,general,other',
            'call_session_id' => 'nullable|uuid',
        ];
    }
}
