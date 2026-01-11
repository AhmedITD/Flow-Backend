<?php

namespace App\Actions\Subscription;

use App\Models\Subscription;
use App\Models\User;

class CancelSubscriptionAction
{
    /**
     * Cancel a subscription for a user.
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
        
        if ($subscription->status === 'cancelled') {
            return [
                'success' => false,
                'error' => 'Subscription is already cancelled',
                'status' => 400,
            ];
        }
        
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'auto_renew' => false,
        ]);
        
        return [
            'success' => true,
            'message' => 'Subscription cancelled successfully',
            'subscription' => $subscription->load('plan'),
            'status' => 200,
        ];
    }
}
