<?php

namespace Tests\Feature\Runtime;

use App\Models\User;
use App\Runtime\Automation\ExecutionContext;
use App\Runtime\Automation\Nodes\FormulaNodeRunner;
use App\Runtime\Models\AutomationExecutionLog;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FlowNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormulaNodeRunnerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Formula Test',
            'email' => 'formula-test@arrahnu.com',
            'password' => 'password',
            'role' => 'branch_staff',
            'entity_id' => 1,
            'branch_id' => 1,
        ]);

        $this->actingAs($this->user);
    }

    public function test_formula_node_evaluates_simple_multiplication()
    {
        $feature = Feature::create(['key' => 'formula_test', 'name' => 'Formula', 'domain' => 'facility']);
        $version = $feature->versions()->create(['version_no' => 1, 'status' => 'published']);
        $flow = $version->flows()->create([
            'key' => 'calc_flow', 'name' => 'Calc Flow', 'trigger_type' => 'manual_entry',
        ]);

        $executionLog = AutomationExecutionLog::create([
            'flow_definition_id' => $flow->id,
            'feature_version_id' => $version->id,
            'trigger_type' => 'manual_entry',
            'status' => 'running',
            'started_at' => now(),
        ]);

        $context = new ExecutionContext($executionLog, [
            'form' => [
                'weight' => 10,
                'gold_price' => 250,
            ],
        ]);

        $node = new FlowNode();
        $node->node_key = 'calc_value';
        $node->node_type = 'formula';
        $node->config = [
            'formula' => 'weight * gold_price',
            'variables' => [
                'weight' => 'form.weight',
                'gold_price' => 'form.gold_price',
            ],
            'output_key' => 'marhun_value',
        ];

        $runner = new FormulaNodeRunner();
        $output = $runner->run($node, $context);

        $this->assertEquals(2500, $output['result']);
        $this->assertEquals('marhun_value', $output['output_key']);

        // Verify context was updated
        $this->assertEquals(2500, $context->get('formula.marhun_value'));
    }

    public function test_formula_node_evaluates_ltv_calculation()
    {
        $feature = Feature::create(['key' => 'ltv_test', 'name' => 'LTV', 'domain' => 'facility']);
        $version = $feature->versions()->create(['version_no' => 1, 'status' => 'published']);
        $flow = $version->flows()->create([
            'key' => 'ltv_flow', 'name' => 'LTV Flow', 'trigger_type' => 'manual_entry',
        ]);

        $executionLog = AutomationExecutionLog::create([
            'flow_definition_id' => $flow->id,
            'feature_version_id' => $version->id,
            'trigger_type' => 'manual_entry',
            'status' => 'running',
            'started_at' => now(),
        ]);

        // Simulate: principal = 5000, marhun_value = 7000
        // LTV = principal / marhun_value = 0.714...
        $context = new ExecutionContext($executionLog, [
            'form' => ['principal' => 5000],
            'formula' => ['marhun_value' => 7000],
        ]);

        $node = new FlowNode();
        $node->node_key = 'calc_ltv';
        $node->node_type = 'formula';
        $node->config = [
            'formula' => 'principal / marhun_value',
            'variables' => [
                'principal' => 'form.principal',
                'marhun_value' => 'formula.marhun_value',
            ],
            'output_key' => 'ltv_ratio',
        ];

        $runner = new FormulaNodeRunner();
        $output = $runner->run($node, $context);

        $this->assertEqualsWithDelta(0.7142, $output['result'], 0.001);
        $this->assertEquals('ltv_ratio', $output['output_key']);
    }

    public function test_formula_node_evaluates_ujrah_calculation()
    {
        $feature = Feature::create(['key' => 'ujrah_test', 'name' => 'Ujrah', 'domain' => 'facility']);
        $version = $feature->versions()->create(['version_no' => 1, 'status' => 'published']);
        $flow = $version->flows()->create([
            'key' => 'ujrah_flow', 'name' => 'Ujrah Flow', 'trigger_type' => 'manual_entry',
        ]);

        $executionLog = AutomationExecutionLog::create([
            'flow_definition_id' => $flow->id,
            'feature_version_id' => $version->id,
            'trigger_type' => 'manual_entry',
            'status' => 'running',
            'started_at' => now(),
        ]);

        $context = new ExecutionContext($executionLog, [
            'form' => ['principal' => 5000],
        ]);

        // Ujrah = principal * 0.005 (0.5% monthly rate)
        $node = new FlowNode();
        $node->node_key = 'calc_ujrah';
        $node->node_type = 'formula';
        $node->config = [
            'formula' => 'principal * 0.005',
            'variables' => [
                'principal' => 'form.principal',
            ],
            'output_key' => 'monthly_ujrah',
        ];

        $runner = new FormulaNodeRunner();
        $output = $runner->run($node, $context);

        $this->assertEquals(25.0, $output['result']);
        $this->assertEquals(25.0, $context->get('formula.monthly_ujrah'));
    }

    public function test_formula_node_throws_on_missing_formula()
    {
        $feature = Feature::create(['key' => 'err_test', 'name' => 'Err', 'domain' => 'facility']);
        $version = $feature->versions()->create(['version_no' => 1, 'status' => 'draft']);
        $flow = $version->flows()->create([
            'key' => 'err_flow', 'name' => 'Err Flow', 'trigger_type' => 'manual_entry',
        ]);

        $executionLog = AutomationExecutionLog::create([
            'flow_definition_id' => $flow->id,
            'feature_version_id' => $version->id,
            'trigger_type' => 'manual_entry',
            'status' => 'running',
            'started_at' => now(),
        ]);

        $context = new ExecutionContext($executionLog);

        $node = new FlowNode();
        $node->node_key = 'bad_formula';
        $node->node_type = 'formula';
        $node->config = []; // No formula defined

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No formula defined');

        $runner = new FormulaNodeRunner();
        $runner->run($node, $context);
    }
}
