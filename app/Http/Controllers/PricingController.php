<?php

namespace App\Http\Controllers;

use App\Actions\Pricing\CalculateCostAction;
use App\Actions\Pricing\GetPricingAction;
use App\Http\Requests\Pricing\CalculateCostRequest;
use Illuminate\Http\JsonResponse;

class PricingController extends Controller
{
    /**
     * Get current pricing for all services.
     */
    public function index(GetPricingAction $action): JsonResponse
    {
        $result = $action->execute();

        return response()->json($result);
    }

    /**
     * Get pricing for a specific service.
     */
    public function show(string $serviceType, GetPricingAction $action): JsonResponse
    {
        $result = $action->execute($serviceType);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 404);
        }

        return response()->json($result);
    }

    /**
     * Calculate cost for a given number of tokens.
     */
    public function calculate(CalculateCostRequest $request, CalculateCostAction $action): JsonResponse
    {
        $validated = $request->validated();
        
        // Get service account for volume discount calculation
        $serviceAccount = auth()->user()?->serviceAccount;

        $result = $action->execute(
            $validated['service_type'],
            $validated['tokens'],
            $serviceAccount
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
        }

        return response()->json($result);
    }
}
