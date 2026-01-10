<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpiqService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('otpiq.base_url', 'https://api.otpiq.com/api');
        $this->apiKey = config('otpiq.api_key');
    }

    /**
     * Send SMS/WhatsApp verification code
     *
     * @param string $phoneNumber Phone number in international format (e.g., 9647716418740)
     * @param string $verificationCode 6-digit verification code
     * @param string $smsType Type of SMS (verification, login, register, etc.)
     * @param string $provider Provider to use (whatsapp-sms, sms)
     * @return array
     */
    public function sendVerificationCode(
        string $phoneNumber,
        string $verificationCode,
        string $smsType = 'verification',
        string $provider = 'whatsapp-sms'
    ): array {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/sms", [
                'phoneNumber' => $phoneNumber,
                'smsType' => $smsType,
                'provider' => $provider,
                'verificationCode' => $verificationCode,
            ]);

            if ($response->successful()) {
                Log::info('OTPIQ SMS sent successfully', [
                    'phone_number' => $phoneNumber,
                    'sms_type' => $smsType,
                    'provider' => $provider,
                ]);

                return [
                    'success' => true,
                    'message' => 'Verification code sent successfully',
                    'data' => $response->json(),
                ];
            }

            Log::error('OTPIQ SMS failed', [
                'phone_number' => $phoneNumber,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send verification code',
                'error' => $response->json() ?? $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('OTPIQ service exception', [
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send verification code',
                'error' => $e->getMessage(),
                'status' => 500,
            ];
        }
    }

    /**
     * Format phone number to international format
     * Removes +, spaces, and ensures proper format
     *
     * @param string $phoneNumber
     * @return string
     */
    public function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters except leading +
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);

        // Remove leading + if present
        $phoneNumber = ltrim($phoneNumber, '+');

        return $phoneNumber;
    }
}
