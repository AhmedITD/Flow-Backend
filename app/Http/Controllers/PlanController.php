<?php

namespace App\Http\Controllers;

use App\Actions\Plan\GetPlanAction;
use App\Actions\Plan\ListPlansAction;
use App\Http\Requests\Plan\ListPlansRequest;
use Illuminate\Http\JsonResponse;

class PlanController extends Controller
{
    /**
     * List all available plans.
     */
    public function index(ListPlansRequest $request): JsonResponse
    {
        $action = new ListPlansAction();
        $result = $action->execute($request->validated());
        
        return response()->json($result);
    }
    
    /**
     * Get a specific plan.
     */
    public function show(string $id): JsonResponse
    {
        $action = new GetPlanAction();
        $result = $action->execute($id);
        
        if (!$result['success']) {
            return response()->json($result, $result['status'] ?? 404);
        }
        
        return response()->json($result);
    }
}
