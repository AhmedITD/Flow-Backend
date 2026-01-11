<?php

namespace App\Actions\Subscription;

use App\Models\Subscription;
use App\Models\User;

final class GetSubscriptionAction
{
    /**
     * Get a specific subscription for a user.
     */
    public function execute(User $user, string $subscriptionId): array
    {
        $subscription = Subscription::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->with(['plan', 'subscriptionServices', 'billingCycles', 'payments'])
            ->first();
        
        if (!$subscription) {
            return [
                'success' => false,
                'error' => 'Subscription not found',
                'status' => 404,
            ];
        }
        
        return [
            'success' => true,
            'subscription' => [
                'id' => $subscription->id,
                'plan' => [
                    'id' => $subscription->plan->id,
                    'name' => $subscription->plan->name,
                    'price' => $subscription->plan->price,
                    'currency' => $subscription->plan->currency,
                    'billing_period' => $subscription->plan->billing_period,
                ],
                'status' => $subscription->status,
                'starts_at' => $subscription->starts_at,
                'ends_at' => $subscription->ends_at,
                'trial_ends_at' => $subscription->trial_ends_at,
                'cancelled_at' => $subscription->cancelled_at,
                'auto_renew' => $subscription->auto_renew,
                'services' => $subscription->subscriptionServices->map(fn($service) => [
                    'service_type' => $service->service_type->value,
                    'allocated_tokens' => $service->allocated_tokens,
                    'tokens_used' => $service->tokens_used,
                    'remaining_tokens' => $service->getRemainingTokens(),
                    'reset_at' => $service->reset_at,
                ]),
                'billing_cycles_count' => $subscription->billingCycles->count(),
                'payments_count' => $subscription->payments->count(),
            ],
        ];
    }
}
