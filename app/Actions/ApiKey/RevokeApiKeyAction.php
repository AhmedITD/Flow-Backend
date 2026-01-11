<?php

namespace App\Actions\ApiKey;

use App\Models\ApiKey;
use App\Models\User;

class RevokeApiKeyAction
{
    /**
     * Revoke an API key.
     */
    public function execute(User $user, string $apiKeyId): array
    {
        $apiKey = ApiKey::where('id', $apiKeyId)
            ->where('user_id', $user->id)
            ->first();

        if (!$apiKey) {
            return [
                'success' => false,
                'error' => 'API key not found',
            ];
        }

        if ($apiKey->isRevoked()) {
            return [
                'success' => false,
                'error' => 'API key is already revoked',
            ];
        }

        $apiKey->revoke();

        return [
            'success' => true,
            'message' => 'API key revoked successfully',
            'api_key' => $apiKey,
        ];
    }
}

