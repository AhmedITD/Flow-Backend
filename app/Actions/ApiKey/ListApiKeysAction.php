<?php

namespace App\Actions\ApiKey;

use App\Models\User;

class ListApiKeysAction
{
    /**
     * List all API keys for the user.
     */
    public function execute(User $user, array $filters = []): array
    {
        $query = $user->apiKeys()->with(['subscription.plan']);

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by subscription
        if (isset($filters['subscription_id'])) {
            $query->where('subscription_id', $filters['subscription_id']);
        }

        $apiKeys = $query->orderBy('created_at', 'desc')->get();

        return [
            'success' => true,
            'api_keys' => $apiKeys->map(function ($key) {
                return [
                    'id' => $key->id,
                    'name' => $key->name,
                    'key_prefix' => $key->key_prefix,
                    'masked_key' => $key->getMaskedKey(),
                    'status' => $key->status,
                    'scopes' => $key->scopes,
                    'last_used_at' => $key->last_used_at,
                    'expires_at' => $key->expires_at,
                    'created_at' => $key->created_at,
                    'subscription' => $key->subscription ? [
                        'id' => $key->subscription->id,
                        'plan' => $key->subscription->plan->name ?? null,
                    ] : null,
                ];
            }),
        ];
    }
}

