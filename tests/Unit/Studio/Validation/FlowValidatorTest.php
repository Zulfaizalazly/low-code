<?php

namespace Tests\Unit\Studio\Validation;

use Tests\TestCase;
use App\Studio\Validation\FlowValidator;
use App\Studio\Registry\FlowDefinition;
use App\Studio\Registry\FlowNode;
use App\Studio\Registry\FlowEdge;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FeatureVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FlowValidatorTest extends TestCase
{
    use RefreshDatabase;

    protected FlowValidator $validator;
    protected FeatureVersion $version;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new FlowValidator();
        
        // Create feature version using factory
        $this->version = FeatureVersion::factory()->create();
    }

    public function test_it_fails_if_no_trigger_node()
    {
        $flow = FlowDefinition::factory()->create([
            'feature_version_id' => $this->version->id,
        ]);
        
        $result = $this->validator->validate($flow);
        
        $this->assertFalse($result['valid']);
        $this->assertContains("Flow missing a trigger node.", $result['errors']);
    }

    public function test_it_fails_if_no_end_node()
    {
        $flow = FlowDefinition::factory()->create([
            'feature_version_id' => $this->version->id,
        ]);
        $flow->nodes()->create([
            'node_key' => 'trig',
            'node_type' => 'trigger',
            'label' => 'Start',
            'position_x' => 0, 'position_y' => 0
        ]);

        $result = $this->validator->validate($flow);
        
        $this->assertFalse($result['valid']);
        $this->assertContains("Flow missing an end node.", $result['errors']);
    }

    public function test_it_detects_unreachable_nodes()
    {
        $flow = FlowDefinition::factory()->create([
            'feature_version_id' => $this->version->id,
        ]);
        $trig = $flow->nodes()->create(['node_key' => 'trig', 'node_type' => 'trigger', 'label' => 'Start', 'position_x' => 0, 'position_y' => 0]);
        $end = $flow->nodes()->create(['node_key' => 'end', 'node_type' => 'end', 'label' => 'End', 'position_x' => 200, 'position_y' => 0]);
        $orphan = $flow->nodes()->create(['node_key' => 'orphan', 'node_type' => 'command', 'label' => 'Orphan', 'position_x' => 100, 'position_y' => 100]);

        $flow->edges()->create(['source_node_id' => $trig->id, 'target_node_id' => $end->id]);

        $result = $this->validator->validate($flow);
        
        $this->assertFalse($result['valid']);
        $this->assertContains("Node 'Orphan' is unreachable (no incoming edges).", $result['errors']);
    }

    public function test_it_detects_circular_dependencies()
    {
        $flow = FlowDefinition::factory()->create([
            'feature_version_id' => $this->version->id,
        ]);
        $n1 = $flow->nodes()->create(['node_key' => 'n1', 'node_type' => 'command', 'label' => 'N1', 'position_x' => 0, 'position_y' => 0]);
        $n2 = $flow->nodes()->create(['node_key' => 'n2', 'node_type' => 'command', 'label' => 'N2', 'position_x' => 100, 'position_y' => 0]);
        
        $flow->edges()->create(['source_node_id' => $n1->id, 'target_node_id' => $n2->id]);
        $flow->edges()->create(['source_node_id' => $n2->id, 'target_node_id' => $n1->id]);

        $result = $this->validator->validate($flow);
        
        $this->assertFalse($result['valid']);
        $this->assertContains("Flow contains a circular dependency (infinite loop).", $result['errors']);
    }
}
