<?php

namespace App\Actions\Admin\ServiceAccount;

use App\Models\ServiceAccount;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListServiceAccountsAction
{
    /**
     * List all service accounts with filters and pagination.
     */
    public function execute(array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 15;
        $status = $filters['status'] ?? null;
        $minBalance = $filters['min_balance'] ?? null;
        $maxBalance = $filters['max_balance'] ?? null;

        $query = ServiceAccount::query()
            ->with(['user:id,name,phone_number'])
            ->withSum('usageRecords', 'tokens_used')
            ->withSum('usageRecords', 'cost');

        if ($status) {
            $query->where('status', $status);
        }

        if ($minBalance !== null) {
            $query->where('balance', '>=', $minBalance);
        }

        if ($maxBalance !== null) {
            $query->where('balance', '<=', $maxBalance);
        }

        return $query->latest()->paginate($perPage);
    }
}
