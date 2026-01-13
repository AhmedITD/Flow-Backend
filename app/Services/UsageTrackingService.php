<?php

namespace App\Services;

use App\Enums\ServiceType;
use App\Models\Pricing;
use App\Models\PricingTier;
use App\Models\ServiceAccount;
use App\Models\UsageRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for tracking token usage for service accounts (pay-as-you-go).
 * 
 * Token tracking flow:
 * 1. HandleChatAction calculates total tokens from OpenAI response
 * 2. HandleChatAction calls recordTokenUsage() with the calculated tokens
 * 3. This service calculates cost, stores the record, and deducts from balance
 */
class UsageTrackingService
{
    /**
     * Get active service account for a user.
     */
    public function getActiveServiceAccount(?int $userId): ?ServiceAccount
    {
        if (!$userId) {
            return null;
        }

        return ServiceAccount::where('user_id', $userId)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Check if service account has sufficient balance for estimated usage.
     */
    public function checkBalance(ServiceAccount $serviceAccount, ServiceType $serviceType, int $estimatedTokens = 1000): array
    {
        if (!$serviceAccount->isActive()) {
            return [
                'allowed' => false,
                'reason' => 'Service account is not active',
            ];
        }

        // Get current pricing
        $pricing = Pricing::getCurrentPricing($serviceType->value);
        
        if (!$pricing) {
            return [
                'allowed' => false,
                'reason' => "No pricing configured for service: {$serviceType->value}",
            ];
        }

        // Estimate cost for the request
        $estimatedCost = $pricing->calculateCost($estimatedTokens);

        if (!$serviceAccount->hasSufficientBalance($estimatedCost)) {
            return [
                'allowed' => false,
                'reason' => 'Insufficient balance',
                'balance' => (float) $serviceAccount->balance,
                'available_credit' => $serviceAccount->available_credit,
                'estimated_cost' => $estimatedCost,
            ];
        }

        return [
            'allowed' => true,
            'balance' => (float) $serviceAccount->balance,
            'available_credit' => $serviceAccount->available_credit,
            'estimated_cost' => $estimatedCost,
        ];
    }

    /**
     * Record token usage for a service account.
     * 
     * @param ServiceAccount $serviceAccount The service account to record usage for
     * @param ServiceType $serviceType The service type being used
     * @param int $tokensUsed Number of tokens consumed (already calculated by caller)
     * @param string $actionType Type of action (e.g., 'chat', 'tool_call')
     * @param array|null $metadata Optional metadata to store
     * @return array Result with usage record and updated balance
     */
    public function recordTokenUsage(
        ServiceAccount $serviceAccount,
        ServiceType $serviceType,
        int $tokensUsed,
        string $actionType = 'chat',
        ?array $metadata = null
    ): array {
        if ($tokensUsed <= 0) {
            Log::warning('No tokens to record', [
                'service_account_id' => $serviceAccount->id,
                'service_type' => $serviceType->value,
                'action_type' => $actionType,
            ]);
            return [
                'success' => false,
                'message' => 'No tokens to record',
            ];
        }

        // Get current pricing
        $pricing = Pricing::getCurrentPricing($serviceType->value);
        
        if (!$pricing) {
            Log::error('No pricing found for service', [
                'service_type' => $serviceType->value,
            ]);
            return [
                'success' => false,
                'message' => "No pricing configured for service: {$serviceType->value}",
            ];
        }

        // Calculate cost with volume discounts
        $cumulativeTokens = $serviceAccount->getTokensUsed($serviceType->value);
        $tier = PricingTier::getTierForUsage($serviceType->value, $cumulativeTokens);
        
        $basePrice = (float) $pricing->price_per_1k_tokens;
        $effectivePrice = $tier ? $tier->getEffectivePrice($basePrice) : $basePrice;
        $billableTokens = max($tokensUsed, $pricing->min_tokens);
        $cost = ($billableTokens / 1000) * $effectivePrice;

        return DB::transaction(function () use ($serviceAccount, $serviceType, $tokensUsed, $cost, $actionType, $metadata) {
            // Create usage record
            $usageRecord = UsageRecord::create([
                'service_account_id' => $serviceAccount->id,
                'service_type' => $serviceType->value,
                'tokens_used' => $tokensUsed,
                'cost' => $cost,
                'action_type' => $actionType,
                'metadata' => $metadata,
                'recorded_at' => now(),
            ]);

            // Deduct from balance
            $serviceAccount->deductBalance($cost);
            
            Log::info('Token usage recorded', [
                'service_account_id' => $serviceAccount->id,
                'service_type' => $serviceType->value,
                'tokens_used' => $tokensUsed,
                'cost' => $cost,
                'new_balance' => $serviceAccount->balance,
                'action_type' => $actionType,
            ]);

            // Check if balance is critically low
            if ($serviceAccount->balance < 0 && $serviceAccount->credit_limit == 0) {
                $serviceAccount->suspend();
                Log::warning('Service account suspended due to negative balance', [
                    'service_account_id' => $serviceAccount->id,
                    'balance' => $serviceAccount->balance,
                ]);
            }

            return [
                'success' => true,
                'usage_record' => [
                    'id' => $usageRecord->id,
                    'tokens_used' => $usageRecord->tokens_used,
                    'cost' => (float) $usageRecord->cost,
                ],
                'balance' => [
                    'current' => (float) $serviceAccount->balance,
                    'available' => $serviceAccount->available_credit,
                ],
            ];
        });
    }
}
