<?php

namespace App\Actions\Auth;

use App\Models\PhoneVerificationCode;
use App\Services\OtpiqService;
use Illuminate\Support\Facades\Validator;

final class VerifyPhoneCodeAction
{
    public function __construct(
        private OtpiqService $otpiqService
    ) {}

    /**
     * Verify phone verification code for registration
     */
    public function execute(array $data): array
    {
        $validator = Validator::make($data, [
            'phone_number' => 'required|string|regex:/^[0-9+\s\-()]+$/|min:10|max:20',
            'code' => 'required|string|size:6|regex:/^[0-9]+$/',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
                'status' => 422,
            ];
        }

        $phoneNumber = $this->otpiqService->formatPhoneNumber($data['phone_number']);
        $code = $data['code'];

        // Find valid code
        $verificationCode = PhoneVerificationCode::findValidCode($phoneNumber, $code);

        if (!$verificationCode) {
            return [
                'success' => false,
                'error' => 'Invalid or expired verification code',
                'status' => 400,
            ];
        }

        // Mark code as used
        $verificationCode->markAsUsed();

        return [
            'success' => true,
            'message' => 'Phone number verified successfully',
            'phone_number' => $phoneNumber,
            'verified_at' => $verificationCode->verified_at,
            'status' => 200,
        ];
    }
}
