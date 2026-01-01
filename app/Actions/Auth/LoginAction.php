<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LoginAction
{
    /**
     * Execute the login action.
     */
    public function execute(array $credentials): array
    {
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors(),
                'status' => 422,
            ];
        }

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return [
                    'success' => false,
                    'error' => 'Unauthorized',
                    'status' => 401,
                ];
            }
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
        ];
    }
}

