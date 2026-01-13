<?php

namespace App\Http\Controllers;

use App\Actions\ServiceAccount\CreateServiceAccountAction;
use App\Actions\ServiceAccount\GetServiceAccountAction;
use App\Http\Requests\ServiceAccount\CreateServiceAccountRequest;
use Illuminate\Http\JsonResponse;

class ServiceAccountController extends Controller
{
    /**
     * Get the current user's service account.
     */
    public function show(GetServiceAccountAction $action): JsonResponse
    {
        $result = $action->execute(auth()->user());

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 404);
        }

        return response()->json($result);
    }

    /**
     * Create a new service account for the current user.
     */
    public function store(CreateServiceAccountRequest $request, CreateServiceAccountAction $action): JsonResponse
    {
        $result = $action->execute(auth()->user(), $request->validated());

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'service_account' => $result['service_account'] ?? null,
            ], 409); // Conflict - already exists
        }

        return response()->json($result, 201);
    }
}
