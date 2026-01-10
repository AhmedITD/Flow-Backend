<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\OtpiqService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginAction
{
    public function __construct(
        private OtpiqService $otpiqService
    ) {}

    /**
     * Execute the login action.
     * Simple login with phone_number and password (no OTP required).
     */
    public function execute(array $credentials): array
    {
        $validator = Validator::make($credentials, [
            'phone_number' => 'required|string|regex:/^[0-9+\s\-()]+$/|min:10|max:20',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
                'status' => 422,
            ];
        }

        $phoneNumber = $this->otpiqService->formatPhoneNumber($credentials['phone_number']);

        // Find user by phone number
        $user = User::where('phone_number', $phoneNumber)->first();

        // Verify user exists and password is correct
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return [
                'success' => false,
                'error' => 'Invalid phone number or password',
                'status' => 401,
            ];
        }

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
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $user,
        ];
    }
}

