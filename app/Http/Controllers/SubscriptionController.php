<?php

namespace App\Http\Controllers;

use App\Actions\Subscription\CancelSubscriptionAction;
use App\Actions\Subscription\CreateSubscriptionAction;
use App\Actions\Subscription\GetSubscriptionAction;
use App\Actions\Subscription\ListSubscriptionsAction;
use App\Http\Requests\Subscription\CancelSubscriptionRequest;
use App\Http\Requests\Subscription\CreateSubscriptionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * List all subscriptions for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $action = new ListSubscriptionsAction();
        $result = $action->execute(Auth::user(), request()->only('status'));
        
        return response()->json($result);
    }
    
    /**
     * Create a new subscription.
     */
    public function store(CreateSubscriptionRequest $request): JsonResponse
    {
        $action = new CreateSubscriptionAction();
        $result = $action->execute(Auth::user(), $request->plan_id);
        
        if (!$result['success']) {
            return response()->json($result, $result['status'] ?? 400);
        }
        
        return response()->json($result, $result['status'] ?? 201);
    }
    
    /**
     * Get a specific subscription.
     */
    public function show(string $id): JsonResponse
    {
        $action = new GetSubscriptionAction();
        $result = $action->execute(Auth::user(), $id);
        
        if (!$result['success']) {
            return response()->json($result, $result['status'] ?? 404);
        }
        
        return response()->json($result);
    }
    
    /**
     * Cancel a subscription.
     */
    public function cancel(CancelSubscriptionRequest $request, string $id): JsonResponse
    {
        $action = new CancelSubscriptionAction();
        $result = $action->execute(Auth::user(), $id, $request->input('reason'));
        
        if (!$result['success']) {
            return response()->json($result, $result['status'] ?? 400);
        }
        
        return response()->json($result);
    }
}
