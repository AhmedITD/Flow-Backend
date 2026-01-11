<?php

namespace App\Actions\ApiKey;

use App\Models\ApiKey;
use App\Models\User;

class GetApiKeyAction
{
    /**
     * Get API key details.
     */
    public function execute(User $user, string $apiKeyId): array
    {
        $apiKey = ApiKey::where('id', $apiKeyId)
            ->where('user_id', $user->id)
            ->with(['subscription.plan', 'apiKeyServices', 'usageRecords' => function ($query) {
                $query->latest()->take(10);
            }])
            ->first();

        if (!$apiKey) {
            return [
                'success' => false,
                'error' => 'API key not found',
            ];
        }

        return [
            'success' => true,
            'api_key' => [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'key_prefix' => $apiKey->key_prefix,
                'masked_key' => $apiKey->getMaskedKey(),
                'status' => $apiKey->status,
                'services' => $apiKey->apiKeyServices->map(fn($service) => $service->service_type->value),
                'last_used_at' => $apiKey->last_used_at,
                'expires_at' => $apiKey->expires_at,
                'revoked_at' => $apiKey->revoked_at,
                'created_at' => $apiKey->created_at,
                'subscription' => $apiKey->subscription ? [
                    'id' => $apiKey->subscription->id,
                    'plan' => $apiKey->subscription->plan->name ?? null,
                ] : null,
                'recent_usage' => $apiKey->usageRecords->map(function ($usage) {
                    return [
                        'endpoint' => $usage->endpoint,
                        'method' => $usage->method,
                        'status_code' => $usage->status_code,
                        'used_at' => $usage->used_at,
                    ];
                }),
            ],
        ];
    }
}

