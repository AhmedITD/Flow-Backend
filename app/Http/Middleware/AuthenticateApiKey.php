<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get API key from header or query parameter
        // Note: We don't check Authorization header to avoid confusion with JWT Bearer tokens
        $apiKey = $request->header('X-API-Key') 
            ?? $request->input('api_key');

        if (!$apiKey) {
            return response()->json([
                'error' => 'API key is required',
                'message' => 'Please provide an API key in the X-API-Key header or as a query parameter (api_key)',
            ], 401);
        }

        // Hash the provided key
        $keyHash = hash('sha256', $apiKey);

        // Find the API key
        $apiKeyModel = ApiKey::where('key_hash', $keyHash)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$apiKeyModel) {
            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'The provided API key is invalid, revoked, or expired',
            ], 401);
        }

        // Check if key is expired
        if ($apiKeyModel->isExpired()) {
            $apiKeyModel->update(['status' => 'expired']);
            return response()->json([
                'error' => 'API key expired',
                'message' => 'This API key has expired',
            ], 401);
        }

        // Set the authenticated user (for Auth::user() to work)
        Auth::guard('api')->setUser($apiKeyModel->user);
        
        // Also set user resolver for request
        $request->setUserResolver(function () use ($apiKeyModel) {
            return $apiKeyModel->user;
        });

        // Attach API key model to request for later use
        $request->attributes->set('api_key_model', $apiKeyModel);

        // Record start time for response time measurement
        $startTime = microtime(true);

        // Process the request
        $response = $next($request);

        // Calculate response time in milliseconds
        $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);

        // Record usage with actual response data
        $this->recordUsage($apiKeyModel, $request, $response->getStatusCode(), $responseTimeMs);

        return $response;
    }

    /**
     * Record API key usage with response metrics.
     */
    private function recordUsage(ApiKey $apiKey, Request $request, int $statusCode, int $responseTimeMs): void
    {
        try {
            $apiKey->recordUsage([
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'status_code' => $statusCode,
                'response_time_ms' => $responseTimeMs,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'route' => $request->route()?->getName(),
                ],
            ]);
        } catch (\Exception $e) {
            // Log but don't fail the request
            \Log::warning('Failed to record API key usage', [
                'error' => $e->getMessage(),
                'api_key_id' => $apiKey->id,
            ]);
        }
    }
}
