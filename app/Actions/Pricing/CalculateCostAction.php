<?php

namespace App\Actions\Pricing;

use App\Models\Pricing;
use App\Models\PricingTier;
use App\Models\ServiceAccount;

final class CalculateCostAction
{
    /**
     * Calculate the cost for a given number of tokens.
     */
    public function execute(
        string $serviceType,
        int $tokens,
        ?ServiceAccount $serviceAccount = null
    ): array {
        $pricing = Pricing::getCurrentPricing($serviceType);

        if (!$pricing) {
            return [
                'success' => false,
                'message' => "No active pricing found for service: {$serviceType}",
            ];
        }

        // Apply minimum billable tokens
        $billableTokens = max($tokens, $pricing->min_tokens);
        $basePrice = (float) $pricing->price_per_1k_tokens;
        $effectivePrice = $basePrice;
        $discountPercent = 0;

        // Check for volume discount if service account is provided
        if ($serviceAccount) {
            $cumulativeTokens = $serviceAccount->getTokensUsed($serviceType);
            $tier = PricingTier::getTierForUsage($serviceType, $cumulativeTokens);

            if ($tier) {
                $effectivePrice = $tier->getEffectivePrice($basePrice);
                $discountPercent = $tier->discount_percent ?? 
                    (($basePrice - $effectivePrice) / $basePrice * 100);
            }
        }

        $cost = ($billableTokens / 1000) * $effectivePrice;

        return [
            'success' => true,
            'calculation' => [
                'service_type' => $serviceType,
                'tokens_requested' => $tokens,
                'billable_tokens' => $billableTokens,
                'base_price_per_1k' => $basePrice,
                'effective_price_per_1k' => $effectivePrice,
                'discount_percent' => round($discountPercent, 2),
                'total_cost' => round($cost, 4),
                'currency' => $pricing->currency,
            ],
        ];
    }

    /**
     * Estimate cost for a chat request based on typical token usage.
     */
    public function estimateChatCost(
        string $serviceType,
        int $estimatedInputTokens,
        int $estimatedOutputTokens,
        ?ServiceAccount $serviceAccount = null
    ): array {
        $totalTokens = $estimatedInputTokens + $estimatedOutputTokens;
        
        return $this->execute($serviceType, $totalTokens, $serviceAccount);
    }
}
