<?php

namespace App\Actions\Auth;

use App\Models\PhoneVerificationCode;
use App\Models\User;
use App\Services\OtpiqService;
use Illuminate\Support\Facades\Validator;

final class SendPhoneVerificationAction
{
    public function __construct(
        private OtpiqService $otpiqService
    ) {}

    /**
     * Send verification code to phone number for registration.
     * Phone number must NOT exist (user is not registered yet).
     */
    public function execute(array $data, ?string $ipAddress = null): array
    {
        $validator = Validator::make($data, [
            'phone_number' => 'required|string|regex:/^[0-9+\s\-()]+$/|min:10|max:20',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
                'status' => 422,
            ];
        }

        $phoneNumber = $this->otpiqService->formatPhoneNumber($data['phone_number']);

        // Validate that phone number does NOT exist (for registration)
        if (User::where('phone_number', $phoneNumber)->exists()) {
            return [
                'success' => false,
                'error' => 'Phone number is already registered',
                'status' => 422,
            ];
        }

        // Check rate limiting (max 5 codes per phone per hour)
        $recentCodes = PhoneVerificationCode::where('phone_number', $phoneNumber)
            ->where('created_at', '>', now()->subHour())
            ->count();

        if ($recentCodes >= config('otpiq.code.max_attempts', 5)) {
            return [
                'success' => false,
                'error' => 'Too many verification requests. Please try again later.',
                'status' => 429,
            ];
        }

        // Create verification code
        $verificationCode = PhoneVerificationCode::createForPhone(
            $phoneNumber,
            $ipAddress,
            config('otpiq.code.expires_in_minutes', 10)
        );

        // Send via OTPIQ
        $smsResult = $this->otpiqService->sendVerificationCode(
            $phoneNumber,
            $verificationCode->code,
            'verification',
            config('otpiq.default_provider', 'whatsapp-sms')
        );

        if (!$smsResult['success']) {
            // Delete the code if SMS failed to send
            $verificationCode->delete();

            return [
                'success' => false,
                'error' => 'Failed to send verification code. Please try again.',
                'details' => $smsResult['error'] ?? null,
                'status' => $smsResult['status'] ?? 500,
            ];
        }

        return [
            'success' => true,
            'message' => 'Verification code sent successfully',
            'phone_number' => $phoneNumber,
            'expires_in_minutes' => config('otpiq.code.expires_in_minutes', 10),
            'status' => 200,
        ];
    }
}
