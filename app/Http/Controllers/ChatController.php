<?php

namespace App\Http\Controllers;

use App\Actions\HandleChatAction;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Create a new conversation.
     */
    public function createConversation(Request $request)
    {
        $conversation = Conversation::create([
            'tenant_id' => $request->input('tenant_id'),
            'title' => $request->input('title', 'New Conversation'),
            'status' => 'active',
        ]);

        return response()->json([
            'conversation_id' => $conversation->id,
            'title' => $conversation->title,
            'created_at' => $conversation->created_at,
        ]);
    }

    /**
     * Get conversation with messages.
     */
    // public function getConversation(string $conversationId)
    // {
    //     $conversation = Conversation::with('messages')->findOrFail($conversationId);

    //     return response()->json($conversation);
    // }

    /**
     * Stream chat response using SSE with MCP tool integration.
     */
    public function chat(Request $request, string $conversationId)
    {
        $userMessage = $request->input('message');
        $action = new HandleChatAction();

        return response()->eventStream(function () use ($action, $conversationId, $userMessage) {
            yield from $action->execute($conversationId, $userMessage);
        });
    }
}
