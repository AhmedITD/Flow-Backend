<?php

namespace App\Actions\Pricing;

use App\Models\Pricing;
use App\Models\PricingTier;

final class GetPricingAction
{
    /**
     * Get current pricing for all services or a specific service.
     */
    public function execute(?string $serviceType = null): array
    {
        if ($serviceType) {
            return $this->getPricingForService($serviceType);
        }

        return $this->getAllPricing();
    }

    private function getPricingForService(string $serviceType): array
    {
        $pricing = Pricing::getCurrentPricing($serviceType);

        if (!$pricing) {
            return [
                'success' => false,
                'message' => "No active pricing found for service: {$serviceType}",
            ];
        }

        $tiers = PricingTier::getTiersForService($serviceType);

        return [
            'success' => true,
            'pricing' => [
                'service_type' => $pricing->service_type,
                'price_per_1k_tokens' => (float) $pricing->price_per_1k_tokens,
                'min_tokens' => $pricing->min_tokens,
                'currency' => $pricing->currency,
                'effective_from' => $pricing->effective_from->toIso8601String(),
                'effective_until' => $pricing->effective_until?->toIso8601String(),
            ],
            'volume_tiers' => $tiers->map(fn($tier) => [
                'min_tokens' => $tier->min_tokens,
                'discount_percent' => (float) $tier->discount_percent,
                'price_per_1k_tokens' => $tier->price_per_1k_tokens ? (float) $tier->price_per_1k_tokens : null,
            ])->toArray(),
        ];
    }

    private function getAllPricing(): array
    {
        $services = ['call_center', 'hr'];
        $pricing = [];

        foreach ($services as $serviceType) {
            $servicePricing = Pricing::getCurrentPricing($serviceType);
            $tiers = PricingTier::getTiersForService($serviceType);

            if ($servicePricing) {
                $pricing[$serviceType] = [
                    'price_per_1k_tokens' => (float) $servicePricing->price_per_1k_tokens,
                    'min_tokens' => $servicePricing->min_tokens,
                    'currency' => $servicePricing->currency,
                    'effective_from' => $servicePricing->effective_from->toIso8601String(),
                    'volume_tiers' => $tiers->map(fn($tier) => [
                        'min_tokens' => $tier->min_tokens,
                        'discount_percent' => (float) $tier->discount_percent,
                        'price_per_1k_tokens' => $tier->price_per_1k_tokens ? (float) $tier->price_per_1k_tokens : null,
                    ])->toArray(),
                ];
            }
        }

        return [
            'success' => true,
            'pricing' => $pricing,
        ];
    }
}
