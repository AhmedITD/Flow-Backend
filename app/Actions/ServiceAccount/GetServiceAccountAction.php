<?php

namespace App\Actions\ServiceAccount;

use App\Models\ServiceAccount;
use App\Models\User;

final class GetServiceAccountAction
{
    /**
     * Get service account details with usage summary.
     */
    public function execute(User $user, ?string $serviceAccountId = null): array
    {
        $query = ServiceAccount::where('user_id', $user->id);

        if ($serviceAccountId) {
            $serviceAccount = $query->where('id', $serviceAccountId)->first();
        } else {
            // Get the active service account
            $serviceAccount = $query->where('status', 'active')->first();
        }

        if (!$serviceAccount) {
            return [
                'success' => false,
                'message' => 'Service account not found',
            ];
        }

        // Get usage summary for current month
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $usageSummary = [
            'current_month' => [
                'tokens_used' => $serviceAccount->getTokensUsed(null, $startOfMonth, $endOfMonth),
                'total_cost' => $serviceAccount->getTotalCost(null, $startOfMonth, $endOfMonth),
            ],
            'by_service' => [],
        ];

        // Get breakdown by service type
        foreach (['call_center', 'hr'] as $serviceType) {
            $usageSummary['by_service'][$serviceType] = [
                'tokens_used' => $serviceAccount->getTokensUsed($serviceType, $startOfMonth, $endOfMonth),
                'total_cost' => $serviceAccount->getTotalCost($serviceType, $startOfMonth, $endOfMonth),
            ];
        }

        return [
            'success' => true,
            'service_account' => [
                'id' => $serviceAccount->id,
                'user_id' => $serviceAccount->user_id,
                'status' => $serviceAccount->status,
                'balance' => (float) $serviceAccount->balance,
                'currency' => $serviceAccount->currency,
                'credit_limit' => (float) $serviceAccount->credit_limit,
                'available_credit' => $serviceAccount->available_credit,
                'created_at' => $serviceAccount->created_at->toIso8601String(),
                'updated_at' => $serviceAccount->updated_at->toIso8601String(),
            ],
            'usage_summary' => $usageSummary,
        ];
    }
}
