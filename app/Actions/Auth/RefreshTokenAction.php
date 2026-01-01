<?php

namespace App\Actions\Auth;

class RefreshTokenAction
{
    /**
     * Execute the refresh token action.
     */
    public function execute(): array
    {
        $token = auth('api')->refresh();

        return [
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ];
    }
}

