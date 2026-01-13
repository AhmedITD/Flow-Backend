<?php

namespace App\Actions\Balance;

use App\Models\Payment;
use App\Models\ServiceAccount;
use App\Models\User;

final class TopUpBalanceAction
{
    /**
     * Initiate a top-up payment for a service account.
     */
    public function execute(User $user, ServiceAccount $serviceAccount, array $data): array
    {
        // Validate minimum top-up amount
        $minTopUp = config('paygo.min_topup_amount', 1000);
        if ($data['amount'] < $minTopUp) {
            return [
                'success' => false,
                'message' => "Minimum top-up amount is {$minTopUp} {$serviceAccount->currency}",
            ];
        }

        // Create pending payment
        $payment = Payment::createTopup(
            $user,
            $serviceAccount,
            $data['amount'],
            $data['currency'] ?? $serviceAccount->currency,
            $data['description'] ?? 'Account top-up',
            $data['metadata'] ?? null
        );

        // Generate payment URL (integrate with QiCard or other payment gateway)
        $paymentUrl = $this->generatePaymentUrl($payment, $data);

        return [
            'success' => true,
            'message' => 'Top-up payment initiated',
            'payment' => [
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'created_at' => $payment->created_at->toIso8601String(),
            ],
            'payment_url' => $paymentUrl,
        ];
    }

    private function generatePaymentUrl(Payment $payment, array $data): string
    {
        // This would integrate with QiCard or another payment gateway
        // For now, return a placeholder URL
        $baseUrl = config('app.url');
        $returnUrl = $data['return_url'] ?? "{$baseUrl}/payment/return";
        $callbackUrl = $data['callback_url'] ?? "{$baseUrl}/api/payments/webhook";

        return "{$baseUrl}/payment/checkout/{$payment->id}?" . http_build_query([
            'return_url' => $returnUrl,
            'callback_url' => $callbackUrl,
        ]);
    }
}
