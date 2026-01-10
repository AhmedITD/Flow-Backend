<?php

namespace App\Actions;

use App\Enums\ServiceType;
use App\Mcp\Tools\ToolRegistry;
use App\Models\ApiKey;
use App\Models\Subscription;
use App\Services\OpenAIService;
use App\Services\UsageTrackingService;
use Illuminate\Http\StreamedEvent;

/**
 * Handles chat messages with OpenAI and MCP tool integration.
 * 
 * Authentication: Uses API key (passed from controller)
 * 
 * Token tracking:
 * - Tokens are accumulated across all OpenAI API calls in a session
 * - At the end of the chat, total tokens are recorded to UsageTrackingService
 * - Tokens = prompt_tokens + completion_tokens from each OpenAI response
 */
class HandleChatAction
{
    private OpenAIService $openai;
    private UsageTrackingService $usageTracker;
    private string $serverKey;
    
    // Context from API key
    private ?ApiKey $apiKey = null;
    
    // Usage tracking
    private ?Subscription $subscription = null;
    private ?ServiceType $serviceType = null;
    private int $totalTokensUsed = 0;

    public function __construct(?string $serverKey = null)
    {
        $this->openai = new OpenAIService();
        $this->usageTracker = new UsageTrackingService();
        $this->serverKey = $serverKey ?? config('mcp.default', 'call-center');
    }

    /**
     * Execute the chat action with MCP tool integration.
     * 
     * @param string $userMessage The user's message
     * @param ApiKey $apiKey The authenticated API key
     */
    public function execute(string $userMessage, ApiKey $apiKey): \Generator
    {
        // Store API key context
        $this->apiKey = $apiKey;
        
        // 1. Initialize usage tracking from API key
        $this->initializeUsageTracking();

        // 2. Check token limits before processing
        if ($this->subscription && $this->serviceType) {
            $limitCheck = $this->usageTracker->checkTokenLimit($this->subscription, $this->serviceType);
            if (!$limitCheck['allowed']) {
                yield new StreamedEvent(
                    event: 'error',
                    data: 'Token limit exceeded. ' . ($limitCheck['reason'] ?? 'Please upgrade your plan or wait for the next billing cycle.')
                );
                return;
            }
        }

        // 3. Build messages array (stateless - no history)
        $messages = $this->buildMessages($userMessage);

        // 4. Get tools for this server
        $tools = ToolRegistry::toOpenAIFormat($this->serverKey);

        // 5. Initial request to OpenAI with tools
        $response = $this->openai->chat($messages, [
            'tools' => $tools,
            'tool_choice' => 'auto',
        ]);

        if (!$response['success']) {
            yield new StreamedEvent(event: 'error', data: $response['error']);
            return;
        }

        // 6. Calculate tokens from initial response
        $this->addTokensFromResponse($response['data']);

        // 7. Handle tool calling loop
        $maxIterations = 10;
        $iteration = 0;
        $finalResponse = '';
        $toolCallCount = 0;

        while ($iteration < $maxIterations) {
            $iteration++;
            $data = $response['data'];
            $choice = $data['choices'][0] ?? null;
            
            if (!$choice) {
                yield new StreamedEvent(event: 'error', data: 'No response from AI');
                return;
            }

            $message = $choice['message'];
            $finishReason = $choice['finish_reason'];

            // Check if AI wants to call tools
            if ($finishReason === 'tool_calls' || !empty($message['tool_calls'])) {
                $toolCalls = $message['tool_calls'] ?? [];
                $toolCallCount += count($toolCalls);

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

                // Add tokens from follow-up response
                $this->addTokensFromResponse($response['data']);

                continue;
            }

            // AI finished - extract final response
            $finalResponse = $message['content'] ?? '';
            break;
        }

        if (empty($finalResponse)) {
            $finalResponse = 'I processed your request but could not generate a final response.';
        }

        // 8. Record total token usage (once at the end)
        $this->recordTotalTokenUsage($toolCallCount);

        // 9. Stream the response word by word
        $words = explode(' ', $finalResponse);
        foreach ($words as $index => $word) {
            yield new StreamedEvent(
                event: 'update',
                data: $word . ($index < count($words) - 1 ? ' ' : '')
            );
            usleep(30000); // Small delay for streaming effect
        }

        yield new StreamedEvent(event: 'done', data: 'complete');
    }

    /**
     * Build messages array with system prompt and current message (stateless).
     */
    private function buildMessages(string $currentMessage): array
    {
        return [
            [
                'role' => 'system',
                'content' => ToolRegistry::getSystemPrompt($this->serverKey),
            ],
            [
                'role' => 'user',
                'content' => $currentMessage,
            ],
        ];
    }

    /**
     * Initialize usage tracking from API key.
     * 
     * Gets subscription from:
     * 1. API key's direct subscription (if set)
     * 2. Or API key owner's active subscription
     * 
     * Gets service type from MCP server key.
     */
    private function initializeUsageTracking(): void
    {
        if (!$this->apiKey) {
            return;
        }

        // Try to get subscription directly from API key first
        $this->subscription = $this->apiKey->subscription;

        // If no direct subscription, get from API key's owner (user)
        if (!$this->subscription && $this->apiKey->user) {
            $this->subscription = $this->usageTracker->getActiveSubscription($this->apiKey->user->id);
        }

        // Get service type from MCP server key
        $this->serviceType = ServiceType::fromMcpServerKey($this->serverKey) ?? ServiceType::CallCenter;
    }

    private function addTokensFromResponse(array $responseData): void
    {
        $usage = $responseData['usage'] ?? [];

        // Prefer total_tokens if available
        if (isset($usage['total_tokens']) && $usage['total_tokens'] > 0) {
            $this->totalTokensUsed += (int) $usage['total_tokens'];
            return;
        }

        // Fallback: calculate from prompt and completion tokens
        $promptTokens = (int) ($usage['prompt_tokens'] ?? 0);
        $completionTokens = (int) ($usage['completion_tokens'] ?? 0);
        
        $this->totalTokensUsed += $promptTokens + $completionTokens;
    }

    /**
     * Record total token usage at the end of the chat session.
     */
    private function recordTotalTokenUsage(int $toolCallCount): void
    {
        if (!$this->subscription || !$this->serviceType) {
            return;
        }

        if ($this->totalTokensUsed <= 0) {
            \Log::warning('No tokens to record for chat session', [
                'api_key_id' => $this->apiKey?->id,
            ]);
            return;
        }

        try {
            $this->usageTracker->recordTokenUsage(
                $this->subscription,
                $this->serviceType,
                $this->totalTokensUsed,
                $toolCallCount > 0 ? 'chat_with_tools' : 'chat',
                [
                    'tool_calls' => $toolCallCount,
                    'api_key_id' => $this->apiKey?->id,
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Failed to record token usage', [
                'error' => $e->getMessage(),
                'subscription_id' => $this->subscription->id,
                'service_type' => $this->serviceType->value,
                'api_key_id' => $this->apiKey?->id,
                'total_tokens_used' => $this->totalTokensUsed,
            ]);
        }
    }
}
