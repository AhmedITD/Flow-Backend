<?php

namespace App\Actions\Admin\Pricing;

use App\Models\PricingTier;

final class CreatePricingTierAction
{
    /**
     * Create or update pricing tier.
     */
    public function execute(array $data): PricingTier
    {
        return PricingTier::updateOrCreate(
            [
                'service_type' => $data['service_type'],
                'min_tokens' => $data['min_tokens'],
            ],
            $data
        );
    }
}
