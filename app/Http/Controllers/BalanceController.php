<?php

namespace App\Http\Controllers;

use App\Actions\Balance\GetBalanceAction;
use App\Actions\Balance\GetTransactionHistoryAction;
use App\Actions\Balance\TopUpBalanceAction;
use App\Http\Requests\Balance\TopUpRequest;
use App\Http\Requests\Balance\TransactionHistoryRequest;
use App\Models\ServiceAccount;
use Illuminate\Http\JsonResponse;

class BalanceController extends Controller
{
    /**
     * Get current balance information.
     */
    public function show(GetBalanceAction $action): JsonResponse
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
     * Initiate a balance top-up.
     */
    public function topUp(TopUpRequest $request, TopUpBalanceAction $action): JsonResponse
    {
        $user = auth()->user();
        $serviceAccount = $user->getOrCreateServiceAccount();

        $result = $action->execute($user, $serviceAccount, $request->validated());

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
        }

        return response()->json($result);
    }

    /**
     * Get transaction history.
     */
    public function transactions(TransactionHistoryRequest $request, GetTransactionHistoryAction $action): JsonResponse
    {
        $result = $action->execute(auth()->user(), null, $request->validated());

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 404);
        }

        return response()->json($result);
    }
}
