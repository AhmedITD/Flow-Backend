<?php

namespace App\Mcp\Tools\CallCenter\Requests;

class UpdateTicketStatusRequest
{
    public static function rules(): array
    {
        return [
            'ticket_id' => 'required|uuid',
            'status' => 'required|in:open,pending,resolved,closed',
        ];
    }
}
