<?php

namespace App\Livewire\Studio;

use App\Studio\Registry\FlowDefinition;
use Livewire\Component;

class FlowCanvasProxy extends Component
{
    public FlowDefinition $flow;

    public function mount(int $flowId)
    {
        $this->flow = FlowDefinition::with(['nodes', 'edges'])->findOrFail($flowId);
    }

    /**
     * Handle state update from the Vue Flow island.
     */
    public function saveFlowState(array $nodes, array $edges)
    {
        // Persist the diagram state back to the database
        // This effectively completes the "Low-Code" bridge
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($nodes, $edges) {
            // 1. Clean old edges and nodes
            // (In production, we would perform an intelligent diff to keep IDs)
            $this->flow->nodes()->delete();
            $this->flow->edges()->delete();

            // 2. Re-create Nodes
            $nodeMap = [];
            foreach ($nodes as $node) {
                $newNode = $this->flow->nodes()->create([
                    'node_key' => $node['id'],
                    'node_type' => $node['type'] ?? 'step',
                    'label' => $node['data']['label'] ?? 'Untitled',
                    'config' => $node['data']['config'] ?? [],
                    'position_x' => (int) ($node['position']['x'] ?? 0),
                    'position_y' => (int) ($node['position']['y'] ?? 0),
                ]);
                $nodeMap[$node['id']] = $newNode->id;
            }

            // 3. Re-create Edges
            foreach ($edges as $edge) {
                $this->flow->edges()->create([
                    'source_node_id' => $nodeMap[$edge['source']] ?? null,
                    'target_node_id' => $nodeMap[$edge['target']] ?? null,
                    'condition_type' => $edge['data']['condition_type'] ?? 'always',
                    'condition_config' => $edge['data']['condition_config'] ?? [],
                ]);
            }
        });

        $this->dispatch('flow-saved');
    }

    public function render()
    {
        return view('livewire.studio.flow-canvas-proxy')
            ->layout('layouts.studio');
    }
}
