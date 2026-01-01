<?php

namespace App\Mcp\Tools\CallCenter\Requests;

class UpdateTicketRequest
{
    public static function rules(): array
    {
        return [
            'ticket_id' => 'required|uuid',
            'subject' => 'nullable|string|max:255',
            'summary' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'category' => 'nullable|in:billing,technical,shipping,account,general,other',
        ];
    }
}
