<?php

namespace App\Actions\Subscription;

use App\Models\BillingCycle;
use App\Models\Subscription;
use Carbon\Carbon;

class RenewSubscriptionAction
{
    /**
     * Renew a subscription (create new billing cycle).
     */
    public function execute(Subscription $subscription): array
    {
        if (!$subscription->auto_renew) {
            return [
                'success' => false,
                'error' => 'Subscription auto-renewal is disabled',
                'status' => 400,
            ];
        }
        
        if ($subscription->status !== 'active') {
            return [
                'success' => false,
                'error' => 'Subscription is not active',
                'status' => 400,
            ];
        }
        
        $plan = $subscription->plan;
        
        if (!$plan) {
            return [
                'success' => false,
                'error' => 'Plan not found',
                'status' => 404,
            ];
        }
        
        $newEndsAt = match($plan->billing_period) {
            'monthly' => ($subscription->ends_at ?? now())->copy()->addMonth(),
            'yearly' => ($subscription->ends_at ?? now())->copy()->addYear(),
            default => null,
        };
        
        $subscription->update([
            'ends_at' => $newEndsAt,
            'status' => 'active',
        ]);
        
        // Create billing cycle
        $periodStart = $subscription->ends_at->copy()->subMonth();
        if ($plan->billing_period === 'yearly') {
            $periodStart = $subscription->ends_at->copy()->subYear();
        }
        
        $billingCycle = BillingCycle::create([
            'subscription_id' => $subscription->id,
            'period_start' => $periodStart,
            'period_end' => $subscription->ends_at,
            'status' => 'pending',
            'amount' => $plan->price,
            'currency' => $plan->currency,
        ]);
        
        return [
            'success' => true,
            'message' => 'Subscription renewed successfully',
            'subscription' => $subscription->load('plan'),
            'billing_cycle' => $billingCycle,
            'status' => 200,
        ];
    }
}
