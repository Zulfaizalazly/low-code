<?php

namespace Tests\Feature\Runtime;

use App\Domain\Customer\Commands\RegisterCustomer;
use App\Runtime\Automation\FlowOrchestrator;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FeatureVersion;
use App\Studio\Registry\FlowDefinition;
use App\Studio\Registry\FlowNode;
use App\Studio\Registry\FlowEdge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class FlowOrchestratorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::create([
            'name' => 'HQ Admin',
            'email' => 'hq@example.com',
            'password' => 'password',
            'role' => 'hq_admin',
            'entity_id' => 1,
            'branch_id' => 1,
        ]);

        $this->actingAs($this->user);
    }

    public function test_orchestrator_can_execute_a_simple_linear_flow()
    {
        // 1. Setup Registry
        $feature = Feature::create(['key' => 'test_feature', 'name' => 'Test', 'domain' => 'customer']);
        $version = $feature->versions()->create(['version_no' => 1, 'status' => 'published']);
        $flow = $version->flows()->create([
            'key' => 'customer_onboarding',
            'name' => 'Customer Onboarding',
            'trigger_type' => 'manual_entry'
        ]);

        // Nodes
        $startNode = $flow->nodes()->create([
            'node_key' => 'start',
            'node_type' => 'trigger',
            'label' => 'Start'
        ]);

        $commandNode = $flow->nodes()->create([
            'node_key' => 'register_customer',
            'node_type' => 'command',
            'label' => 'Create Customer',
            'config' => [
                'command_class' => RegisterCustomer::class,
                'payload_mapping' => [
                    'name' => 'trigger.name',
                    'icNumber' => 'trigger.ic',
                    'email' => 'trigger.email',
                ]
            ]
        ]);

        $endNode = $flow->nodes()->create([
            'node_key' => 'end',
            'node_type' => 'end',
            'label' => 'Finish'
        ]);

        // Edges
        $flow->edges()->create([
            'source_node_id' => $startNode->id,
            'target_node_id' => $commandNode->id,
            'condition_type' => 'always'
        ]);

        $flow->edges()->create([
            'source_node_id' => $commandNode->id,
            'target_node_id' => $endNode->id,
            'condition_type' => 'always'
        ]);

        // 2. Execute
        $orchestrator = app(FlowOrchestrator::class);
        $triggerData = [
            'trigger' => [
                'name' => 'Flow Bot',
                'ic' => 'FLOW-001',
                'email' => 'bot@flow.com',
            ]
        ];

        $executionLog = $orchestrator->execute($flow, $triggerData);

        // 3. Verify
        $this->assertEquals('completed', $executionLog->status);
        $this->assertCount(3, $executionLog->nodeLogs);

        // Verify that the Phase 1 Command actually worked
        $this->assertDatabaseHas('customers', [
            'name' => 'Flow Bot',
            'ic_number' => 'FLOW-001',
        ]);

        // Verify Node Trace
        $this->assertDatabaseHas('automation_node_logs', [
            'execution_log_id' => $executionLog->id,
            'node_key' => 'register_customer',
            'status' => 'completed'
        ]);
    }
}
