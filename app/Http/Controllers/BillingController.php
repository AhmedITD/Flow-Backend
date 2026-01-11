<?php

namespace App\Http\Controllers;

use App\Actions\Billing\GetBillingCyclesAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    /**
     * Get billing cycles for a subscription.
     */
    public function cycles(string $subscriptionId): JsonResponse
    {
        $action = new GetBillingCyclesAction();
        $result = $action->execute(Auth::user(), $subscriptionId);
        
        if (!$result['success']) {
            return response()->json($result, $result['status'] ?? 404);
        }
        
        return response()->json($result);
    }
}
