<?php

namespace App\Http\Controllers;

use App\Actions\Usage\CalculateUsageCostAction;
use App\Actions\Usage\CreateUsagePaymentAction;
use App\Http\Requests\Usage\CalculateUsageCostRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UsageController extends Controller
{
    /**
     * Calculate usage costs for a subscription.
     */
    public function calculateCost(CalculateUsageCostRequest $request, string $subscriptionId): JsonResponse
    {
        $tokenPricingService = app(\App\Services\TokenPricingService::class);
        $action = new CalculateUsageCostAction($tokenPricingService);
        
        $result = $action->execute(
            Auth::user(),
            $subscriptionId,
            $request->input('service_type')
        );
        
        if (!$result['success']) {
            return response()->json($result, $result['status'] ?? 404);
        }
        
        return response()->json($result);
    }
    
    /**
     * Create a payment for usage costs.
     */
    public function createPayment(CalculateUsageCostRequest $request, string $subscriptionId): JsonResponse
    {
        $tokenPricingService = app(\App\Services\TokenPricingService::class);
        $action = new CreateUsagePaymentAction($tokenPricingService);
        
        $result = $action->execute(
            Auth::user(),
            $subscriptionId,
            $request->input('service_type')
        );
        
        if (!$result['success']) {
            return response()->json($result, $result['status'] ?? 400);
        }
        
        return response()->json($result, $result['status'] ?? 201);
    }
}
