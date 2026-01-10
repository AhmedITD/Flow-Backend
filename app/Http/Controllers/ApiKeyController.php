<?php

namespace App\Http\Controllers;

use App\Actions\ApiKey\GenerateApiKeyAction;
use App\Actions\ApiKey\GetApiKeyAction;
use App\Actions\ApiKey\ListApiKeysAction;
use App\Actions\ApiKey\RevokeApiKeyAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiKeyController extends Controller
{
    /**
     * List all API keys for the authenticated user.
     */
    public function index(Request $request)
    {
        $action = new ListApiKeysAction();
        $result = $action->execute(Auth::user(), $request->only(['status', 'subscription_id']));

        return response()->json($result);
    }

    /**
     * Generate a new API key.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'environment' => 'nullable|in:live,test',
            'scopes' => 'nullable|array',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $action = new GenerateApiKeyAction();
        $result = $action->execute(Auth::user(), $request->all());

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'API key generated successfully. Please save it securely - it will not be shown again.',
            'api_key' => [
                'id' => $result['api_key']->id,
                'name' => $result['api_key']->name,
                'key' => $result['plain_key'], // Only shown once
                'key_prefix' => $result['api_key']->key_prefix,
                'status' => $result['api_key']->status,
                'expires_at' => $result['api_key']->expires_at,
                'created_at' => $result['api_key']->created_at,
            ],
        ], 201);
    }

    /**
     * Get a specific API key.
     */
    public function show(string $id)
    {
        $action = new GetApiKeyAction();
        $result = $action->execute(Auth::user(), $id);

        if (!$result['success']) {
            return response()->json($result, 404);
        }

        return response()->json($result);
    }

    /**
     * Revoke an API key.
     */
    public function destroy(Request $request, string $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $action = new RevokeApiKeyAction();
        $result = $action->execute(Auth::user(), $id, $request->input('reason'));

        if (!$result['success']) {
            return response()->json($result, 404);
        }

        return response()->json($result);
    }

    /**
     * Get iframe embed code for a specific API key.
     */
    public function embedCode(string $id)
    {
        $action = new GetApiKeyAction();
        $result = $action->execute(Auth::user(), $id);

        if (!$result['success']) {
            return response()->json($result, 404);
        }

        $apiKey = $result['api_key'];
        
        // Generate signed URL for iframe
        $iframeUrl = url('/chat/embed?' . http_build_query([
            'key' => encrypt($apiKey['id']),
            'token' => encrypt($apiKey['id'] . '|' . now()->timestamp),
        ]));

        return response()->json([
            'success' => true,
            'iframe_url' => $iframeUrl,
            'embed_code' => '<iframe src="' . htmlspecialchars($iframeUrl) . '" width="100%" height="600" frameborder="0"></iframe>',
            'api_key' => $apiKey,
        ]);
    }
}
