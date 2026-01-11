<?php

namespace App\Actions\Billing;

use App\Models\Subscription;
use App\Models\User;

class GetBillingCyclesAction
{
    /**
     * Get billing cycles for a subscription.
     */
    public function execute(User $user, string $subscriptionId): array
    {
        $subscription = Subscription::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$subscription) {
            return [
                'success' => false,
                'error' => 'Subscription not found',
                'status' => 404,
            ];
        }
        
        $billingCycles = $subscription->billingCycles()
            ->with('payment')
            ->orderBy('period_start', 'desc')
            ->get();
        
        return [
            'success' => true,
            'billing_cycles' => $billingCycles->map(fn($cycle) => [
                'id' => $cycle->id,
                'period_start' => $cycle->period_start,
                'period_end' => $cycle->period_end,
                'status' => $cycle->status,
                'amount' => $cycle->amount,
                'currency' => $cycle->currency,
                'paid_at' => $cycle->paid_at,
                'payment' => $cycle->payment ? [
                    'id' => $cycle->payment->id,
                    'status' => $cycle->payment->status,
                    'amount' => $cycle->payment->amount,
                ] : null,
                'created_at' => $cycle->created_at,
            ]),
        ];
    }
}
