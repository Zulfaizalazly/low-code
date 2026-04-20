# AI-Generated UI Builder - Implementation Notes

## Technology Stack (From V3-ai-doc.md)

### Core Components
- **Backend**: Laravel 13 (existing)
- **AI Provider**: OpenAI API (Direct HTTP calls)
- **Model**: GPT-5.4 Turbo
- **HTTP Client**: Guzzle (Laravel built-in)
- **Rate Limiting**: Laravel Cache + Redis
- **Cost Tracking**: Database (ai_usage_logs table)

### Why Direct API (No SDK)?
✅ Full control over requests
✅ No external dependencies
✅ Easy to switch providers
✅ Latest model access (GPT-5.4)
✅ Custom retry logic
✅ Precise cost tracking

---

## Environment Configuration

```bash
# .env additions
AI_PROVIDER=openai
OPENAI_API_KEY=sk-proj-xxxxxxxxxxxxx
OPENAI_MODEL=gpt-5.4-turbo
OPENAI_API_BASE=https://api.openai.com/v1

AI_RATE_LIMIT_PER_MINUTE=10
AI_RATE_LIMIT_PER_HOUR=100
AI_MAX_TOKENS_PER_REQUEST=4000
AI_WARN_COST_THRESHOLD=10.00
```

---

## Database Schema (From V3-ai-doc.md)

### ai_usage_logs
```sql
CREATE TABLE ai_usage_logs (
    id BIGINT PRIMARY KEY,
    user_id BIGINT FK,
    org_id BIGINT FK,
    feature_type VARCHAR,  -- 'ui_generation'
    provider VARCHAR,      -- 'openai'
    model_used VARCHAR,    -- 'gpt-5.4-turbo'
    tokens_input INT,
    tokens_output INT,
    cost_usd DECIMAL(10,6),
    used_at TIMESTAMP,
    INDEX (org_id, used_at),
    INDEX (user_id, used_at)
);
```

### ai_generation_sessions (New - for UI Builder)
```sql
CREATE TABLE ai_generation_sessions (
    id BIGINT PRIMARY KEY,
    user_id BIGINT FK,
    feature_version_id BIGINT FK,
    workflow_context JSON,
    generated_page_definition JSON,
    design_compliance_score INT,
    refinement_count INT DEFAULT 0,
    total_cost_usd DECIMAL(10,6),
    status ENUM('generating', 'completed', 'failed'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## Core Service Implementation

### File: `app/Services/AI/OpenAIService.php`

**Key Methods:**
```php
public function chat(array $messages, ?string $model = null, array $options = []): array
public function calculateCost(int $inputTokens, int $outputTokens, string $model): float
public function estimateTokens(string $text): int
```

**Usage:**
```php
$service = new OpenAIService();

$response = $service->chat([
    ['role' => 'system', 'content' => $systemPrompt],
    ['role' => 'user', 'content' => $userPrompt]
], 'gpt-5.4-turbo');

$content = $response['choices'][0]['message']['content'];
$tokensIn = $response['usage']['prompt_tokens'];
$tokensOut = $response['usage']['completion_tokens'];
$cost = $service->calculateCost($tokensIn, $tokensOut, 'gpt-5.4-turbo');
```

---

## Pricing (April 2026)

### OpenAI GPT-5.4 Turbo
- **Input**: $0.15 per 1M tokens
- **Output**: $0.60 per 1M tokens
- **Average cost per generation**: ~$0.30 (500 input + 200 output tokens)

### Cost Calculation
```php
// Example: 500 input tokens, 200 output tokens
$inputCost = (500 / 1_000_000) * 0.15 = $0.000075
$outputCost = (200 / 1_000_000) * 0.60 = $0.000120
$totalCost = $0.000195 (~$0.20 per generation)
```

### Monthly Budget Example
- 1000 generations/month × $0.20 = **$200/month**
- 5000 generations/month × $0.20 = **$1000/month**

---

## Rate Limiting Strategy

### Per User
```php
// Laravel RateLimiter
$key = 'ai-generation:' . auth()->id();
$perMinute = 10;  // 10 requests per minute
$perHour = 100;   // 100 requests per hour

