<?php

namespace App\Actions\ApiKey;

use App\Enums\ServiceType;
use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Support\Str;

class GenerateApiKeyAction
{
    /**
     * Generate a new API key for the user.
     */
    public function execute(User $user, array $data): array
    {
        // Get user's active subscription
        $subscription = $user->activeSubscription;

        // Generate API key
        $prefix = $data['environment'] ?? 'live';
        $randomPart = bin2hex(random_bytes(16));
        $plainKey = "sk_{$prefix}_{$randomPart}";
        $keyHash = hash('sha256', $plainKey);
        $keyPrefix = substr($plainKey, 0, 20);

        // Create API key record
        $apiKey = ApiKey::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription?->id,
            'name' => $data['name'] ?? 'API Key ' . now()->format('Y-m-d H:i'),
            'key_hash' => $keyHash,
            'key_prefix' => $keyPrefix,
            'status' => 'active',
            'expires_at' => isset($data['expires_at']) ? $data['expires_at'] : null,
            'metadata' => $data['metadata'] ?? [],
        ]);

        // Attach services if provided, otherwise attach all services
        if (isset($data['services']) && is_array($data['services'])) {
            $serviceTypes = array_map(fn($service) => ServiceType::from($service), $data['services']);
        } else {
            // Default to all services
            $serviceTypes = ServiceType::cases();
        }
        $apiKey->attachServices($serviceTypes);

        return [
            'success' => true,
            'api_key' => $apiKey,
            'plain_key' => $plainKey, // Only shown once
        ];
    }
}

