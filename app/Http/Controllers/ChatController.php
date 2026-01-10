<?php

namespace App\Http\Controllers;

use App\Actions\HandleChatAction;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Stream chat response using SSE with MCP tool integration.
     * Authenticated via API key (customer service access).
     * No conversation or message history is saved.
     */
    public function chat(Request $request)
    {
        // Get the ApiKey model attached by AuthenticateApiKey middleware
        $apiKeyModel = $request->attributes->get('api_key_model');
        
        if (!$apiKeyModel) {
            return response()->json([
                'error' => 'API key required',
                'message' => 'This endpoint requires API key authentication',
            ], 401);
        }

        $userMessage = $request->input('message');
        
        if (!$userMessage) {
            return response()->json([
                'error' => 'Message required',
                'message' => 'Please provide a message in the request',
            ], 400);
        }

        $action = new HandleChatAction();

        return response()->eventStream(function () use ($action, $userMessage, $apiKeyModel) {
            yield from $action->execute($userMessage, $apiKeyModel);
        });
    }
}
