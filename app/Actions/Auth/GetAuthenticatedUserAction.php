<?php

namespace App\Actions\Auth;

final class GetAuthenticatedUserAction
{
    /**
     * Execute the get authenticated user action.
     */
    public function execute(): array
    {
        $user = auth('api')->user();

        return [
            'success' => true,
            'user' => $user,
        ];
    }
}

