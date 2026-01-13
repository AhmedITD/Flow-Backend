<?php

namespace App\Actions\Admin\ServiceAccount;

use App\Models\ServiceAccount;

final class ShowServiceAccountAction
{
    /**
     * Show a specific service account with related data.
     */
    public function execute(string $id): ServiceAccount
    {
        return ServiceAccount::with([
            'user:id,name,phone_number,role,created_at',
            'usageRecords' => fn($q) => $q->latest()->limit(10),
            'payments' => fn($q) => $q->latest()->limit(10),
            'invoices' => fn($q) => $q->latest()->limit(5),
        ])
        ->withSum('usageRecords', 'tokens_used')
        ->withSum('usageRecords', 'cost')
        ->findOrFail($id);
    }
}
