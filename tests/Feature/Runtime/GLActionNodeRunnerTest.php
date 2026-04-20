<?php

namespace Tests\Feature\Runtime;

use App\Models\User;
use App\Runtime\Automation\ExecutionContext;
use App\Runtime\Automation\Nodes\GLActionNodeRunner;
use App\Runtime\Models\AutomationExecutionLog;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FlowNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GLActionNodeRunnerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'GL Test Staff',
            'email' => 'gl-test@arrahnu.com',
            'password' => 'password',
            'role' => 'branch_staff',
            'entity_id' => 1,
            'branch_id' => 1,
        ]);

        $this->actingAs($this->user);
    }

    public function test_gl_action_node_posts_balanced_journal_entry()
    {
        $feature = Feature::create(['key' => 'gl_test', 'name' => 'GL', 'domain' => 'accounting']);
        $version = $feature->versions()->create(['version_no' => 1, 'status' => 'published']);
        $flow = $version->flows()->create([
            'key' => 'gl_flow', 'name' => 'GL Flow', 'trigger_type' => 'manual_entry',
        ]);

        $executionLog = AutomationExecutionLog::create([
            'flow_definition_id' => $flow->id,
            'feature_version_id' => $version->id,
            'trigger_type' => 'manual_entry',
            'status' => 'running',
            'started_at' => now(),
        ]);

        $context = new ExecutionContext($executionLog, [
            'form' => ['amount' => 5000],
        ]);

        $node = new FlowNode();
        $node->node_key = 'post_gl';
        $node->node_type = 'gl_action';
        $node->config = [
            'description' => 'Pledge disbursement for {{form.amount}}',
            'lines' => [
                [
                    'account_code' => '1200',
                    'account_name' => 'Cash Disbursement',
                    'debit' => 'form.amount',
                    'credit' => 0,
                ],
                [
                    'account_code' => '2100',
                    'account_name' => 'Facility Receivable',
                    'debit' => 0,
                    'credit' => 'form.amount',
                ],
            ],
        ];

        $runner = app(GLActionNodeRunner::class);
        $output = $runner->run($node, $context);

        $this->assertTrue($output['is_balanced']);
        $this->assertEquals(2, $output['lines_count']);
        $this->assertNotNull($output['journal_entry_id']);
        $this->assertNotNull($output['entry_number']);

        $this->assertDatabaseHas('journal_entries', [
            'description' => 'Pledge disbursement for 5000',
            'is_balanced' => true,
        ]);

        $this->assertDatabaseHas('journal_entry_lines', [
            'account_code' => '1200',
            'debit_amount' => 5000,
            'credit_amount' => 0,
        ]);

        $this->assertDatabaseHas('journal_entry_lines', [
            'account_code' => '2100',
            'debit_amount' => 0,
            'credit_amount' => 5000,
        ]);
    }

    public function test_gl_action_node_simulation_mode()
    {
        $feature = Feature::create(['key' => 'gl_sim', 'name' => 'GL Sim', 'domain' => 'accounting']);
        $version = $feature->versions()->create(['version_no' => 1, 'status' => 'draft']);
        $flow = $version->flows()->create([
            'key' => 'sim_flow', 'name' => 'Sim Flow', 'trigger_type' => 'manual_entry',
        ]);

        $executionLog = AutomationExecutionLog::create([
            'flow_definition_id' => $flow->id,
            'feature_version_id' => $version->id,
            'trigger_type' => 'manual_entry',
            'status' => 'running',
            'started_at' => now(),
        ]);

        $context = new ExecutionContext($executionLog, [
            '_simulation' => true,
            'form' => ['amount' => 3000],
        ]);

        $node = new FlowNode();
        $node->node_key = 'post_gl_sim';
        $node->node_type = 'gl_action';
        $node->config = [
            'description' => 'Sim journal',
            'lines' => [
                ['account_code' => '1200', 'account_name' => 'Cash', 'debit' => 'form.amount', 'credit' => 0],
                ['account_code' => '2100', 'account_name' => 'Receivable', 'debit' => 0, 'credit' => 'form.amount'],
            ],
        ];

        $runner = app(GLActionNodeRunner::class);
        $output = $runner->run($node, $context);

        $this->assertTrue($output['simulated_execution']);
        $this->assertEquals(3000, $output['total_debit']);
        $this->assertEquals(3000, $output['total_credit']);
        $this->assertDatabaseCount('journal_entries', 0);
    }
}
