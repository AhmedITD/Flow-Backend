<?php

namespace App\Actions\Auth;

class LogoutAction
{
    /**
     * Execute the logout action.
     */
    public function execute(): array
    {
        auth('api')->logout();

        return [
            'success' => true,
            'message' => 'Successfully logged out',
        ];
    }
}

