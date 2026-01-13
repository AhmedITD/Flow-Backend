<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\ServiceAccount;
use App\Models\UsageRecord;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get usage analytics.
     */
    public function usage(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $serviceType = $request->input('service_type');
        $startDate = now()->subDays($days);

        $query = UsageRecord::query()
            ->where('recorded_at', '>=', $startDate);

        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }

        // Daily usage
        $dailyUsage = (clone $query)
            ->select([
                DB::raw('DATE(recorded_at) as date'),
                DB::raw('SUM(tokens_used) as total_tokens'),
                DB::raw('SUM(cost) as total_cost'),
                DB::raw('COUNT(*) as request_count'),
            ])
            ->groupBy(DB::raw('DATE(recorded_at)'))
            ->orderBy('date')
            ->get();

        // Usage by service type
        $usageByService = (clone $query)
            ->select([
                'service_type',
                DB::raw('SUM(tokens_used) as total_tokens'),
                DB::raw('SUM(cost) as total_cost'),
                DB::raw('COUNT(*) as request_count'),
            ])
            ->groupBy('service_type')
            ->get();

        // Top users by usage
        $topUsers = (clone $query)
            ->select([
                'service_account_id',
                DB::raw('SUM(tokens_used) as total_tokens'),
                DB::raw('SUM(cost) as total_cost'),
            ])
            ->groupBy('service_account_id')
            ->orderByDesc('total_tokens')
            ->limit(10)
            ->with('serviceAccount.user:id,name,phone_number')
            ->get();

        // Summary
        $summary = [
            'total_tokens' => $query->sum('tokens_used'),
            'total_cost' => $query->sum('cost'),
            'total_requests' => $query->count(),
            'unique_accounts' => $query->distinct('service_account_id')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'daily_usage' => $dailyUsage,
                'usage_by_service' => $usageByService,
                'top_users' => $topUsers,
            ],
        ]);
    }

    /**
     * Get revenue analytics.
     */
    public function revenue(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $startDate = now()->subDays($days);

        // Daily revenue (from payments)
        $dailyRevenue = Payment::query()
            ->where('status', 'completed')
            ->where('type', 'topup')
            ->where('paid_at', '>=', $startDate)
            ->select([
                DB::raw('DATE(paid_at) as date'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as payment_count'),
            ])
            ->groupBy(DB::raw('DATE(paid_at)'))
            ->orderBy('date')
            ->get();

        // Revenue by payment method
        $revenueByMethod = Payment::query()
            ->where('status', 'completed')
            ->where('type', 'topup')
            ->where('paid_at', '>=', $startDate)
            ->select([
                'payment_method',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as payment_count'),
            ])
            ->groupBy('payment_method')
            ->get();

        // Summary
        $summary = [
            'total_revenue' => Payment::where('status', 'completed')
                ->where('type', 'topup')
                ->where('paid_at', '>=', $startDate)
                ->sum('amount'),
            'total_payments' => Payment::where('status', 'completed')
                ->where('type', 'topup')
                ->where('paid_at', '>=', $startDate)
                ->count(),
            'total_refunds' => Payment::where('status', 'completed')
                ->where('type', 'refund')
                ->where('paid_at', '>=', $startDate)
                ->sum('amount'),
        ];

        // Outstanding balance (total balance across all accounts)
        $summary['outstanding_balance'] = ServiceAccount::where('status', 'active')
            ->sum('balance');

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'daily_revenue' => $dailyRevenue,
                'revenue_by_method' => $revenueByMethod,
            ],
        ]);
    }

    /**
     * Get user analytics.
     */
    public function users(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $startDate = now()->subDays($days);

        // Daily registrations
        $dailyRegistrations = User::query()
            ->where('created_at', '>=', $startDate)
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
            ])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // User summary
        $summary = [
            'total_users' => User::count(),
            'new_users' => User::where('created_at', '>=', $startDate)->count(),
            'active_accounts' => ServiceAccount::where('status', 'active')->count(),
            'suspended_accounts' => ServiceAccount::where('status', 'suspended')->count(),
            'users_with_balance' => ServiceAccount::where('balance', '>', 0)->count(),
            'admins' => User::where('role', 'admin')->count(),
        ];

        // Users by role
        $usersByRole = User::query()
            ->select([
                'role',
                DB::raw('COUNT(*) as count'),
            ])
            ->groupBy('role')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'daily_registrations' => $dailyRegistrations,
                'users_by_role' => $usersByRole,
            ],
        ]);
    }
}
