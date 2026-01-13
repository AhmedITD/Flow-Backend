<?php

namespace App\Actions\Balance;

use App\Models\ServiceAccount;
use App\Models\User;

final class GetTransactionHistoryAction
{
    /**
     * Get transaction history for a service account.
     */
    public function execute(User $user, ?string $serviceAccountId = null, array $filters = []): array
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

        $perPage = $filters['per_page'] ?? 20;
        $type = $filters['type'] ?? null; // 'payments', 'usage', or null for both

        $transactions = collect();

        // Get payments
        if (!$type || $type === 'payments') {
            $payments = $serviceAccount->payments()
                ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'transaction_type' => 'payment',
                    'type' => $p->type,
                    'amount' => $p->type === 'topup' ? (float) $p->amount : -(float) $p->amount,
                    'currency' => $p->currency,
                    'status' => $p->status,
                    'description' => $p->description,
                    'date' => $p->paid_at ?? $p->created_at,
                ]);

            $transactions = $transactions->concat($payments);
        }

        // Get usage records
        if (!$type || $type === 'usage') {
            $usage = $serviceAccount->usageRecords()
                ->when(isset($filters['service_type']), fn($q) => $q->where('service_type', $filters['service_type']))
                ->orderBy('recorded_at', 'desc')
                ->get()
                ->map(fn($u) => [
                    'id' => $u->id,
                    'transaction_type' => 'usage',
                    'type' => 'debit',
                    'amount' => -(float) $u->cost,
                    'currency' => $serviceAccount->currency,
                    'status' => 'completed',
                    'description' => "Usage: {$u->tokens_used} tokens ({$u->service_type})",
                    'service_type' => $u->service_type,
                    'tokens_used' => $u->tokens_used,
                    'date' => $u->recorded_at,
                ]);

            $transactions = $transactions->concat($usage);
        }

        // Sort by date descending and paginate
        $sorted = $transactions->sortByDesc('date')->values();
        $page = $filters['page'] ?? 1;
        $paginated = $sorted->forPage($page, $perPage);

        return [
            'success' => true,
            'transactions' => $paginated->values()->toArray(),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $sorted->count(),
                'last_page' => ceil($sorted->count() / $perPage),
            ],
        ];
    }
}
