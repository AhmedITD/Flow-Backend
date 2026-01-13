<?php

namespace App\Actions\Usage;

use App\Models\ServiceAccount;
use Illuminate\Support\Facades\DB;

final class GetUsageSummaryAction
{
    /**
     * Get usage summary for a service account.
     */
    public function execute(ServiceAccount $serviceAccount, ?string $period = 'month'): array
    {
        $now = now();

        // Determine date range based on period
        switch ($period) {
            case 'day':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'week':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                break;
            case 'year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                break;
            case 'month':
            default:
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
        }

        // Get summary by service type
        $byService = $serviceAccount->usageRecords()
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->select('service_type')
            ->selectRaw('SUM(tokens_used) as tokens_used')
            ->selectRaw('SUM(cost) as total_cost')
            ->selectRaw('COUNT(*) as request_count')
            ->groupBy('service_type')
            ->get()
            ->keyBy('service_type')
            ->map(fn($item) => [
                'tokens_used' => (int) $item->tokens_used,
                'total_cost' => (float) $item->total_cost,
                'request_count' => (int) $item->request_count,
            ])
            ->toArray();

        // Get daily breakdown for the period
        $dailyBreakdown = $serviceAccount->usageRecords()
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->selectRaw('DATE(recorded_at) as date')
            ->selectRaw('SUM(tokens_used) as tokens_used')
            ->selectRaw('SUM(cost) as total_cost')
            ->selectRaw('COUNT(*) as request_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'tokens_used' => (int) $item->tokens_used,
                'total_cost' => (float) $item->total_cost,
                'request_count' => (int) $item->request_count,
            ])
            ->toArray();

        // Calculate totals
        $totalTokens = array_sum(array_column($byService, 'tokens_used'));
        $totalCost = array_sum(array_column($byService, 'total_cost'));
        $totalRequests = array_sum(array_column($byService, 'request_count'));

        return [
            'success' => true,
            'summary' => [
                'period' => $period,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'totals' => [
                    'tokens_used' => $totalTokens,
                    'total_cost' => $totalCost,
                    'request_count' => $totalRequests,
                    'currency' => $serviceAccount->currency,
                ],
                'by_service' => $byService,
                'daily_breakdown' => $dailyBreakdown,
            ],
            'balance' => [
                'current' => (float) $serviceAccount->balance,
                'available' => $serviceAccount->available_credit,
            ],
        ];
    }
}
