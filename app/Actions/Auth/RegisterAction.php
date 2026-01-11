<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\OtpiqService;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

final class RegisterAction
{
    public function __construct(
        private OtpiqService $otpiqService
    ) {}

    /**
     * Execute the register action with OTP verification.
     * User must first request verification code via /api/auth/send-verification endpoint.
     * Then user provides all registration info + verification code in this single call.
     */
    public function execute(array $data): array
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|between:2,100',
            'phone_number' => 'required|string|regex:/^[0-9+\s\-()]+$/|min:10|max:20|unique:users',
            'password' => 'required|string|min:6',
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

        // Check if phone number already exists
        if (User::where('phone_number', $phoneNumber)->exists()) {
            return [
                'success' => false,
                'error' => 'Phone number already registered',
                'status' => 422,
            ];
        }

        // Verify OTP code (must have been sent via /api/auth/send-verification endpoint first)
        $verifyOtpAction = new VerifyPhoneCodeAction($this->otpiqService);
        $otpResult = $verifyOtpAction->execute([
            'phone_number' => $phoneNumber,
            'code' => $data['code'],
        ]);

        if (!$otpResult['success']) {
            return $otpResult;
        }

        // Create user
        $user = User::create([
            'name' => $data['name'],
            'phone_number' => $phoneNumber,
            'password' => $data['password'], // Automatically hashed by User model cast
            'phone_verified_at' => now(), // Phone verified via OTP code
        ]);

        // Generate JWT token
        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return [
                'success' => false,
                'error' => 'Could not create token',
                'status' => 500,
            ];
        }

        return [
            'success' => true,
            'message' => 'User successfully registered',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'status' => 201,
        ];
    }
}

