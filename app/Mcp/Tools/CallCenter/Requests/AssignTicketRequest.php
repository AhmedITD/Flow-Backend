<?php

namespace App\Mcp\Tools\CallCenter\Requests;

class AssignTicketRequest
{
    public static function rules(): array
    {
        return [
            'ticket_id' => 'required|uuid',
            'agent_id' => 'required|uuid',
        ];
    }
}
