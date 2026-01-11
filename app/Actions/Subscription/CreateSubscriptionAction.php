<?php

namespace App\Actions\Subscription;

use App\Enums\ServiceType;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionService;
use App\Models\User;
use Carbon\Carbon;

class CreateSubscriptionAction
{
    /**
     * Create a new subscription for a user.
     */
    public function execute(User $user, string $planId): array
    {
        $plan = Plan::find($planId);
        
        if (!$plan) {
            return [
                'success' => false,
                'error' => 'Plan not found',
                'status' => 404,
            ];
        }
        
        if (!$plan->is_active) {
            return [
                'success' => false,
                'error' => 'Plan is not active',
                'status' => 400,
            ];
        }
        
        // Check if user has active subscription
        $activeSubscription = $user->activeSubscription;
        if ($activeSubscription) {
            return [
                'success' => false,
                'error' => 'User already has an active subscription',
                'status' => 400,
            ];
        }
        
        // Calculate dates
        $startsAt = now();
        $trialEndsAt = $plan->trial_days > 0 
            ? $startsAt->copy()->addDays($plan->trial_days) 
            : null;
        
        $endsAt = match($plan->billing_period) {
            'monthly' => $startsAt->copy()->addMonth(),
            'yearly' => $startsAt->copy()->addYear(),
            'one_time' => null,
            default => null,
        };
        
        // Create subscription
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => $plan->trial_days > 0 ? 'trial' : 'active',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'trial_ends_at' => $trialEndsAt,
            'auto_renew' => $plan->billing_period !== 'one_time',
        ]);
        
        // Create subscription services for all service types
        $limits = $plan->limits ?? [];
        foreach (ServiceType::cases() as $serviceType) {
            $serviceLimits = $limits[$serviceType->value] ?? [];
            $allocatedTokens = $serviceLimits['tokens'] ?? 0;
            
            SubscriptionService::create([
                'subscription_id' => $subscription->id,
                'service_type' => $serviceType,
                'allocated_tokens' => $allocatedTokens,
                'tokens_used' => 0,
                'reset_at' => $endsAt ?? $startsAt->copy()->addYear(),
            ]);
        }
        
        return [
            'success' => true,
            'message' => 'Subscription created successfully',
            'subscription' => $subscription->load(['plan', 'subscriptionServices']),
            'status' => 201,
        ];
    }
}
