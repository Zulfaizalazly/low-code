<?php

namespace Tests\Feature\Runtime;

use App\Domain\Facility\Models\Facility;
use App\Models\User;
use App\Runtime\Automation\ExecutionContext;
use App\Runtime\Automation\Nodes\DocumentNodeRunner;
use App\Runtime\Models\AutomationExecutionLog;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FlowNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentNodeRunnerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Doc Test Staff',
            'email' => 'doc-test@arrahnu.com',
            'password' => 'password',
            'role' => 'branch_staff',
            'entity_id' => 1,
            'branch_id' => 1,
        ]);

        $this->actingAs($this->user);
    }

    public function test_document_node_generates_document()
    {
        $customer = \App\Domain\Customer\Models\Customer::create([
            'name' => 'Doc Test Customer',
            'ic_number' => 'DOC-IC-001',
            'status' => 'active',
        ]);

        $facility = Facility::create([
            'customer_id' => $customer->id,
            'facility_number' => 'F-DOC-001',
            'product_code' => 'ARRAHNU_GOLD',
            'branch_id' => 1,
            'entity_id' => 1,
            'principal_amount' => 5000,
            'status' => 'active',
        ]);

        $feature = Feature::create(['key' => 'doc_test', 'name' => 'Doc', 'domain' => 'facility']);
        $version = $feature->versions()->create(['version_no' => 1, 'status' => 'published']);
        $flow = $version->flows()->create([
            'key' => 'doc_flow', 'name' => 'Doc Flow', 'trigger_type' => 'manual_entry',
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
                'create_facility' => [
                    'output' => [
                        'class' => Facility::class,
                        'id' => $facility->id,
                    ],
                ],
            ],
        ]);

        $node = new FlowNode();
        $node->node_key = 'generate_contract';
        $node->node_type = 'document';
        $node->config = [
            'documentable_type' => 'nodes.create_facility.output.class',
            'documentable_id' => 'nodes.create_facility.output.id',
            'document_type' => 'contract',
        ];

        $runner = app(DocumentNodeRunner::class);
        $output = $runner->run($node, $context);

        $this->assertEquals('contract', $output['document_type']);
        $this->assertNotNull($output['document_id']);
        $this->assertNotNull($output['file_path']);

        $this->assertDatabaseHas('documents', [
            'documentable_type' => Facility::class,
            'documentable_id' => $facility->id,
            'document_type' => 'contract',
        ]);
    }

    public function test_document_node_simulation_mode()
    {
        $feature = Feature::create(['key' => 'doc_sim', 'name' => 'Doc Sim', 'domain' => 'facility']);
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
        $node->node_key = 'gen_doc_sim';
        $node->node_type = 'document';
        $node->config = [
            'documentable_type' => 'App\\Domain\\Facility\\Models\\Facility',
            'documentable_id' => 99,
            'document_type' => 'receipt',
        ];

        $runner = app(DocumentNodeRunner::class);
        $output = $runner->run($node, $context);

        $this->assertTrue($output['simulated_execution']);
        $this->assertEquals('skipped (simulation)', $output['status']);
        $this->assertDatabaseCount('documents', 0);
    }
}
