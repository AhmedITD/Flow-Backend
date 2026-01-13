<?php

namespace App\Actions\Admin\ServiceAccount;

use App\Models\ServiceAccount;

final class ChangeStatusAction
{
    /**
     * Change service account status.
     */
    public function execute(ServiceAccount $account, string $status, string $reason, int $changedBy): array
    {
        $oldStatus = $account->status;
        
        $account->update([
            'status' => $status,
            'metadata' => array_merge($account->metadata ?? [], [
                'status_history' => array_merge($account->metadata['status_history'] ?? [], [
                    [
                        'from' => $oldStatus,
                        'to' => $status,
                        'reason' => $reason,
                        'changed_by' => $changedBy,
                        'changed_at' => now()->toIso8601String(),
                    ],
                ]),
            ]),
        ]);

        return [
            'old_status' => $oldStatus,
            'new_status' => $status,
        ];
    }
}
