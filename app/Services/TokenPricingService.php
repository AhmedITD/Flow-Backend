<?php

namespace App\Services;

use App\Enums\ServiceType;
use App\Models\Plan;
use App\Models\SubscriptionService;

class TokenPricingService
{
    /**
     * Calculate cost for tokens used in a subscription service.
     * 
     * @param SubscriptionService $subscriptionService
     * @param int $tokensUsed Total tokens used
     * @return array ['cost' => float, 'included_tokens' => int, 'overage_tokens' => int, 'breakdown' => array]
     */
    public function calculateCost(SubscriptionService $subscriptionService, ?int $tokensUsed = null): array
    {
        $tokensUsed = $tokensUsed ?? $subscriptionService->tokens_used;
        $plan = $subscriptionService->subscription->plan;
        $limits = $plan->limits ?? [];
        $serviceType = $subscriptionService->service_type;
        
        $serviceLimits = $limits[$serviceType->value] ?? [];
        $allocatedTokens = $serviceLimits['tokens'] ?? $subscriptionService->allocated_tokens;
        $pricePerToken = $serviceLimits['price_per_token'] ?? 0;
        $pricePer1kTokens = $serviceLimits['price_per_1k_tokens'] ?? null;
        
        // Calculate overage
        $overageTokens = max(0, $tokensUsed - $allocatedTokens);
        $includedTokens = min($tokensUsed, $allocatedTokens);
        
        // Calculate cost
        $overageCost = 0;
        if ($overageTokens > 0 && $pricePerToken > 0) {
            if ($pricePer1kTokens !== null) {
                // Calculate per 1k tokens (round up)
                $overageCost = ceil($overageTokens / 1000) * $pricePer1kTokens;
            } else {
                // Calculate per token
                $overageCost = $overageTokens * $pricePerToken;
            }
        }
        
        // For pay-as-you-go plans (subscription price = 0 or very low)
        // Charge for all tokens, not just overage
        $totalCost = 0;
        $payAsYouGo = ($plan->price == 0 || $serviceLimits['pay_as_you_go'] ?? false);
        
        if ($payAsYouGo) {
            if ($pricePer1kTokens !== null) {
                $totalCost = ceil($tokensUsed / 1000) * $pricePer1kTokens;
            } else {
                $totalCost = $tokensUsed * $pricePerToken;
            }
            $overageTokens = $tokensUsed;
            $includedTokens = 0;
        } else {
            $totalCost = $overageCost;
        }
        
        return [
            'cost' => round($totalCost, 2),
            'included_tokens' => $includedTokens,
            'overage_tokens' => $overageTokens,
            'total_tokens_used' => $tokensUsed,
            'allocated_tokens' => $allocatedTokens,
            'price_per_token' => $pricePerToken,
            'price_per_1k_tokens' => $pricePer1kTokens,
            'pay_as_you_go' => $payAsYouGo,
            'breakdown' => [
                'included' => $includedTokens,
                'overage' => $overageTokens,
                'overage_cost' => round($overageCost, 2),
                'total_cost' => round($totalCost, 2),
            ],
        ];
    }
    
    /**
     * Calculate total cost for all services in a subscription.
     */
    public function calculateSubscriptionCost(SubscriptionService $subscriptionService): array
    {
        return $this->calculateCost($subscriptionService);
    }
    
    /**
     * Calculate cost for tokens used in a specific period.
     * 
     * @param SubscriptionService $subscriptionService
     * @param \Carbon\Carbon $periodStart
     * @param \Carbon\Carbon $periodEnd
     * @return array
     */
    public function calculatePeriodCost(
        SubscriptionService $subscriptionService,
        \Carbon\Carbon $periodStart,
        \Carbon\Carbon $periodEnd
    ): array {
        // Get usage records for the period
        $tokensUsed = $subscriptionService->subscription
            ->usageRecords()
            ->where('service_type', $subscriptionService->service_type->value)
            ->whereBetween('recorded_at', [$periodStart, $periodEnd])
            ->sum('tokens_used');
        
        return $this->calculateCost($subscriptionService, (int) $tokensUsed);
    }
}
