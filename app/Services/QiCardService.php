<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class QiCardService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $merchantId;
    protected bool $isSandbox;

    public function __construct()
    {
        $this->baseUrl = config('services.qicard.base_url', 'https://api-gate.qi.iq');
        $this->xTerminalId = config('services.qicard.x_terminal_id');
        $this->username = config('services.qicard.username');
        $this->password = config('services.qicard.password');
        $this->isSandbox = config('services.qicard.sandbox', false);

        if ($this->isSandbox) {
            $this->baseUrl = 'https://uat-sandbox-3ds-api.qi.iq/api/v1';
            $this->xTerminalId = '237984';
            $this->username = 'paymentgatewaytest';
            $this->password = 'WHaNFE5C3qlChqNbAzH4';
        }
    }

    /**
     * Initialize a payment transaction
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function initiatePayment(array $data): array
    {
        try {
            $payload = [
                "requestId"=> $data['requestId'],
                "amount"=> $data['amount'],
                "currency"=> $data['currency'] ?? 'IQD',
                "locale"=> "en_US",
                "finishPaymentUrl"=> $data['finishPaymentUrl'] ?? config('app.url') . '/payment/finish',
                "notificationUrl"=> $data['notificationUrl'] ?? config('app.url') . '/api/payment/webhook',
                "customerInfo"=> [
                    "firstName"=> $data['customerInfo']['firstName'] ?? 'Customer',
                    // "middleName"=> $data['customerInfo']['middleName'] ?? '',
                    // "lastName"=> $data['customerInfo']['lastName'] ?? '',
                    // "phone"=> $user->phone ?? '',
                    "email"=> $data['customerInfo']['email'],
                    // "accountId"=> (string) $data['customerInfo']['accountId'],
                    // "accountNumber"=> $data['customerInfo']['accountNumber'] ?? '',
                    // "address"=> $data['customerInfo']['address'] ?? '',  
                    // "city"=> $data['customerInfo']['city'] ?? '',
                    // "provinceCode"=> $data['customerInfo']['provinceCode'] ?? '',
                    // "countryCode"=> $data['customerInfo']['countryCode'] ?? 'IQ',
                    // "postalCode"=> $data['customerInfo']['postalCode'] ?? '',
                    // "birthDate"=> $data['customerInfo']['birthDate'] ?? '',
                    // "identificationType"=> $data['customerInfo']['identificationType'] ?? '00',
                    // "identificationNumber"=> $data['customerInfo']['identificationNumber'] ?? '',
                    // "identificationCountryCode"=> $data['customerInfo']['identificationCountryCode'] ?? 'IQ',
                    // "identificationExpirationDate"=> $data['customerInfo']['identificationExpirationDate'] ?? '',
                    // "nationality"=> $data['customerInfo']['nationality'] ?? 'IQ',
                    // "countryOfBirth"=> $data['customerInfo']['countryOfBirth'] ?? 'IQ',
                    // "fundSource"=> "01",
                    // "participantId"=> (string) $data['customerInfo']['participantId'],
                    // "additionalMessage"=> $data['customerInfo']['additionalMessage'] ?? '',
                    // "transactionReason"=> "00",
                    // "claimCode"=> (string) $data['claimCode']
                ],
                "browserInfo"=> [
                    "browserAcceptHeader"=> $data['browserInfo']['browserAcceptHeader'] ?? $request->header('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8'),
                    "browserIp"=> $data['browserInfo']['browserIp'] ?? $request->ip(),
                    "browserJavaEnabled"=> $data['browserInfo']['browserJavaEnabled'] ?? false,
                    "browserLanguage"=> $data['browserInfo']['browserLanguage'] ?? $request->header('Accept-Language', 'en-US'),
                    "browserColorDepth"=> "24",
                    "browserScreenWidth"=> "1024",
                    "browserScreenHeight"=> "768",
                    "browserTZ"=> (string) date('Z'),
                    "browserUserAgent"=> $data['browserInfo']['browserUserAgent'] ?? $request->header('User-Agent', 'Mozilla/5.0')
                ],
                "additionalInfo"=> [
                    "description"=> $data['additionalInfo']['description'] ?? ''
                ]
            ];
            Log::info('QiCard payment payload', ['payload' => $payload]);
            Log::info('QiCard payment username', ['username' => $this->username]);
            Log::info('QiCard payment password', ['password' => $this->password]);
            Log::info('QiCard payment xTerminalId', ['xTerminalId' => $this->xTerminalId]);
            Log::info('QiCard payment baseUrl', ['baseUrl' => $this->baseUrl]);
            Log::info('Basic ' . base64_encode($this->username . ':' . $this->password));
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->password),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-Terminal-Id' => $this->xTerminalId,
            ])->post($this->baseUrl . '/payment', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('QiCard payment initiated', ['response' => $responseData]);
                return [
                    'success' => true,
                    'data' => $responseData,
                    'payment_url' => $responseData['formUrl'] ?? $responseData['payment_url'] ?? null,
                    'payment_id' => $responseData['id'] ?? null,
                ];
            }

            $errorResponse = $response->json();
            $errorMessage = 'Payment initiation failed';
            
            if (isset($errorResponse['error'])) {
                $errorMessage = $errorResponse['error']['description'] ?? $errorResponse['error']['message'] ?? $errorMessage;
            } elseif (isset($errorResponse['message'])) {
                $errorMessage = $errorResponse['message'];
            }

            Log::error('QiCard payment initiation failed', [
                'status' => $response->status(),
                'response' => $response->body(),
                'error' => $errorResponse,
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'error_code' => $errorResponse['error']['code'] ?? null,
                'status' => $response->status(),
            ];
        } catch (Exception $e) {
            Log::error('QiCard payment exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Verify a payment transaction
     *
     * @param string $paymentId
     * @return array
     */
    public function verifyPayment(string $paymentId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/api/v1/payments/' . $paymentId);

            if ($response->successful()) {
                $responseData = $response->json();
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Payment verification failed',
                'status' => $response->status(),
            ];
        } catch (Exception $e) {
            Log::error('QiCard payment verification exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process webhook notification
     *
     * @param array $webhookData
     * @return array
     */
    public function processWebhook(array $webhookData): array
    {
        try {
            // Verify webhook signature if provided
            if (isset($webhookData['signature'])) {
                $isValid = $this->verifyWebhookSignature($webhookData);
                if (!$isValid) {
                    return [
                        'success' => false,
                        'error' => 'Invalid webhook signature',
                    ];
                }
            }

            return [
                'success' => true,
                'data' => $webhookData,
            ];
        } catch (Exception $e) {
            Log::error('QiCard webhook processing exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify webhook signature
     *
     * @param array $webhookData
     * @return bool
     */
    protected function verifyWebhookSignature(array $webhookData): bool
    {
        // Implement signature verification based on QiCard documentation
        // This is a placeholder - adjust based on actual QiCard webhook signature method
        $secret = config('services.qicard.webhook_secret');
        
        if (!isset($webhookData['signature']) || !$secret) {
            return false;
        }

        // Example signature verification (adjust based on actual implementation)
        $expectedSignature = hash_hmac('sha256', json_encode($webhookData['data'] ?? []), $secret);
        
        return hash_equals($expectedSignature, $webhookData['signature']);
    }

    /**
     * Refund a payment
     *
     * @param string $paymentId
     * @param float|null $amount
     * @return array
     */
    public function refundPayment(string $paymentId, ?float $amount = null): array
    {
        try {
            $payload = [
                'payment_id' => $paymentId,
            ];

            if ($amount !== null) {
                $payload['amount'] = $amount;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/api/v1/payments/' . $paymentId . '/refund', $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Refund failed',
                'status' => $response->status(),
            ];
        } catch (Exception $e) {
            Log::error('QiCard refund exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

