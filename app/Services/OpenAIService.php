<?php

namespace App\Services;

use App\Mcp\Servers\CallCenterServer;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAIService
{
    /**
     * Send a chat completion request.
     */
    public function chat(array $messages, array $options = []): array
    {
        // Check if API key is configured
        $apiKey = config('openai.api_key');
        if (empty($apiKey)) {
            return [
                'success' => false,
                'error' => 'OpenAI API key is not configured. Please set OPENAI_API_KEY in your .env file and run: php artisan config:clear',
            ];
        }

        // Validate API key format (should start with 'sk-' or 'sk-proj-')
        if (!str_starts_with($apiKey, 'sk-')) {
            return [
                'success' => false,
                'error' => 'Invalid OpenAI API key format. API keys should start with "sk-". Please check your OPENAI_API_KEY in .env file.',
            ];
        }

        try {
            // Build parameters, filtering out null values
            $params = [
                'model' => $options['model'] ?? config('openai.model', 'gpt-4o-mini'),
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['max_tokens'] ?? 1000,
            ];

            // Only add optional parameters if they are set
            if (isset($options['tools']) && $options['tools'] !== null) {
                $params['tools'] = $options['tools'];
            }
            if (isset($options['tool_choice']) && $options['tool_choice'] !== null) {
                $params['tool_choice'] = $options['tool_choice'];
            }

            // Log the request for debugging
            Log::debug('OpenAI Chat Request', [
                'model' => $params['model'],
                'messages_count' => count($params['messages']),
                'has_tools' => isset($params['tools']),
            ]);

            $response = OpenAI::chat()->create($params);

            // Convert response to array safely
            $responseArray = [];
            try {
                $responseArray = $response->toArray();
                
                // Validate that the response has the expected structure
                if (!isset($responseArray['choices']) || !is_array($responseArray['choices']) || empty($responseArray['choices'])) {
                    Log::warning('OpenAI response missing choices', [
                        'response' => $responseArray,
                    ]);
                    
                    return [
                        'success' => false,
                        'error' => 'Invalid response format from OpenAI API. The response does not contain choices.',
                    ];
                }
            } catch (\ErrorException $e) {
                // Handle "Undefined array key" errors from the package
                if (str_contains($e->getMessage(), 'Undefined array key')) {
                    Log::error('OpenAI response parsing error', [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                    
                    return [
                        'success' => false,
                        'error' => 'Invalid response from OpenAI API. Please verify your API key is correct and the API is accessible.',
                    ];
                }
                throw $e;
            } catch (\Exception $e) {
                // If toArray() fails, log and return error
                Log::warning('Failed to convert OpenAI response to array', [
                    'message' => $e->getMessage(),
                    'class' => get_class($e),
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Failed to parse OpenAI API response: ' . $e->getMessage(),
                ];
            }

            return [
                'success' => true,
                'data' => $responseArray,
            ];
        } catch (\ErrorException $e) {
            // Catch "Undefined array key" errors from OpenAI package
            if (str_contains($e->getMessage(), 'Undefined array key')) {
                Log::error('OpenAI response parsing error - likely invalid API key or API error', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'api_key_prefix' => substr(config('openai.api_key'), 0, 7) . '...',
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Invalid response from OpenAI API. This usually means: 1) Your API key is invalid or expired, 2) Your API key has no credits/quota, 3) The API is temporarily unavailable. Please verify your OPENAI_API_KEY in .env file and check your OpenAI account status.',
                ];
            }
            // Re-throw if it's a different ErrorException
            throw $e;
        } catch (\OpenAI\Exceptions\UnserializableResponse $e) {
            // Try to get the actual HTTP response body
            $errorMessage = $e->getMessage();
            $responseBody = null;
            
            try {
                // Use reflection to access private properties if needed
                $reflection = new \ReflectionClass($e);
                if ($reflection->hasProperty('response')) {
                    $property = $reflection->getProperty('response');
                    $property->setAccessible(true);
                    $response = $property->getValue($e);
                    
                    if ($response && method_exists($response, 'getBody')) {
                        $body = $response->getBody()->getContents();
                        $responseBody = json_decode($body, true);
                        
                        if ($responseBody && isset($responseBody['error']['message'])) {
                            $errorMessage = $responseBody['error']['message'];
                        }
                    }
                }
            } catch (\Exception $reflectionError) {
                // If reflection fails, use the exception message
            }

            Log::error('OpenAI UnserializableResponse', [
                'message' => $e->getMessage(),
                'extracted_error' => $errorMessage,
                'response_body' => $responseBody,
            ]);

            return [
                'success' => false,
                'error' => 'OpenAI API Error: ' . $errorMessage,
            ];
        } catch (\OpenAI\Exceptions\ErrorException $e) {
            $errorMessage = $e->getMessage();
            
            // Provide more helpful error messages
            if (str_contains($errorMessage, 'Syntax error') || str_contains($errorMessage, 'Invalid')) {
                $errorMessage = 'Invalid API request. Please check your OpenAI API key and configuration.';
            }
            
            Log::error('OpenAI API error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI API exception', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            // Check if it's an API key issue
            if (str_contains($e->getMessage(), '401') || str_contains($e->getMessage(), 'Unauthorized')) {
                return [
                    'success' => false,
                    'error' => 'Invalid OpenAI API key. Please check your OPENAI_API_KEY in .env file.',
                ];
            }

            return [
                'success' => false,
                'error' => 'An error occurred: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Test the OpenAI API connection with a simple request.
     */
    // public function testConnection(): array
    // {
    //     $apiKey = config('openai.api_key');
        
    //     if (empty($apiKey)) {
    //         return [
    //             'success' => false,
    //             'error' => 'API key is not configured',
    //         ];
    //     }

    //     if (!str_starts_with($apiKey, 'sk-')) {
    //         return [
    //             'success' => false,
    //             'error' => 'Invalid API key format. Should start with "sk-"',
    //         ];
    //     }

    //     try {
    //         // Make a minimal test request
    //         $response = OpenAI::chat()->create([
    //             'model' => 'gpt-4o-mini',
    //             'messages' => [
    //                 ['role' => 'user', 'content' => 'Say "test"'],
    //             ],
    //             'max_tokens' => 5,
    //         ]);

    //         if ($response && method_exists($response, 'toArray')) {
    //             $data = $response->toArray();
    //             if (isset($data['choices'][0]['message']['content'])) {
    //                 return [
    //                     'success' => true,
    //                     'message' => 'API connection successful',
    //                     'response' => $data['choices'][0]['message']['content'],
    //                 ];
    //             }
    //         }

    //         return [
    //             'success' => false,
    //             'error' => 'Unexpected response format',
    //         ];
    //     } catch (\Exception $e) {
    //         return [
    //             'success' => false,
    //             'error' => 'Connection failed: ' . $e->getMessage(),
    //             'class' => get_class($e),
    //         ];
    //     }
    // }

}

