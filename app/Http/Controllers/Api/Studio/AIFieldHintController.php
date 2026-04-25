<?php

namespace App\Http\Controllers\Api\Studio;

use App\Http\Controllers\Controller;
use App\Studio\AI\FieldHintPromptBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AIFieldHintController extends Controller
{
    public function hint(Request $request): JsonResponse
    {
        // 1. Validate
        $validated = $request->validate([
            'nodeType'     => 'required|string|max:50',
            'fieldKey'     => 'required|string|max:100',
            'fieldLabel'   => 'required|string|max:200',
            'mode'         => 'required|string|in:quick,detailed',
            'userQuestion' => 'nullable|string|max:500',
        ]);

        // 2. Rate limit: 30/min per user
        $userId = auth()->id();
        $rateLimitKey = "field-hint:{$userId}";

        if (RateLimiter::tooManyAttempts($rateLimitKey, 30)) {
            return response()->json([
                'message' => 'Too many requests. Please wait a moment.',
            ], 429);
        }

        RateLimiter::hit($rateLimitKey, 60);

        // 3. Build prompt
        $builder = new FieldHintPromptBuilder();
        $systemPrompt = $builder->buildSystemPrompt();
        $userPrompt = $builder->buildUserPrompt(
            $validated['nodeType'],
            $validated['fieldKey'],
            $validated['fieldLabel'],
            $validated['mode'],
            $validated['userQuestion'] ?? null
        );

        // 4. Call AI API
        $apiKey = config('services.openai.key');
        $model = config('ai.primary_model', config('ai.openai.model', 'gpt-4o-mini'));
        $baseUrl = rtrim((string) config('ai.openai.base_url', 'https://api.openai.com/v1'), '/');

        if (!$apiKey) {
            return response()->json(['message' => 'AI API key not configured.'], 500);
        }

        $maxTokens = $validated['mode'] === 'quick' ? 60 : 500;

        try {
            $response = Http::withToken($apiKey)
                ->timeout(15)
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.3,
                    'max_completion_tokens' => $maxTokens,
                ]);

            if ($response->failed()) {
                Log::error('AI Field Hint API Failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json(['message' => 'AI service unavailable.'], 500);
            }

            $hint = $response->json('choices.0.message.content', '');

            return response()->json(['hint' => trim($hint)]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('AI Field Hint Timeout', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Request timed out. Please try again.'], 504);
        } catch (\Exception $e) {
            Log::error('AI Field Hint Error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }
}
