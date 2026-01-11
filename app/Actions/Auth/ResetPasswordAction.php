<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\OtpiqService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

final class ResetPasswordAction
{
    public function __construct(
        private OtpiqService $otpiqService
    ) {}

    /**
     * Reset user password after OTP verification.
     * User must have requested password reset via /api/auth/forgot-password first.
     */
    public function execute(array $data): array
    {
        $validator = Validator::make($data, [
            'phone_number' => 'required|string|regex:/^[0-9+\s\-()]+$/|min:10|max:20',
            'code' => 'required|string|size:6|regex:/^[0-9]+$/',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
                'status' => 422,
            ];
        }

        $phoneNumber = $this->otpiqService->formatPhoneNumber($data['phone_number']);

        // Find user
        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user) {
            return [
                'success' => false,
                'error' => 'Phone number is not registered',
                'status' => 404,
            ];
        }

        // Verify OTP code (must have been sent via /api/auth/forgot-password endpoint first)
        $verifyOtpAction = new VerifyPhoneCodeAction($this->otpiqService);
        $otpResult = $verifyOtpAction->execute([
            'phone_number' => $phoneNumber,
            'code' => $data['code'],
        ]);

        if (!$otpResult['success']) {
            return $otpResult;
        }

        // Update password
        $user->password = $data['password']; // Automatically hashed by User model cast
        $user->save();

        // Optionally invalidate all existing tokens/sessions
        // You might want to implement this if using token revocation

        return [
            'success' => true,
            'message' => 'Password has been reset successfully',
            'status' => 200,
        ];
    }
}
