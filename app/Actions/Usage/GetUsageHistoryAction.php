<?php

namespace App\Actions\Usage;

use App\Models\ServiceAccount;

final class GetUsageHistoryAction
{
    /**
     * Get usage history for a service account.
     */
    public function execute(ServiceAccount $serviceAccount, array $filters = []): array
    {
        $query = $serviceAccount->usageRecords();

        // Filter by service type
        if (isset($filters['service_type'])) {
            $query->where('service_type', $filters['service_type']);
        }

        // Filter by date range
        if (isset($filters['from'])) {
            $query->where('recorded_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->where('recorded_at', '<=', $filters['to']);
        }

        // Filter by action type
        if (isset($filters['action_type'])) {
            $query->where('action_type', $filters['action_type']);
        }

        // Order by recorded_at descending
        $query->orderBy('recorded_at', 'desc');

        // Paginate
        $perPage = $filters['per_page'] ?? 20;
        $records = $query->paginate($perPage);

        // Calculate totals for the filtered period
        $totalsQuery = $serviceAccount->usageRecords();
        
        if (isset($filters['service_type'])) {
            $totalsQuery->where('service_type', $filters['service_type']);
        }
        if (isset($filters['from'])) {
            $totalsQuery->where('recorded_at', '>=', $filters['from']);
        }
        if (isset($filters['to'])) {
            $totalsQuery->where('recorded_at', '<=', $filters['to']);
        }

        $totalTokens = $totalsQuery->sum('tokens_used');
        $totalCost = $totalsQuery->sum('cost');

        return [
            'success' => true,
            'usage_records' => $records->map(fn($r) => [
                'id' => $r->id,
                'service_type' => $r->service_type->value,
                'tokens_used' => $r->tokens_used,
                'cost' => (float) $r->cost,
                'action_type' => $r->action_type,
                'resource_id' => $r->resource_id,
                'recorded_at' => $r->recorded_at->toIso8601String(),
            ])->toArray(),
            'totals' => [
                'tokens_used' => (int) $totalTokens,
                'total_cost' => (float) $totalCost,
            ],
            'pagination' => [
                'current_page' => $records->currentPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
                'last_page' => $records->lastPage(),
            ],
        ];
    }
}
