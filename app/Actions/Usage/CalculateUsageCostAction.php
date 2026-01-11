<?php

namespace App\Actions\Usage;

use App\Models\Subscription;
use App\Models\User;
use App\Services\TokenPricingService;

final class CalculateUsageCostAction
{
    public function __construct(
        private TokenPricingService $tokenPricingService
    ) {}
    
    /**
     * Calculate usage costs for a subscription.
     */
    public function execute(User $user, string $subscriptionId, ?string $serviceType = null): array
    {
        $subscription = Subscription::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->with(['subscriptionServices', 'plan'])
            ->first();
        
        if (!$subscription) {
            return [
                'success' => false,
                'error' => 'Subscription not found',
                'status' => 404,
            ];
        }
        
        $services = $subscription->subscriptionServices;
        
        // Filter by service type if provided
        if ($serviceType) {
            $services = $services->filter(fn($service) => $service->service_type->value === $serviceType);
            
            if ($services->isEmpty()) {
                return [
                    'success' => false,
                    'error' => 'Service type not found in subscription',
                    'status' => 404,
                ];
            }
        }
        
        $costs = [];
        $totalCost = 0;
        
        foreach ($services as $service) {
            $costData = $this->tokenPricingService->calculateCost($service);
            
            $costs[] = [
                'service_type' => $service->service_type->value,
                'allocated_tokens' => $costData['allocated_tokens'],
                'tokens_used' => $costData['total_tokens_used'],
                'included_tokens' => $costData['included_tokens'],
                'overage_tokens' => $costData['overage_tokens'],
                'cost' => $costData['cost'],
                'breakdown' => $costData['breakdown'],
                'pricing' => [
                    'price_per_token' => $costData['price_per_token'],
                    'price_per_1k_tokens' => $costData['price_per_1k_tokens'],
                    'pay_as_you_go' => $costData['pay_as_you_go'],
                ],
            ];
            
            $totalCost += $costData['cost'];
        }
        
        return [
            'success' => true,
            'subscription_id' => $subscription->id,
            'currency' => $subscription->plan->currency,
            'total_cost' => round($totalCost, 2),
            'services' => $costs,
        ];
    }
}
