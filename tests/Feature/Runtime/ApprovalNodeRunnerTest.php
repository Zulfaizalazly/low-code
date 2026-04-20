<?php

namespace Tests\Feature\Runtime;

use App\Domain\Customer\Models\Customer;
use App\Models\User;
use App\Runtime\Automation\ExecutionContext;
use App\Runtime\Automation\Nodes\ApprovalNodeRunner;
use App\Runtime\Models\AutomationExecutionLog;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FlowNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalNodeRunnerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test Staff',
            'email' => 'test@arrahnu.com',
            'password' => 'password',
            'role' => 'branch_staff',
            'entity_id' => 1,
            'branch_id' => 1,
        ]);

        $this->actingAs($this->user);
    }

    public function test_approval_node_creates_approval_task()
    {
        // Setup: Create a customer to approve
        $customer = Customer::create([
            'name' => 'Test Customer',
            'ic_number' => 'APPROVE-001',
            'status' => 'active',
        ]);

        // Create context with the customer data
        $feature = Feature::create(['key' => 'test', 'name' => 'Test', 'domain' => 'customer']);
        $version = $feature->versions()->create(['version_no' => 1, 'status' => 'published']);
        $flow = $version->flows()->create([
            'key' => 'test_flow', 'name' => 'Test Flow', 'trigger_type' => 'manual_entry',
        ]);

        $executionLog = AutomationExecutionLog::create([
            'flow_definition_id' => $flow->id,
            'feature_version_id' => $version->id,
            'trigger_type' => 'manual_entry',
            'status' => 'running',
            'started_at' => now(),
        ]);

        $context = new ExecutionContext($executionLog, [
            'nodes' => [
                'register_customer' => [
                    'output' => [
                        'class' => Customer::class,
                        'id' => $customer->id,
                    ],
                ],
            ],
        ]);

        $node = new FlowNode();
        $node->node_key = 'approve_customer';
        $node->node_type = 'approval';
        $node->config = [
            'approvable_type' => 'nodes.register_customer.output.class',
            'approvable_id' => 'nodes.register_customer.output.id',
            'approval_tier' => 'tier_1',
            'assigned_role' => 'branch_manager',
        ];

        $runner = app(ApprovalNodeRunner::class);
        $output = $runner->run($node, $context);

        $this->assertEquals('pending', $output['status']);
        $this->assertEquals('tier_1', $output['approval_tier']);
        $this->assertNotNull($output['approval_task_id']);

        $this->assertDatabaseHas('approval_tasks', [
            'approvable_type' => Customer::class,
            'approvable_id' => $customer->id,
            'approval_tier' => 'tier_1',
            'assigned_role' => 'branch_manager',
            'status' => 'pending',
        ]);
    }

    public function test_approval_node_simulation_mode_skips_dispatch()
    {
        $feature = Feature::create(['key' => 'sim_test', 'name' => 'Sim', 'domain' => 'customer']);
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

        $context = new ExecutionContext($executionLog, ['_simulation' => true]);

        $node = new FlowNode();
        $node->node_key = 'approve_sim';
        $node->node_type = 'approval';
        $node->config = [
            'approvable_type' => 'App\\Models\\User',
            'approvable_id' => 1,
            'approval_tier' => 'tier_2',
        ];

        $runner = app(ApprovalNodeRunner::class);
        $output = $runner->run($node, $context);

        $this->assertTrue($output['simulated_execution']);
        $this->assertEquals('skipped (simulation)', $output['status']);
        $this->assertDatabaseMissing('approval_tasks', ['approval_tier' => 'tier_2']);
    }
}
