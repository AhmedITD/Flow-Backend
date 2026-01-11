<?php

namespace App\Actions\Payment;

use App\Models\Payment;
use App\Models\User;
use App\Services\QiCardService;

final class GetPaymentStatusAction
{
    public function __construct(
        private QiCardService $qiCardService
    ) {}
    
    /**
     * Get payment status and verify with payment gateway if needed.
     */
    public function execute(User $user, string $paymentId): array
    {
        $payment = Payment::where('id', $paymentId)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$payment) {
            return [
                'success' => false,
                'error' => 'Payment not found',
                'status' => 404,
            ];
        }
        
        // Verify with QiCard if payment is still processing
        if ($payment->status === 'processing' && $payment->qicard_payment_id) {
            $verification = $this->qiCardService->verifyPayment($payment->qicard_payment_id);
            
            if ($verification['success']) {
                $qicardData = $verification['data'];
                $status = $this->mapQiCardStatus($qicardData['status'] ?? 'unknown');
                
                $payment->update([
                    'status' => $status,
                    'qicard_response' => $qicardData,
                    'paid_at' => $status === 'completed' ? now() : null,
                ]);
            }
        }
        
        return [
            'success' => true,
            'payment' => [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'description' => $payment->description,
                'payment_method' => $payment->payment_method,
                'paid_at' => $payment->paid_at,
                'created_at' => $payment->created_at,
                'subscription_id' => $payment->subscription_id,
            ],
        ];
    }
    
    /**
     * Map QiCard status to our payment status.
     */
    private function mapQiCardStatus(string $qicardStatus): string
    {
        return match(strtolower($qicardStatus)) {
            'completed', 'success', 'paid' => 'completed',
            'failed', 'error' => 'failed',
            'cancelled', 'canceled', 'refunded' => 'cancelled',
            default => 'processing',
        };
    }
}