if (RateLimiter::tooManyAttempts($key, $perMinute)) {
    throw new TooManyRequestsException();
}

RateLimiter::hit($key, 60); // 60 seconds window
```

### Per Organization
```php
$orgKey = 'ai-generation-org:' . auth()->user()->org_id;
$orgLimit = 500; // 500 requests per hour per org

if (RateLimiter::tooManyAttempts($orgKey, $orgLimit)) {
    throw new OrganizationLimitException();
}
```

---

## Prompt Engineering for UI Generation

### System Prompt Structure
```
You are a UI generator for Arrahnumation V3 (Islamic pawn broking platform).

WORKFLOW CONTEXT:
{workflow_nodes}
{commands}
{data_models}

V3 UI DESIGN GUIDELINES (Apple/iOS Inspired):
- Typography: SF Pro, 17pt body, hierarchy via weight
- Spacing: 8pt base unit, 16/24/32pt padding
- Colors: Semantic only (systemBlue #007AFF, systemGray, etc.)
- Components: Liquid Glass material (translucent, backdrop-filter)
- Touch targets: Minimum 44x44pt
- Icons: SF Symbols, 2pt stroke, monochrome

APPROVED COMPONENTS:
{component_library}

DOMAIN CONSTRAINTS:
{arrahnu_business_rules}

OUTPUT:
Generate valid PageDefinition JSON with:
- Multi-step form structure
- Proper field bindings to database tables
- Validation rules (IC format, amount > 0, etc.)
- Liquid Glass styling
- 90%+ design compliance

Example:
{example_page_definition}
```

### Token Optimization
- Keep workflow context concise (max 1000 tokens)
- Use abbreviated component specs
- Reference examples by ID instead of full JSON
- Cache common prompt sections

---

## Error Handling

### Retry Logic
```php
$maxRetries = 3;
$attempt = 0;

while ($attempt < $maxRetries) {
    try {
        $response = $this->callOpenAI($prompt);
        break;
    } catch (Exception $e) {
        $attempt++;
        if ($attempt >= $maxRetries) {
            throw $e;
        }
        sleep(pow(2, $attempt)); // Exponential backoff: 2s, 4s, 8s
    }
}
```

### Error Types
1. **API Unavailable** (503) → Retry with backoff
2. **Rate Limited** (429) → Wait and retry
3. **Invalid Response** → Log and show user-friendly error
4. **Timeout** (60s) → Cancel and allow retry
5. **Invalid JSON** → Parse error, request regeneration

---

## Cost Monitoring

### Artisan Command
```bash
php artisan ai:check-costs

# Output:
# Today's AI cost: $12.45
# ┌──────────┬────────────────┬──────────┬─────────┐
# │ Provider │ Model          │ Requests │ Cost    │
# ├──────────┼────────────────┼──────────┼─────────┤
# │ openai   │ gpt-5.4-turbo  │ 62       │ $12.45  │
# └──────────┴────────────────┴──────────┴─────────┘
```

### Scheduled Monitoring
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('ai:check-costs')->hourly();
}
```

### Alert Thresholds
- **80% of budget** → Warning banner in HQ Studio
- **100% of budget** → Disable AI generation, send email alert
- **Daily spike** → Notify if today's cost > 2× average

---

## Security Best Practices

### 1. API Key Protection
```php
// ✅ CORRECT
$apiKey = config('ai.providers.openai.api_key');

// ❌ WRONG
$apiKey = "sk-proj-xxxxx"; // Hardcoded
```

### 2. Input Sanitization
```php
private function sanitizeWorkflowContext(array $context): array
{
    // Remove sensitive data
    unset($context['api_keys']);
    unset($context['passwords']);
    
    // Limit size
    $json = json_encode($context);
    if (strlen($json) > 10000) {
        throw new ContextTooLargeException();
    }
    
    return $context;
}
```

### 3. Output Validation
```php
private function validateGeneratedUI(array $pageDefinition): void
{
    // Check for injection attempts
    $json = json_encode($pageDefinition);
    if (preg_match('/<script|javascript:|onerror=/i', $json)) {
        throw new SecurityException('Potential XSS detected');
    }
    
    // Validate against schema
    $validator = new PageDefinitionValidator();
    if (!$validator->validate($pageDefinition)) {
        throw new InvalidSchemaException();
    }
}
```

---

## Testing Strategy

### Unit Tests
```php
// tests/Unit/OpenAIServiceTest.php
public function test_calculates_cost_correctly()
{
    $service = new OpenAIService();
    $cost = $service->calculateCost(500, 200, 'gpt-5.4-turbo');
    
    $this->assertEquals(0.000195, $cost);
}
```

### Integration Tests
```php
// tests/Feature/UIGenerationTest.php
public function test_generates_valid_page_definition()
{
    $generator = new UIGenerator();
    $workflow = factory(FlowDefinition::class)->create();
    
    $result = $generator->generate($workflow);
    
    $this->assertArrayHasKey('page_key', $result);
    $this->assertArrayHasKey('steps', $result);
    $this->assertGreaterThanOrEqual(90, $result['compliance_score']);
}
```

### Cost Tests
```php
public function test_respects_budget_limits()
{
    // Set low budget
    config(['ai.cost_threshold' => 0.01]);
    
    // Exceed budget
    factory(AIUsageLog::class, 100)->create([
        'org_id' => 1,
        'cost_usd' => 0.001,
        'used_at' => now()
    ]);
    
    $this->expectException(BudgetExceededException::class);
    
    $generator = new UIGenerator();
    $generator->generate($workflow);
}
```

---

## Performance Optimization

### 1. Prompt Caching
```php
// Cache common prompt sections
$designGuidelines = Cache::remember('ai-prompt-design-guidelines', 3600, function() {
    return file_get_contents(resource_path('ai/design-guidelines.txt'));
});
```

### 2. Parallel Validation
```php
// Validate while AI is generating
$promise = Http::async()->post($apiUrl, $payload);

// Do other work
$validator = new WorkflowContextValidator();
$validator->validate($context);

// Wait for AI response
$response = $promise->wait();
```

### 3. Response Streaming (Future)
```php
// For real-time preview (Phase 2)
public function streamGeneration(FlowDefinition $flow): Generator
{
    foreach ($this->openAI->streamChat($messages) as $chunk) {
        yield $chunk; // Send to frontend via SSE
    }
}
```

---

## Migration Path

### Phase 1: Basic Generation (Week 1-2)
- ✅ OpenAI service setup
- ✅ Basic prompt engineering
- ✅ Cost tracking
- ✅ Simple UI generation

### Phase 2: Design System Enforcement (Week 3)
- ✅ V3 UI guidelines in prompts
- ✅ Component library restriction
- ✅ Design compliance validation
- ✅ Compliance reporting

### Phase 3: Visual Refinement Engine (Week 4-5)
- ✅ Aspect detection
- ✅ Option generation
- ✅ Refinement modal
- ✅ Structured instructions

### Phase 4: Production Hardening (Week 6)
- ✅ Rate limiting
- ✅ Budget controls
- ✅ Error handling
- ✅ Monitoring dashboard

---

## Key Differences from V3-ai-doc.md

| Aspect | V3-ai-doc.md (Chat) | Our Implementation (UI Gen) |
|--------|---------------------|----------------------------|
| **Use Case** | Chat assistant | UI generation |
| **Interaction** | Conversational | Single-shot generation |
| **Context** | Conversation history | Workflow context |
| **Output** | Text response | Structured JSON |
| **Validation** | None | Design compliance check |
| **Refinement** | Natural language | Visual options |
| **Storage** | ai_messages table | ai_generation_sessions |

---

## References

- V3-ai-doc.md: Full AI implementation guide
- V3-UI-design.md: Design system guidelines
- V3-BLUEPRINT.md: Platform architecture
- OpenAI API Docs: https://platform.openai.com/docs
- OpenAI Pricing: https://openai.com/api/pricing

---

*Last Updated: 20 April 2026*
*Based on: V3-ai-doc.md + Requirements Document*
