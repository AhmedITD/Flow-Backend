<?php

namespace App\Http\Controllers;

use App\Actions\Auth\GetAuthenticatedUserAction;
use App\Actions\Auth\LoginAction;
use App\Actions\Auth\LogoutAction;
use App\Actions\Auth\RefreshTokenAction;
use App\Actions\Auth\RegisterAction;
use App\Actions\Auth\RequestPasswordResetAction;
use App\Actions\Auth\ResetPasswordAction;
use App\Actions\Auth\SendPhoneVerificationAction;
use App\Actions\Auth\VerifyPhoneCodeAction;
use App\Services\OtpiqService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     * Simple login with phone_number and password (no OTP required).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $otpiqService = app(OtpiqService::class);
        $action = new LoginAction($otpiqService);
        
        $result = $action->execute(
            $request->only('phone_number', 'password')
        );

        if (!$result['success']) {
            return response()->json(
                $result['errors'] ?? ['error' => $result['error']] ?? ['error' => 'Authentication failed'],
                $result['status'] ?? 400
            );
        }

        return response()->json([
            'access_token' => $result['token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
            'user' => $result['user'] ?? null,
        ]);
    }

    /**
     * Register a new user with OTP verification.
     * User must first call /api/auth/send-verification with phone_number to get verification code.
     * Then user calls this endpoint with name, phone_number, password, password_confirmation, and code.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $otpiqService = app(OtpiqService::class);
        $action = new RegisterAction($otpiqService);
        
        $result = $action->execute($request->all());

        if (!$result['success']) {
            return response()->json(
                $result['errors'] ?? ['error' => $result['error']] ?? ['error' => 'Registration failed'],
                $result['status'] ?? 400
            );
        }

        return response()->json([
            'message' => $result['message'],
            'user' => $result['user'],
            'access_token' => $result['access_token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
        ], $result['status'] ?? 201);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $action = new GetAuthenticatedUserAction();
        $result = $action->execute();

        return response()->json($result['user']);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $action = new LogoutAction();
        $result = $action->execute();

        return response()->json(['message' => $result['message']]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $action = new RefreshTokenAction();
        $result = $action->execute();

        return response()->json([
            'access_token' => $result['access_token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
        ]);
    }

    /**
     * Send phone verification code for registration.
     * Phone number must NOT be registered yet.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendPhoneVerification(Request $request)
    {
        $otpiqService = app(OtpiqService::class);
        $action = new SendPhoneVerificationAction($otpiqService);
        
        $result = $action->execute(
            $request->only('phone_number'),
            $request->ip()
        );

        if (!$result['success']) {
            return response()->json(
                $result['errors'] ?? ['error' => $result['error']],
                $result['status'] ?? 400
            );
        }

        return response()->json([
            'message' => $result['message'],
            'phone_number' => $result['phone_number'],
            'expires_in_minutes' => $result['expires_in_minutes'],
        ]);
    }

    /**
     * Verify phone verification code for registration
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyPhoneCode(Request $request)
    {
        $otpiqService = app(OtpiqService::class);
        $action = new VerifyPhoneCodeAction($otpiqService);
        
        $result = $action->execute(
            $request->only('phone_number', 'code')
        );

        if (!$result['success']) {
            return response()->json(
                $result['errors'] ?? ['error' => $result['error']],
                $result['status'] ?? 400
            );
        }

        return response()->json([
            'message' => $result['message'],
            'phone_number' => $result['phone_number'],
            'verified_at' => $result['verified_at'],
        ]);
    }

    /**
     * Request password reset by sending OTP code.
     * Phone number MUST be registered.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $otpiqService = app(OtpiqService::class);
        $action = new RequestPasswordResetAction($otpiqService);
        
        $result = $action->execute(
            $request->only('phone_number'),
            $request->ip()
        );

        if (!$result['success']) {
            return response()->json(
                $result['errors'] ?? ['error' => $result['error']],
                $result['status'] ?? 400
            );
        }

        return response()->json([
            'message' => $result['message'],
            'phone_number' => $result['phone_number'],
            'expires_in_minutes' => $result['expires_in_minutes'],
        ]);
    }

    /**
     * Reset password with OTP verification.
     * User must have requested password reset via /api/auth/forgot-password first.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $otpiqService = app(OtpiqService::class);
        $action = new ResetPasswordAction($otpiqService);
        
        $result = $action->execute($request->only('phone_number', 'code', 'password', 'password_confirmation'));

        if (!$result['success']) {
            return response()->json(
                $result['errors'] ?? ['error' => $result['error']],
                $result['status'] ?? 400
            );
        }

        return response()->json([
            'message' => $result['message'],
        ], $result['status'] ?? 200);
    }
}

