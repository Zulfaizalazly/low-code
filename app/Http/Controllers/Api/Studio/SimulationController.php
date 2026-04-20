<?php

namespace App\Http\Controllers\Api\Studio;

use App\Http\Controllers\Controller;
use App\Studio\Registry\FeatureVersion;
use App\Studio\Registry\FlowDefinition;
use App\Runtime\Simulation\FlowSimulator;
use App\Runtime\Simulation\Models\SimulationLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SimulationController extends Controller
{
    public function __construct(private FlowSimulator $simulator) {}

    /**
     * Run a simulation for a specific flow.
     */
    public function simulate(Request $request, int $versionId, string $flowKey): JsonResponse
    {
        $version = FeatureVersion::findOrFail($versionId);
        $flow = $version->flows()->where('key', $flowKey)->firstOrFail();
        
        try {
            $simulationLog = $this->simulator->simulate($flow, $request->input('input_data', []));
            
            return response()->json([
                'success' => true,
                'message' => 'Simulation completed.',
                'log' => $simulationLog
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get simulation history for a version.
     */
    public function history(int $versionId): JsonResponse
    {
        $version = FeatureVersion::findOrFail($versionId);
        
        $history = SimulationLog::where('feature_version_id', $version->id)
            ->with('executor')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'history' => $history
        ]);
    }

    /**
     * Get details of a specific simulation log.
     */
    public function show(int $simulationId): JsonResponse
    {
        $log = SimulationLog::with('executor')->findOrFail($simulationId);
        
        return response()->json([
            'success' => true,
            'log' => $log
        ]);
    }
}
