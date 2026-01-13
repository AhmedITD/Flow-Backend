<?php

namespace App\Actions\Admin\Pricing;

use App\Models\PricingTier;

final class DeletePricingTierAction
{
    /**
     * Delete pricing tier.
     */
    public function execute(PricingTier $tier): void
    {
        $tier->delete();
    }
}
