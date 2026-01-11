<?php

namespace App\Actions\Subscription;

use App\Models\User;

class ListSubscriptionsAction
{
    /**
     * List all subscriptions for a user.
     */
    public function execute(User $user, array $filters = []): array
    {
        $query = $user->subscriptions()->with(['plan']);
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        $subscriptions = $query->orderBy('created_at', 'desc')->get();
        
        return [
            'success' => true,
            'subscriptions' => $subscriptions->map(fn($sub) => [
                'id' => $sub->id,
                'plan' => [
                    'id' => $sub->plan->id,
                    'name' => $sub->plan->name,
                    'price' => $sub->plan->price,
                    'currency' => $sub->plan->currency,
                ],
                'status' => $sub->status,
                'starts_at' => $sub->starts_at,
                'ends_at' => $sub->ends_at,
                'trial_ends_at' => $sub->trial_ends_at,
                'auto_renew' => $sub->auto_renew,
                'created_at' => $sub->created_at,
            ]),
        ];
    }
}
