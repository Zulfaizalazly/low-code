<?php

namespace Tests\Feature\Studio;

use Tests\TestCase;
use App\Models\User;
use App\Studio\Registry\FlowDefinition;
use App\Studio\Registry\PageDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Studio\FlowCanvasProxy;
use App\Livewire\Studio\PageBuilderProxy;

class VisualBuilderPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'hq_admin']));
    }

    /** @test */
    public function it_can_save_flow_state_from_canvas()
    {
        $flow = FlowDefinition::create(['name' => 'Test Flow', 'key' => 'test-flow']);
        
        $nodes = [
            [
                'node_key' => 'trig_1',
                'node_type' => 'trigger',
                'label' => 'Start',
                'config' => ['trigger_type' => 'manual_start'],
                'position_x' => 100,
                'position_y' => 100
            ],
            [
                'node_key' => 'end_1',
                'node_type' => 'end',
                'label' => 'End',
                'config' => [],
                'position_x' => 500,
                'position_y' => 100
            ]
        ];

        $edges = [
            [
                'source_node_key' => 'trig_1',
                'target_node_key' => 'end_1',
                'condition_type' => 'always',
                'condition_config' => []
            ]
        ];

        Livewire::test(FlowCanvasProxy::class, ['flowId' => $flow->id])
            ->set('nodes', $nodes)
            ->set('edges', $edges)
            ->call('saveFlowState', $nodes, $edges);

        $this->assertDatabaseHas('flow_nodes', [
            'flow_definition_id' => $flow->id,
            'node_key' => 'trig_1',
            'node_type' => 'trigger'
        ]);

        $this->assertDatabaseHas('flow_edges', [
            'flow_definition_id' => $flow->id,
            'source_node_id' => $flow->nodes()->where('node_key', 'trig_1')->first()->id,
            'target_node_id' => $flow->nodes()->where('node_key', 'end_1')->first()->id
        ]);
    }

    /** @test */
    public function it_can_save_page_state_from_builder()
    {
        $page = PageDefinition::create(['name' => 'Test Page', 'key' => 'test-page']);
        
        $steps = [
            [
                'title' => 'Step 1',
                'order' => 1,
                'fields' => [
                    [
                        'field_key' => 'field_1',
                        'label' => 'Full Name',
                        'component_type' => 'text_input',
                        'order' => 1,
                        'is_required' => true,
                        'binding' => [
                            'target_entity' => 'customers',
                            'target_path' => 'name'
                        ]
                    ]
                ]
            ]
        ];

        Livewire::test(PageBuilderProxy::class, ['featureVersionId' => 1, 'pageId' => $page->id])
            ->call('savePageState', $steps);

        $this->assertDatabaseHas('form_steps', [
            'page_definition_id' => $page->id,
            'title' => 'Step 1'
        ]);

        $this->assertDatabaseHas('form_fields', [
            'label' => 'Full Name',
            'component_type' => 'text_input'
        ]);

        $this->assertDatabaseHas('field_bindings', [
            'target_entity' => 'customers',
            'target_path' => 'name'
        ]);
    }
}
