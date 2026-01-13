<?php

namespace App\Actions\Admin\Pricing;

use App\Models\Pricing;

final class DeletePricingAction
{
    /**
     * Delete pricing.
     */
    public function execute(Pricing $pricing): void
    {
        // Don't allow deleting if it's the only active pricing for a service
        $activeCount = Pricing::where('service_type', $pricing->service_type)
            ->whereNull('effective_until')
            ->count();

        if ($activeCount <= 1 && $pricing->effective_until === null) {
            throw new \Exception('Cannot delete the only active pricing for this service.');
        }

        $pricing->delete();
    }
}
