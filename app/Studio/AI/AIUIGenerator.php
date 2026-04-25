<?php

namespace App\Studio\AI;

use App\Studio\Registry\FlowDefinition;
use App\Studio\Registry\PageDefinition;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AIUIGenerator
{
    protected PromptEngine $promptEngine;
    protected UIConsistencyValidator $validator;

    public function __construct(PromptEngine $promptEngine, UIConsistencyValidator $validator)
    {
        $this->promptEngine = $promptEngine;
        $this->validator = $validator;
    }

    /**
     * Generate UI PageDefinition from a Flow.
     */
    public function generateFromFlow(FlowDefinition $flow): array
    {
        $context = $this->extractWorkflowContext($flow);
        
        $systemPrompt = $this->promptEngine->getSystemPrompt();
        $userPrompt = $this->promptEngine->buildGenerationPrompt($context);

        return $this->callAI($systemPrompt, $userPrompt, $flow->feature_version_id);
    }

    /**
     * Refine an existing PageDefinition.
     */
    public function refineDefinition(array $currentDefinition, string $instruction, int $featureVersionId): array
    {
        // Check refinement iteration limit (Requirement 7)
        $session = \DB::table('ai_generation_sessions')
            ->where('feature_version_id', $featureVersionId)
            ->latest('created_at')
            ->first();

        $maxIterations = config('ai.max_refinement_iterations', 5);
        
        if ($session && $session->refinement_iteration_count >= $maxIterations) {
            throw new \Exception(
                "Maximum refinement iterations ({$maxIterations}) reached for this feature. " .
                "Please use Manual Override to make further changes."
            );
        }

        $systemPrompt = $this->promptEngine->getSystemPrompt();
        $userPrompt = $this->promptEngine->buildRefinementPrompt($currentDefinition, $instruction);

        $result = $this->callAI($systemPrompt, $userPrompt, $featureVersionId);

        // Increment refinement counter
        if ($session) {
            \DB::table('ai_generation_sessions')
                ->where('id', $session->id)
                ->increment('refinement_iteration_count');

            // Log refinement audit trail
            \DB::table('ai_refinement_audit_trails')->insert([
                'session_id' => $session->id,
                'refinement_request' => $instruction,
                'selected_options' => json_encode([]), // Will be populated by frontend
                'previous_definition' => json_encode($currentDefinition),
                'new_definition' => json_encode($result),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $result;
    }

    protected function extractWorkflowContext(FlowDefinition $flow): array
    {
        $flow->load(['nodes', 'edges']);

        return [
            'flow_name' => $flow->name,
            'nodes' => $flow->nodes->map(fn($node) => [
                'key' => $node->node_key,
                'type' => $node->node_type,
                'label' => $node->label,
                'config' => $node->config,
            ])->toArray(),
            'edges' => $flow->edges->map(fn($edge) => [
                'source' => $flow->nodes->firstWhere('id', $edge->source_node_id)?->node_key ?? (string)$edge->source_node_id,
                'target' => $flow->nodes->firstWhere('id', $edge->target_node_id)?->node_key ?? (string)$edge->target_node_id,
                'condition' => $edge->condition_config,
            ])->toArray(),
        ];
    }

    /**
     * Call the AI API (OpenAI primary).
     */
    protected function callAI(string $systemPrompt, string $userPrompt, int $featureVersionId): array
    {
        $userId = auth()->id() ?? 1;
        
        // Rate Limiting (Requirement 11)
        if (RateLimiter::tooManyAttempts("ai-gen:{$userId}", config('ai.rate_limits.per_minute', 10))) {
            throw new \Exception("Rate limit exceeded. Please wait a moment.");
        }

        // Budget Enforcement (Requirement 10)
        $this->checkBudget();

        $apiKey = config('services.openai.key');
        $model = $this->resolveModel();
        $baseUrl = rtrim((string) config('ai.openai.base_url', 'https://api.openai.com/v1'), '/');

        if (!$apiKey) {
            throw new \Exception("AI API Key not configured.");
        }

        $startTime = microtime(true);

        $response = Http::withToken($apiKey)
            ->retry(3, 1000, throw: false)
            ->timeout(60)
            ->post("{$baseUrl}/chat/completions", [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => config('ai.openai.temperature', 0.7),
                'max_completion_tokens' => config('ai.openai.max_completion_tokens', 4000),
                'response_format' => ['type' => 'json_object'],
            ]);

        if ($response->failed()) {
            $errorMessage = data_get($response->json(), 'error.message')
                ?: $response->reason()
                ?: 'Unknown error';

            Log::error("AI API Call Failed", [
                'status' => $response->status(),
                'body' => $response->body(),
                'model' => $model,
            ]);
            throw new \Exception("AI Generation failed: {$errorMessage}");
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? '{}';
        $result = json_decode($content, true);

        // Normalize schema if LLM returns 'component' instead of 'component_type'
        if (isset($result['steps'])) {
            foreach ($result['steps'] as &$step) {
                if (isset($step['fields'])) {
                    foreach ($step['fields'] as &$field) {
                        if (isset($field['component']) && !isset($field['component_type'])) {
                            $field['component_type'] = $field['component'];
                            unset($field['component']);
                        }
                        $field['component_type'] = $field['component_type'] ?? 'text_input';
                    }
                }
            }
        }

        // Record Attempt
        RateLimiter::hit("ai-gen:{$userId}", 60);

        // UI Consistency Validation (Requirement 15)
        $validationResults = $this->validator->validate($result);

        // Design Compliance Threshold Enforcement (Requirement 22)
        $threshold = config('ai.design_compliance_threshold', 90);
        if ($validationResults['score'] < $threshold) {
            $violationCount = count($validationResults['violations']);
            throw new \Exception(
                "Generated UI failed design compliance check. " .
                "Score: {$validationResults['score']}% (Required: {$threshold}%). " .
                "Violations: {$violationCount}. Please regenerate or adjust design constraints."
            );
        }

        // Log usage (Requirement 10)
        $this->logUsage($data, $startTime);

        // Audit Session (Requirement 12)
        $this->auditSession($featureVersionId, $userPrompt, $content, $result, $validationResults);

        return array_merge($result, ['_validation' => $validationResults]);
    }

    /**
     * Log token usage and cost.
     */
    protected function logUsage(array $apiResponse, float $startTime): void
    {
        $usage = $apiResponse['usage'] ?? [];
        $inputTokens = $usage['prompt_tokens'] ?? 0;
        $outputTokens = $usage['completion_tokens'] ?? 0;

        // Approximate GPT-5.2 pricing: $1.75/1M input, $14/1M output.
        $cost = ($inputTokens * 0.00000175) + ($outputTokens * 0.000014);

        \DB::table('ai_usage_logs')->insert([
            'user_id' => auth()->id() ?? 1, // Fallback for testing
            'organization_id' => auth()->user()->organization_id ?? null,
            'feature_type' => 'ui_generation',
            'provider' => 'openai',
            'model_used' => $apiResponse['model'] ?? 'unknown',
            'tokens_input' => $inputTokens,
            'tokens_output' => $outputTokens,
            'cost_usd' => $cost,
            'used_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Audit the generation session.
     */
    protected function auditSession(int $versionId, string $prompt, string $rawResponse, array $parsedResult, array $validation = []): void
    {
        \DB::table('ai_generation_sessions')->insert([
            'session_key' => Str::uuid(),
            'feature_version_id' => $versionId,
            'user_id' => auth()->id() ?? 1,
            'workflow_context' => json_encode(['prompt' => $prompt]),
            'prompt' => $prompt,
            'response_raw' => $rawResponse,
            'generated_definition' => json_encode($parsedResult),
            'validation_results' => json_encode($validation),
            'status' => 'completed',
            'refinement_iteration_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Check if monthly budget is exceeded.
     */
    protected function checkBudget(): void
    {
        $mtdCost = \DB::table('ai_usage_logs')
            ->whereMonth('used_at', now()->month)
            ->whereYear('used_at', now()->year)
            ->sum('cost_usd');

        $budget = config('ai.monthly_budget_usd', 50.0);
        $warnThreshold = config('ai.warn_cost_threshold', 0.8);

        if ($mtdCost >= $budget) {
            throw new \Exception(
                "Monthly AI budget exceeded (\${$mtdCost} / \${$budget}). " .
                "Please contact your administrator to increase the budget or wait until next month."
            );
        }

        if ($mtdCost >= ($budget * $warnThreshold)) {
            \Log::warning("AI Budget Warning", [
                'mtd_cost' => $mtdCost,
                'budget' => $budget,
                'percent_used' => ($mtdCost / $budget) * 100,
            ]);
        }
    }

    protected function resolveModel(): string
    {
        $configuredModel = trim((string) config('ai.primary_model', 'gpt-5.2'));
        $fallbackModel = trim((string) config('ai.openai.fallback_model', 'gpt-5.2'));
        $aliases = config('ai.openai.model_aliases', []);

        if ($configuredModel === '') {
            return $fallbackModel;
        }

        return $aliases[$configuredModel] ?? $configuredModel;
    }
}
