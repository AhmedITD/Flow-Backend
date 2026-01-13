<?php

namespace App\Actions\Usage;

use App\Enums\ServiceType;
use App\Models\ServiceAccount;
use App\Models\UsageRecord;

final class RecordUsageAction
{
    /**
     * Record usage and deduct from service account balance.
     * 
     * @throws \RuntimeException If no pricing found or insufficient balance
     */
    public function execute(
        ServiceAccount $serviceAccount,
        ServiceType $serviceType,
        int $tokensUsed,
        ?string $actionType = null,
        ?string $resourceId = null,
        ?array $metadata = null
    ): array {
        // Check if service account is active
        if (!$serviceAccount->isActive()) {
            return [
                'success' => false,
                'message' => 'Service account is not active',
            ];
        }

        // Record usage (this calculates cost and deducts from balance)
        try {
            $record = UsageRecord::recordUsage(
                $serviceAccount,
                $serviceType,
                $tokensUsed,
                $actionType,
                $resourceId,
                $metadata
            );
        } catch (\RuntimeException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        // Refresh service account to get updated balance
        $serviceAccount->refresh();

        // Check if balance is low and account should be suspended
        if ($serviceAccount->balance < 0 && $serviceAccount->credit_limit == 0) {
            $serviceAccount->suspend();
        }

        return [
            'success' => true,
            'usage_record' => [
                'id' => $record->id,
                'service_type' => $record->service_type->value,
                'tokens_used' => $record->tokens_used,
                'cost' => (float) $record->cost,
                'recorded_at' => $record->recorded_at->toIso8601String(),
            ],
            'balance' => [
                'current' => (float) $serviceAccount->balance,
                'available' => $serviceAccount->available_credit,
            ],
        ];
    }
}
