<?php

namespace App\Actions\Admin\ServiceAccount;

use App\Models\Payment;
use App\Models\ServiceAccount;
use Illuminate\Support\Str;

final class AdjustBalanceAction
{
    /**
     * Manually adjust service account balance.
     */
    public function execute(ServiceAccount $account, float $amount, string $type, string $description, int $adjustedBy): array
    {
        $oldBalance = $account->balance;
        $newBalance = $oldBalance + $amount;

        // Create payment record for audit trail
        $payment = Payment::create([
            'user_id' => $account->user_id,
            'service_account_id' => $account->id,
            'transaction_id' => 'ADJ-' . Str::upper(Str::random(12)),
            'amount' => abs($amount),
            'currency' => $account->currency,
            'type' => $type,
            'status' => 'completed',
            'description' => $description,
            'payment_method' => 'admin_adjustment',
            'metadata' => [
                'adjusted_by' => $adjustedBy,
                'old_balance' => $oldBalance,
                'new_balance' => $newBalance,
            ],
            'paid_at' => now(),
        ]);

        // Update balance
        $account->update(['balance' => $newBalance]);

        return [
            'old_balance' => $oldBalance,
            'adjustment' => $amount,
            'new_balance' => $newBalance,
            'payment_id' => $payment->id,
        ];
    }
}
