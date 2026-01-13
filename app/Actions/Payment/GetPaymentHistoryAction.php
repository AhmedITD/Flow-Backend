<?php

namespace App\Actions\Payment;

use App\Models\User;

final class GetPaymentHistoryAction
{
    /**
     * Get payment history for a user.
     */
    public function execute(User $user, array $filters = []): array
    {
        $query = $user->payments();
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        $perPage = $filters['per_page'] ?? 15;
        $payments = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return [
            'success' => true,
            'payments' => $payments->map(fn($payment) => [
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'currency' => $payment->currency,
                'type' => $payment->type,
                'status' => $payment->status,
                'description' => $payment->description,
                'paid_at' => $payment->paid_at,
                'created_at' => $payment->created_at,
                'service_account_id' => $payment->service_account_id,
            ]),
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ];
    }
}
