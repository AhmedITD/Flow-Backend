<?php

namespace App\Actions\Plan;

use App\Models\Plan;

class ListPlansAction
{
    /**
     * List all available plans with optional filters.
     */
    public function execute(array $filters = []): array
    {
        $query = Plan::query();
        
        if (isset($filters['billing_period'])) {
            $query->where('billing_period', $filters['billing_period']);
        }
        
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        
        $plans = $query->orderBy('price', 'asc')->get();
        
        return [
            'success' => true,
            'plans' => $plans->map(fn($plan) => [
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
            ]),
        ];
    }
}
