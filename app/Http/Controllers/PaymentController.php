<?php

namespace App\Http\Controllers;

use App\Actions\Payment\GetPaymentHistoryAction;
use App\Actions\Payment\GetPaymentStatusAction;
use App\Http\Requests\Payment\GetPaymentHistoryRequest;
use App\Http\Requests\Payment\InitiatePaymentRequest;
use App\Models\Payment;
use App\Services\QiCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected QiCardService $qiCardService;

    public function __construct(QiCardService $qiCardService)
    {
        $this->qiCardService = $qiCardService;
    }

    /**
     * Initiate a top-up payment for pay-as-you-go account.
     */
    public function initiate(InitiatePaymentRequest $request): JsonResponse
    {
        $user = auth('api')->user();
        $serviceAccount = $user->getOrCreateServiceAccount();

        // Create payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'service_account_id' => $serviceAccount->id,
            'amount' => $request->amount,
            'currency' => $request->currency ?? 'IQD',
            'type' => 'topup',
            'status' => 'pending',
            'description' => $request->description ?? 'Account top-up',
        ]);

        // Prepare payment data for QiCard
        $paymentData = [
            "requestId" => $payment->id,
            "amount" => $request->amount,
            "currency" => $request->currency ?? 'IQD',
            "locale" => "en_US",
            "finishPaymentUrl" => $request->return_url ?? config('app.url') . '/payment/finish',
            "notificationUrl" => $request->callback_url ?? config('app.url') . '/api/payments/webhook',
            "customerInfo" => [
                "firstName" => $user->name ?? 'Customer',
                "email" => $user->phone_number . '@flow.app',
            ],
            "browserInfo" => [
                "browserAcceptHeader" => $request->header('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8'),
                "browserIp" => $request->ip(),
                "browserJavaEnabled" => false,
                "browserLanguage" => $request->header('Accept-Language', 'en-US'),
                "browserUserAgent" => $request->header('User-Agent', 'Mozilla/5.0')
            ],
        ];

        $result = $this->qiCardService->initiatePayment($paymentData);

        if ($result['success']) {
            $payment->update([
                'qicard_payment_id' => $result['payment_id'] ?? null,
                'qicard_response' => $result['data'] ?? null,
                'status' => 'processing',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'payment' => [
                    'id' => $payment->id,
                    'amount' => (float) $payment->amount,
                    'currency' => $payment->currency,
                    'type' => $payment->type,
                    'status' => $payment->status,
                    'payment_url' => $result['payment_url'],
                ],
                'service_account' => [
                    'id' => $serviceAccount->id,
                    'current_balance' => (float) $serviceAccount->balance,
                ],
            ], 201);
        }

        $payment->update([
            'status' => 'failed',
            'qicard_response' => $result,
        ]);

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'Failed to initiate payment',
            'payment' => [
                'id' => $payment->id,
                'status' => $payment->status,
            ],
        ], 400);
    }

    /**
     * Get payment status.
     */
    public function status(string $id): JsonResponse
    {
        $action = new GetPaymentStatusAction($this->qiCardService);
        $result = $action->execute(auth('api')->user(), $id);
        
        if (!$result['success']) {
            return response()->json($result, $result['status'] ?? 404);
        }
        
        return response()->json($result);
    }

    /**
     * Get payment history for the authenticated user.
     */
    public function history(GetPaymentHistoryRequest $request): JsonResponse
    {
        $action = new GetPaymentHistoryAction();
        $result = $action->execute(auth('api')->user(), $request->validated());
        
        return response()->json($result);
    }

    /**
     * Handle QiCard webhook for payment completion.
     */
    public function webhook(\Illuminate\Http\Request $request): JsonResponse
    {
        Log::info('QiCard webhook received', ['data' => $request->all()]);

        $webhookData = $request->all();
        $result = $this->qiCardService->processWebhook($webhookData);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], 400);
        }

        $data = $result['data'];
        
        // Find payment by request ID (which is our payment UUID)
        $paymentId = $data['request_id'] ?? $data['requestId'] ?? null;
        $payment = Payment::where('id', $paymentId)
            ->orWhere('qicard_payment_id', $data['payment_id'] ?? $data['id'] ?? null)
            ->first();

        if (!$payment) {
            Log::warning('Payment not found for webhook', ['webhook_data' => $data]);
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        $status = $this->mapQiCardStatus($data['status'] ?? 'unknown');
        
        $payment->update([
            'status' => $status,
            'qicard_response' => $data,
            'paid_at' => $status === 'completed' ? now() : null,
        ]);

        // If payment completed and is a top-up, add balance to service account
        if ($status === 'completed' && $payment->type === 'topup' && $payment->serviceAccount) {
            $payment->serviceAccount->addBalance((float) $payment->amount);
            
            Log::info('Balance added to service account', [
                'service_account_id' => $payment->service_account_id,
                'amount' => $payment->amount,
                'new_balance' => $payment->serviceAccount->fresh()->balance,
            ]);
        }

        Log::info('Payment updated from webhook', [
            'payment_id' => $payment->id,
            'status' => $status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Webhook processed successfully',
        ]);
    }

    /**
     * Map QiCard status to our payment status.
     */
    protected function mapQiCardStatus(string $qicardStatus): string
    {
        $statusMap = [
            'pending' => 'pending',
            'processing' => 'processing',
            'completed' => 'completed',
            'success' => 'completed',
            'paid' => 'completed',
            'failed' => 'failed',
            'error' => 'failed',
            'cancelled' => 'cancelled',
            'canceled' => 'cancelled',
            'refunded' => 'cancelled',
        ];

        return $statusMap[strtolower($qicardStatus)] ?? 'pending';
    }
}
