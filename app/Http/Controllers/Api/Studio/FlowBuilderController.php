<?php

namespace App\Http\Controllers\Api\Studio;

use App\Http\Controllers\Controller;
use App\Studio\Registry\FlowDefinition;
use App\Studio\Validation\FlowValidator;
use App\Runtime\Automation\FlowOrchestrator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class FlowBuilderController extends Controller
{
    public function save(Request $request, int $flowId): JsonResponse
    {
        $flow = FlowDefinition::findOrFail($flowId);
        $nodes = $request->input('nodes', []);
        $edges = $request->input('edges', []);

        DB::transaction(function () use ($flow, $nodes, $edges) {
            // Delete existing
            $flow->nodes()->delete();
            $flow->edges()->delete();

            // Store new nodes
            $nodeMap = [];
            foreach ($nodes as $nData) {
                // Support both formats: {id, type, position, data} and {node_key, node_type, ...}
                $nodeKey = $nData['node_key'] ?? $nData['id'] ?? null;
                $nodeType = $nData['node_type'] ?? $nData['type'] ?? null;
                $label = $nData['label'] ?? ($nData['data']['label'] ?? 'Untitled');
                $posX = $nData['position_x'] ?? ($nData['position']['x'] ?? 0);
                $posY = $nData['position_y'] ?? ($nData['position']['y'] ?? 0);
                
                $node = $flow->nodes()->create([
                    'node_key' => $nodeKey,
                    'node_type' => $nodeType,
                    'label' => $label,
                    'config' => $nData['config'] ?? [],
                    'position_x' => $posX,
                    'position_y' => $posY,
                ]);
                $nodeMap[$nodeKey] = $node->id;
            }

            // Store new edges
            foreach ($edges as $eData) {
                // Support both formats: {source, target} and {source_node_key, target_node_key}
                $sourceKey = $eData['source_node_key'] ?? $eData['source'] ?? null;
                $targetKey = $eData['target_node_key'] ?? $eData['target'] ?? null;
                
                $flow->edges()->create([
                    'source_node_id' => $nodeMap[$sourceKey] ?? null,
                    'target_node_id' => $nodeMap[$targetKey] ?? null,
                    'condition_type' => $eData['condition_type'] ?? 'always',
                    'condition_config' => $eData['condition_config'] ?? [],
                    'priority' => $eData['priority'] ?? 0,
                ]);
            }
        });

        return response()->json(['message' => 'Flow saved successfully']);
    }

    public function validate(Request $request, int $flowId): JsonResponse
    {
        $flow = FlowDefinition::findOrFail($flowId);
        $validator = new FlowValidator();
        $result = $validator->validate($flow);

        return response()->json($result);
    }

    public function simulate(Request $request, int $flowId): JsonResponse
    {
        $flow = FlowDefinition::findOrFail($flowId);
        $orchestrator = new FlowOrchestrator();
        
        try {
            // Run in simulation mode (Dry Run)
            $triggerData = $request->input('trigger_data', $request->input('payload', []));
            $log = $orchestrator->execute($flow, $triggerData, true);
            
            $nodeLogs = $log->nodeLogs()->orderBy('started_at')->get();
            
            return response()->json([
                'success' => true,
                'status' => $log->status,
                'execution_path' => $nodeLogs->pluck('node_key')->toArray(),
                'node_outputs' => $nodeLogs->mapWithKeys(fn($n) => [
                    $n->node_key => $n->output_data
                ])->toArray(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
