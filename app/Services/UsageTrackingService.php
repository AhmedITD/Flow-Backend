<?php

namespace App\Services;

use App\Enums\ServiceType;
use App\Models\Subscription;
use App\Models\SubscriptionService;
use App\Models\UsageRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for tracking token usage for subscriptions.
 * 
 * Token tracking flow:
 * 1. HandleChatAction calculates total tokens from OpenAI response
 * 2. HandleChatAction calls recordTokenUsage() with the calculated tokens
 * 3. This service stores the tokens and updates the subscription counter
 */
class UsageTrackingService
{
    /**
     * Get active subscription for a user.
     */
    public function getActiveSubscription(?int $userId): ?Subscription
    {
        if (!$userId) {
            return null;
        }

        return Subscription::where('user_id', $userId)
            ->whereIn('status', ['active', 'trial'])
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->first();
    }

    /**
     * Check if subscription has available tokens for a service type.
     */
    public function checkTokenLimit(Subscription $subscription, ServiceType $serviceType): array
    {
        $subscriptionService = SubscriptionService::where('subscription_id', $subscription->id)
            ->where('service_type', $serviceType)
            ->first();

        if (!$subscriptionService) {
            return [
                'allowed' => false,
                'reason' => 'Service not allocated to subscription',
                'service_type' => $serviceType->value,
            ];
        }

        if ($subscriptionService->isLimitExceeded()) {
            return [
                'allowed' => false,
                'reason' => 'Token limit exceeded',
                'tokens_used' => $subscriptionService->tokens_used,
                'allocated_tokens' => $subscriptionService->allocated_tokens,
                'service_type' => $serviceType->value,
            ];
        }

        return [
            'allowed' => true,
            'remaining_tokens' => $subscriptionService->getRemainingTokens(),
            'tokens_used' => $subscriptionService->tokens_used,
            'allocated_tokens' => $subscriptionService->allocated_tokens,
            'service_type' => $serviceType->value,
        ];
    }

    /**
     * Record token usage for a subscription.
     * 
     * @param Subscription $subscription The subscription to record usage for
     * @param ServiceType $serviceType The service type being used
     * @param int $tokensUsed Number of tokens consumed (already calculated by caller)
     * @param string $actionType Type of action (e.g., 'chat', 'tool_call')
     * @param array|null $metadata Optional metadata to store
     */
    public function recordTokenUsage(
        Subscription $subscription,
        ServiceType $serviceType,
        int $tokensUsed,
        string $actionType = 'chat',
        ?array $metadata = null
    ): void {
        if ($tokensUsed <= 0) {
            Log::warning('No tokens to record', [
                'subscription_id' => $subscription->id,
                'service_type' => $serviceType->value,
                'action_type' => $actionType,
            ]);
            return;
        }

        DB::transaction(function () use ($subscription, $serviceType, $tokensUsed, $actionType, $metadata) {
            // Get or create subscription service
            $subscriptionService = SubscriptionService::firstOrCreate(
                [
                    'subscription_id' => $subscription->id,
                    'service_type' => $serviceType,
                ],
                [
                    'allocated_tokens' => 0, // Set when plan is configured
                    'tokens_used' => 0,
                    'reset_at' => $this->calculateResetDate($subscription),
                ]
            );

            // Create usage record (append-only log)
            UsageRecord::create([
                'subscription_id' => $subscription->id,
                'service_type' => $serviceType,
                'tokens_used' => $tokensUsed,
                'action_type' => $actionType,
                'metadata' => $metadata,
                'recorded_at' => now(),
            ]);

            // Update subscription service counter
            $subscriptionService->incrementTokensUsed($tokensUsed);
            
            Log::info('Token usage recorded', [
                'subscription_id' => $subscription->id,
                'service_type' => $serviceType->value,
                'tokens_used' => $tokensUsed,
                'total_tokens_used' => $subscriptionService->fresh()->tokens_used,
                'action_type' => $actionType,
            ]);
        });
    }

    /**
     * Calculate reset date based on subscription billing period.
     */
    private function calculateResetDate(Subscription $subscription): \DateTime
    {
        $plan = $subscription->plan;

        if ($plan && $plan->billing_period === 'yearly') {
            return now()->addYear()->startOfMonth();
        }

        // Default to monthly
        return now()->addMonth()->startOfMonth();
    }
}
