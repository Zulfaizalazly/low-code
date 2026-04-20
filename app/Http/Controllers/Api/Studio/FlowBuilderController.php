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
                $node = $flow->nodes()->create([
                    'node_key' => $nData['node_key'],
                    'node_type' => $nData['node_type'],
                    'label' => $nData['label'],
                    'config' => $nData['config'] ?? [],
                    'position_x' => $nData['position_x'],
                    'position_y' => $nData['position_y'],
                ]);
                $nodeMap[$nData['node_key']] = $node->id;
            }

            // Store new edges
            foreach ($edges as $eData) {
                $flow->edges()->create([
                    'source_node_id' => $nodeMap[$eData['source_node_key']] ?? null,
                    'target_node_id' => $nodeMap[$eData['target_node_key']] ?? null,
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
            $log = $orchestrator->execute($flow, $request->input('trigger_data', []), true);
            
            return response()->json([
                'success' => true,
                'status' => $log->status,
                'path' => $log->nodeLogs()->orderBy('started_at')->get()->map(fn($n) => [
                    'node_key' => $n->node_key,
                    'status' => $n->status,
                    'output' => $n->output_data
                ])
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
