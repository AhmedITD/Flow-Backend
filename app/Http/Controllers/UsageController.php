<?php

namespace App\Http\Controllers;

use App\Actions\Usage\GetUsageHistoryAction;
use App\Actions\Usage\GetUsageSummaryAction;
use App\Http\Requests\Usage\UsageHistoryRequest;
use App\Http\Requests\Usage\UsageSummaryRequest;
use Illuminate\Http\JsonResponse;

class UsageController extends Controller
{
    /**
     * Get usage summary for the current billing period.
     */
    public function summary(UsageSummaryRequest $request, GetUsageSummaryAction $action): JsonResponse
    {
        $user = auth()->user();
        $serviceAccount = $user->serviceAccount;

        if (!$serviceAccount) {
            return response()->json([
                'success' => false,
                'message' => 'No service account found',
            ], 404);
        }

        $result = $action->execute($serviceAccount, $request->input('period', 'month'));

        return response()->json($result);
    }

    /**
     * Get detailed usage history.
     */
    public function history(UsageHistoryRequest $request, GetUsageHistoryAction $action): JsonResponse
    {
        $user = auth()->user();
        $serviceAccount = $user->serviceAccount;

        if (!$serviceAccount) {
            return response()->json([
                'success' => false,
                'message' => 'No service account found',
            ], 404);
        }

        $result = $action->execute($serviceAccount, $request->validated());

        return response()->json($result);
    }
}
