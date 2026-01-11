<?php

namespace App\Actions\Usage;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Services\TokenPricingService;

class CreateUsagePaymentAction
{
    public function __construct(
        private TokenPricingService $tokenPricingService
    ) {}
    
    /**
     * Create a payment for usage costs.
     */
    public function execute(User $user, string $subscriptionId, ?string $serviceType = null): array
    {
        $subscription = Subscription::where('id', $subscriptionId)
            ->where('user_id', $user->id)
            ->with(['subscriptionServices', 'plan'])
            ->first();
        
        if (!$subscription) {
            return [
                'success' => false,
                'error' => 'Subscription not found',
                'status' => 404,
            ];
        }
        
        $services = $subscription->subscriptionServices;
        
        // Filter by service type if provided
        if ($serviceType) {
            $services = $services->filter(fn($service) => $service->service_type->value === $serviceType);
            
            if ($services->isEmpty()) {
                return [
                    'success' => false,
                    'error' => 'Service type not found in subscription',
                    'status' => 404,
                ];
            }
        }
        
        $totalCost = 0;
        $serviceBreakdown = [];
        
        foreach ($services as $service) {
            $costData = $this->tokenPricingService->calculateCost($service);
            
            if ($costData['cost'] > 0) {
                $totalCost += $costData['cost'];
                $serviceBreakdown[] = [
                    'service_type' => $service->service_type->value,
                    'tokens_used' => $costData['total_tokens_used'],
                    'cost' => $costData['cost'],
                ];
            }
        }
        
        if ($totalCost <= 0) {
            return [
                'success' => false,
                'error' => 'No usage costs to charge',
                'status' => 400,
            ];
        }
        
        // Create payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'amount' => $totalCost,
            'currency' => $subscription->plan->currency,
            'status' => 'pending',
            'description' => 'Usage-based payment for token consumption',
            'metadata' => [
                'type' => 'usage',
                'services' => $serviceBreakdown,
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
        
        return [
            'success' => true,
            'message' => 'Usage payment created successfully',
            'payment' => [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'description' => $payment->description,
            ],
            'breakdown' => $serviceBreakdown,
            'status' => 201,
        ];
    }
}
