<?php

namespace App\Actions\Plan;

use App\Models\Plan;

final class GetPlanAction
{
    /**
     * Get a specific plan by ID.
     */
    public function execute(string $planId): array
    {
        $plan = Plan::find($planId);
        
        if (!$plan) {
            return [
                'success' => false,
                'error' => 'Plan not found',
            ];
        }
        
        return [
            'success' => true,
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'description' => $plan->description,
                'price' => $plan->price,
                'currency' => $plan->currency,
                'billing_period' => $plan->billing_period,
                'trial_days' => $plan->trial_days,
                'features' => $plan->features,
                'limits' => $plan->limits,
                'is_active' => $plan->is_active,
                'created_at' => $plan->created_at,
            ],
        ];
    }
}
