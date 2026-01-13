<?php

namespace App\Actions\Balance;

use App\Models\ServiceAccount;
use App\Models\User;

final class GetBalanceAction
{
    /**
     * Get balance information for a service account.
     */
    public function execute(User $user, ?string $serviceAccountId = null): array
    {
        $query = ServiceAccount::where('user_id', $user->id);

        if ($serviceAccountId) {
            $serviceAccount = $query->where('id', $serviceAccountId)->first();
        } else {
            $serviceAccount = $query->where('status', 'active')->first();
        }

        if (!$serviceAccount) {
            return [
                'success' => false,
                'message' => 'Service account not found',
            ];
        }

        // Get recent transactions
        $recentPayments = $serviceAccount->payments()
            ->where('status', 'completed')
            ->orderBy('paid_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'type' => $p->type,
                'amount' => (float) $p->amount,
                'currency' => $p->currency,
                'paid_at' => $p->paid_at?->toIso8601String(),
            ]);

        // Calculate spending for different periods
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        return [
            'success' => true,
            'balance' => [
                'current' => (float) $serviceAccount->balance,
                'credit_limit' => (float) $serviceAccount->credit_limit,
                'available' => $serviceAccount->available_credit,
                'currency' => $serviceAccount->currency,
            ],
            'spending' => [
                'today' => $serviceAccount->getTotalCost(null, $today),
                'this_week' => $serviceAccount->getTotalCost(null, $thisWeek),
                'this_month' => $serviceAccount->getTotalCost(null, $thisMonth),
            ],
            'recent_payments' => $recentPayments,
        ];
    }
}
