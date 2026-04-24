<?php

namespace Tests\Unit\Studio\AI;

use App\Studio\AI\AIUIGenerator;
use App\Studio\AI\PromptEngine;
use App\Studio\AI\UIConsistencyValidator;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AIUIGeneratorTest extends TestCase
{
    public function test_it_normalizes_legacy_model_aliases(): void
    {
        config()->set('ai.primary_model', 'gpt-5.4-turbo');
        config()->set('ai.openai.fallback_model', 'gpt-5.2');
        config()->set('ai.openai.model_aliases', [
            'gpt-5.4-turbo' => 'gpt-5.4',
        ]);

        $generator = new class(new PromptEngine(), new UIConsistencyValidator()) extends AIUIGenerator {
            public function exposedResolveModel(): string
            {
                return $this->resolveModel();
            }
        };

        $this->assertSame('gpt-5.4', $generator->exposedResolveModel());
    }

    public function test_it_falls_back_when_primary_model_is_blank(): void
    {
        config()->set('ai.primary_model', '   ');
        config()->set('ai.openai.fallback_model', 'gpt-5.2');
        config()->set('ai.openai.model_aliases', []);

        $generator = new class(new PromptEngine(), new UIConsistencyValidator()) extends AIUIGenerator {
            public function exposedResolveModel(): string
            {
                return $this->resolveModel();
            }
        };

        $this->assertSame('gpt-5.2', $generator->exposedResolveModel());
    }

    public function test_it_surfaces_api_error_messages_without_throwing_raw_http_exceptions(): void
    {
        config()->set('services.openai.key', 'test-key');
        config()->set('ai.primary_model', 'gpt-5.2');
        config()->set('ai.openai.base_url', 'https://api.openai.com/v1');
        config()->set('ai.openai.temperature', 0.7);
        config()->set('ai.openai.max_tokens', 4000);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'error' => [
                    'message' => 'You exceeded your current quota.',
                ],
            ], 429),
        ]);

        $generator = new class(new PromptEngine(), new UIConsistencyValidator()) extends AIUIGenerator {
            public function exposedCallAI(): array
            {
                return $this->callAI('system', 'user', 1);
            }

            protected function checkBudget(): void
            {
            }
        };

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('AI Generation failed: You exceeded your current quota.');

        $generator->exposedCallAI();
    }
}
