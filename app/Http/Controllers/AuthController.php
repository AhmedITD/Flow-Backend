<?php

namespace App\Http\Controllers;

use App\Actions\Auth\GetAuthenticatedUserAction;
use App\Actions\Auth\LoginAction;
use App\Actions\Auth\LogoutAction;
use App\Actions\Auth\RefreshTokenAction;
use App\Actions\Auth\RegisterAction;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $action = new LoginAction();
        $result = $action->execute($request->only('email', 'password'));

        if (!$result['success']) {
            return response()->json(
                $result['errors'] ?? ['error' => $result['error']],
                $result['status']
            );
        }

        return response()->json([
            'access_token' => $result['token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
        ]);
    }

    /**
     * Register a new user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $action = new RegisterAction();
        $result = $action->execute($request->all());

        if (!$result['success']) {
            return response()->json(
                $result['errors'] ?? ['error' => $result['error']],
                $result['status']
            );
        }

        return response()->json([
            'message' => $result['message'],
            'user' => $result['user'],
            'access_token' => $result['access_token'],
            'token_type' => $result['token_type'],
            'expires_in' => $result['expires_in'],
        ], $result['status']);
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
}

