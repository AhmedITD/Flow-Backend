<?php

namespace App\Actions;

use App\Mcp\Tools\ToolRegistry;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\OpenAIService;
use Illuminate\Http\StreamedEvent;

class HandleChatAction
{
    private OpenAIService $openai;
    private string $serverKey;

    public function __construct(?string $serverKey = null)
    {
        $this->openai = new OpenAIService();
        $this->serverKey = $serverKey ?? config('mcp.default', 'call-center');
    }

    /**
     * Execute the chat action with MCP tool integration.
     */
    public function execute(string $conversationId, string $userMessage): \Generator
    {
        $conversation = Conversation::findOrFail($conversationId);

        // Save user message
        Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $userMessage,
        ]);

        // Build messages array with conversation history
        $messages = $this->buildMessages($conversation, $userMessage);

        // Get tools for this server
        $tools = ToolRegistry::toOpenAIFormat($this->serverKey);

        // Initial request to OpenAI with tools
        $response = $this->openai->chat($messages, [
            'tools' => $tools,
            'tool_choice' => 'auto',
        ]);

        if (!$response['success']) {
            yield new StreamedEvent(event: 'error', data: $response['error']);
            return;
        }

        // Handle tool calling loop
        $maxIterations = 10;
        $iteration = 0;
        $finalResponse = '';
        while ($iteration < $maxIterations) {
            $iteration++;
            $data = $response['data'];
            $choice = $data['choices'][0] ?? null;
            if (!$choice) 
            {
                yield new StreamedEvent(event: 'error', data: 'No response from AI');
                return;
            }

            $message = $choice['message'];
            $finishReason = $choice['finish_reason'];

            // Check if AI wants to call tools
            if ($finishReason === 'tool_calls' || !empty($message['tool_calls'])) {
                $toolCalls = $message['tool_calls'] ?? [];

                // Add assistant message with tool calls to history
                $messages[] = $message;

                foreach ($toolCalls as $toolCall) {
                    $toolName = $toolCall['function']['name'];
                    $toolArgs = json_decode($toolCall['function']['arguments'], true) ?? [];
                    $toolCallId = $toolCall['id'];

                    // Notify client about tool call
                    yield new StreamedEvent(
                        event: 'tool_call',
                        data: json_encode([
                            'tool' => $toolName,
                            'arguments' => $toolArgs,
                        ])
                    );

                    // Execute the tool
                    $toolResult = ToolRegistry::execute($toolName, $toolArgs);

                    // Notify client about tool result
                    yield new StreamedEvent(
                        event: 'tool_result',
                        data: json_encode([
                            'tool' => $toolName,
                            'success' => $toolResult['success'],
                            'content' => $toolResult['content'] ?? ($toolResult['error'] ?? 'No content'),
                        ])
                    );

                    // Add tool result to messages
                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCallId,
                        'content' => $toolResult['success']
                            ? ($toolResult['content'] ?? json_encode($toolResult))
                            : "Error: " . ($toolResult['error'] ?? 'Unknown error'),
                    ];
                }

                // Continue conversation with tool results
                $response = $this->openai->chat($messages, [
                    'tools' => $tools,
                    'tool_choice' => 'auto',
                ]);

                if (!$response['success']) {
                    yield new StreamedEvent(event: 'error', data: $response['error']);
                    return;
                }

                continue;
            }

            // AI finished - extract final response
            $finalResponse = $message['content'] ?? '';
            break;
        }

        if (empty($finalResponse)) {
            $finalResponse = 'I processed your request but could not generate a final response.';
        }

        // Stream the response word by word
        $words = explode(' ', $finalResponse);
        foreach ($words as $index => $word) {
            yield new StreamedEvent(
                event: 'update',
                data: $word . ($index < count($words) - 1 ? ' ' : '')
            );
            usleep(30000); // Small delay for streaming effect
        }

        // Save assistant message
        Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $finalResponse,
        ]);

        yield new StreamedEvent(event: 'done', data: 'complete');
    }

    /**
     * Build messages array with system prompt and conversation history.
     */
    private function buildMessages(Conversation $conversation, string $currentMessage): array
    {
        $messages = [];

        // Add system prompt for this server
        $messages[] = [
            'role' => 'system',
            'content' => ToolRegistry::getSystemPrompt($this->serverKey),
        ];

        // Add recent conversation history (last 10 messages)
        $history = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->reverse();

        foreach ($history as $msg) {
            if ($msg->role === 'user' || $msg->role === 'assistant') {
                $messages[] = [
                    'role' => $msg->role,
                    'content' => $msg->content,
                ];
            }
        }

        // Add current user message
        $messages[] = [
            'role' => 'user',
            'content' => $currentMessage,
        ];

        return $messages;
    }
}
