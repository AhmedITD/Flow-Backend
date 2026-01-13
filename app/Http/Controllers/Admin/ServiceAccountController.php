<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ServiceAccount\AdjustBalanceAction;
use App\Actions\Admin\ServiceAccount\ChangeStatusAction;
use App\Actions\Admin\ServiceAccount\ListServiceAccountsAction;
use App\Actions\Admin\ServiceAccount\ShowServiceAccountAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustBalanceRequest;
use App\Http\Requests\Admin\ChangeStatusRequest;
use App\Http\Requests\Admin\ListServiceAccountsRequest;
use App\Models\ServiceAccount;
use Illuminate\Http\JsonResponse;

class ServiceAccountController extends Controller
{
    /**
     * List all service accounts with pagination.
     */
    public function index(ListServiceAccountsRequest $request, ListServiceAccountsAction $action): JsonResponse
    {
        $accounts = $action->execute($request->validated());

        return response()->json([
            'success' => true,
            'data' => $accounts,
        ]);
    }

    /**
     * Show a specific service account.
     */
    public function show(string $id, ShowServiceAccountAction $action): JsonResponse
    {
        $account = $action->execute($id);

        return response()->json([
            'success' => true,
            'data' => $account,
        ]);
    }

    /**
     * Manually adjust service account balance.
     */
    public function adjustBalance(AdjustBalanceRequest $request, string $id, AdjustBalanceAction $action): JsonResponse
    {
        $account = ServiceAccount::findOrFail($id);
        
        $data = $action->execute(
            $account,
            $request->validated()['amount'],
            $request->validated()['type'],
            $request->validated()['description'],
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Balance adjusted successfully.',
            'data' => $data,
        ]);
    }

    /**
     * Change service account status.
     */
    public function changeStatus(ChangeStatusRequest $request, string $id, ChangeStatusAction $action): JsonResponse
    {
        $account = ServiceAccount::findOrFail($id);
        
        $data = $action->execute(
            $account,
            $request->validated()['status'],
            $request->validated()['reason'],
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Account status changed successfully.',
            'data' => $data,
        ]);
    }
}
