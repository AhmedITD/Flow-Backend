<?php

namespace App\Mcp\Tools\CallCenter\Requests;

class ViewTicketRequest
{
    public static function rules(): array
    {
        return [
            'ticket_id' => 'required|uuid',
        ];
    }
}
