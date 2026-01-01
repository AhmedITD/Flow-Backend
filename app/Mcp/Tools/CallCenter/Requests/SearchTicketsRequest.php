<?php

namespace App\Mcp\Tools\CallCenter\Requests;

class SearchTicketsRequest
{
    public static function rules(): array
    {
        return [
            'tenant_id' => 'nullable|uuid',
            'status' => 'nullable|in:open,pending,resolved,closed',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'category' => 'nullable|in:billing,technical,shipping,account,general,other',
            'assigned_to' => 'nullable|uuid',
            'limit' => 'nullable|integer|min:1|max:50',
        ];
    }
}
