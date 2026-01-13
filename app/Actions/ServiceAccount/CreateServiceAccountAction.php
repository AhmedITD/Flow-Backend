<?php

namespace App\Actions\ServiceAccount;

use App\Models\ServiceAccount;
use App\Models\User;

final class CreateServiceAccountAction
{
    /**
     * Create a new service account for a user.
     */
    public function execute(User $user, array $data = []): array
    {
        // Check if user already has an active service account
        $existingAccount = ServiceAccount::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($existingAccount) {
            return [
                'success' => false,
                'message' => 'User already has an active service account',
                'service_account' => $this->formatServiceAccount($existingAccount),
            ];
        }

        $serviceAccount = ServiceAccount::create([
            'user_id' => $user->id,
            'status' => 'active',
            'balance' => $data['initial_balance'] ?? 0,
            'currency' => $data['currency'] ?? 'IQD',
            'credit_limit' => $data['credit_limit'] ?? 0,
            'metadata' => $data['metadata'] ?? null,
        ]);

        return [
            'success' => true,
            'message' => 'Service account created successfully',
            'service_account' => $this->formatServiceAccount($serviceAccount),
        ];
    }

    private function formatServiceAccount(ServiceAccount $account): array
    {
        return [
            'id' => $account->id,
            'user_id' => $account->user_id,
            'status' => $account->status,
            'balance' => (float) $account->balance,
            'currency' => $account->currency,
            'credit_limit' => (float) $account->credit_limit,
            'available_credit' => $account->available_credit,
            'created_at' => $account->created_at->toIso8601String(),
        ];
    }
}
