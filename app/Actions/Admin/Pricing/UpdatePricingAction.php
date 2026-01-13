<?php

namespace App\Actions\Admin\Pricing;

use App\Models\Pricing;

final class UpdatePricingAction
{
    /**
     * Update pricing.
     */
    public function execute(Pricing $pricing, array $data): Pricing
    {
        $pricing->update($data);

        return $pricing->fresh();
    }
}
