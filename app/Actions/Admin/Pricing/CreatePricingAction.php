<?php

namespace App\Actions\Admin\Pricing;

use App\Models\Pricing;

final class CreatePricingAction
{
    /**
     * Create new pricing and end current active pricing if exists.
     */
    public function execute(array $data): Pricing
    {
        // End current active pricing if exists
        Pricing::where('service_type', $data['service_type'])
            ->whereNull('effective_until')
            ->update(['effective_until' => $data['effective_from']]);

        return Pricing::create($data);
    }
}
