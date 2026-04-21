<?php

namespace Tests\Feature\Studio;

use App\Models\User;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FeatureVersion;
use App\Studio\Registry\FlowDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlowBuilderApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected FeatureVersion $version;
    protected FlowDefinition $flow;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
        
        $this->user = User::factory()->create();
        $this->user->assignRole('super-admin');
        
        $feature = Feature::factory()->create();
        $this->version = FeatureVersion::factory()->create([
            'feature_id' => $feature->id,
            'status' => 'draft',
        ]);
        
        $this->flow = FlowDefinition::factory()->create([
            'feature_version_id' => $this->version->id,
        ]);
    }

    public function test_can_save_flow_via_api()
    {
        $flowData = [
            'nodes' => [
                [
                    'id' => 'trigger-1',
                    'type' => 'trigger',
                    'position' => ['x' => 100, 'y' => 100],
                    'data' => ['label' => 'Start'],
                ],
                [
                    'id' => 'end-1',
                    'type' => 'end',
                    'position' => ['x' => 300, 'y' => 100],
                    'data' => ['label' => 'End'],
                ],
            ],
            'edges' => [
                [
                    'id' => 'e1',
                    'source' => 'trigger-1',
                    'target' => 'end-1',
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/studio/flows/{$this->flow->id}/save", $flowData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Flow saved successfully',
            ]);

        $this->assertDatabaseHas('flow_nodes', [
            'flow_definition_id' => $this->flow->id,
            'node_key' => 'trigger-1',
        ]);
    }

    public function test_can_validate_flow_via_api()
    {
        // Create a valid flow
        $trigger = $this->flow->nodes()->create([
            'node_key' => 'trigger-1',
            'node_type' => 'trigger',
            'label' => 'Start',
            'position_x' => 100,
            'position_y' => 100,
        ]);

        $endNode = $this->flow->nodes()->create([
            'node_key' => 'end-1',
            'node_type' => 'end',
            'label' => 'End',
            'position_x' => 300,
            'position_y' => 100,
        ]);

        // Connect trigger to end
        $this->flow->edges()->create([
            'source_node_id' => $trigger->id,
            'target_node_id' => $endNode->id,
            'condition_type' => 'always',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/studio/flows/{$this->flow->id}/validate");

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
            ]);
    }

    public function test_validation_fails_for_invalid_flow()
    {
        // Create flow without end node
        $this->flow->nodes()->create([
            'node_key' => 'trigger-1',
            'node_type' => 'trigger',
            'label' => 'Start',
            'position_x' => 100,
            'position_y' => 100,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/studio/flows/{$this->flow->id}/validate");

        $response->assertStatus(200)
            ->assertJson([
                'valid' => false,
            ])
            ->assertJsonStructure([
                'valid',
                'errors',
            ]);
    }

    public function test_can_simulate_flow_via_api()
    {
        // Create a simple flow
        $trigger = $this->flow->nodes()->create([
            'node_key' => 'trigger-1',
            'node_type' => 'trigger',
            'label' => 'Start',
            'position_x' => 100,
            'position_y' => 100,
        ]);

        $end = $this->flow->nodes()->create([
            'node_key' => 'end-1',
            'node_type' => 'end',
            'label' => 'End',
            'position_x' => 300,
            'position_y' => 100,
        ]);

        $this->flow->edges()->create([
            'source_node_id' => $trigger->id,
            'target_node_id' => $end->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/studio/flows/{$this->flow->id}/simulate", [
                'payload' => ['test' => 'data'],
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'execution_path',
                'node_outputs',
            ]);
    }

    public function test_unauthorized_user_cannot_save_flow()
    {
        $unauthorizedUser = User::factory()->create();
        $unauthorizedUser->assignRole('business-user');

        $response = $this->actingAs($unauthorizedUser)
            ->postJson("/api/studio/flows/{$this->flow->id}/save", [
                'nodes' => [],
                'edges' => [],
            ]);

        $response->assertStatus(403);
    }

    public function test_can_validate_flow_with_command_node()
    {
        $trigger = $this->flow->nodes()->create([
            'node_key' => 'trigger-1',
            'node_type' => 'trigger',
            'label' => 'Start',
            'position_x' => 100,
            'position_y' => 100,
        ]);

        $command = $this->flow->nodes()->create([
            'node_key' => 'command-1',
            'node_type' => 'command',
            'label' => 'Register Customer',
            'config' => [
                'command_class' => 'App\\Domain\\Customer\\Commands\\RegisterCustomer',
                'payload_mapping' => ['name' => 'form.name'],
            ],
            'position_x' => 200,
            'position_y' => 100,
        ]);

        $end = $this->flow->nodes()->create([
            'node_key' => 'end-1',
            'node_type' => 'end',
            'label' => 'End',
            'position_x' => 300,
            'position_y' => 100,
        ]);

        $this->flow->edges()->create([
            'source_node_id' => $trigger->id,
            'target_node_id' => $command->id,
        ]);

        $this->flow->edges()->create([
            'source_node_id' => $command->id,
            'target_node_id' => $end->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/studio/flows/{$this->flow->id}/validate");

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
            ]);
    }
}
