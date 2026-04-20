<?php

namespace App\Http\Controllers\Api\Studio;

use App\Http\Controllers\Controller;
use App\Studio\Registry\FeatureVersion;
use App\Studio\Publishing\ImpactAnalyzer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ImpactAnalysisController extends Controller
{
    public function __construct(private ImpactAnalyzer $analyzer) {}

    /**
     * Get the latest impact analysis report for a version.
     */
    public function show(int $id): JsonResponse
    {
        $version = FeatureVersion::findOrFail($id);
        
        $report = DB::table('impact_analysis_reports')
            ->where('feature_version_id', $version->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$report) {
            return response()->json(['success' => false, 'message' => 'No analysis report found.'], 404);
        }

        return response()->json([
            'success' => true,
            'report' => json_decode($report->report_data, true),
            'risk_level' => $report->risk_level,
            'generated_at' => $report->created_at
        ]);
    }

    /**
     * Trigger a new impact analysis.
     */
    public function analyze(int $id): JsonResponse
    {
        $version = FeatureVersion::findOrFail($id);
        
        try {
            $report = $this->analyzer->analyze($version);
            
            return response()->json([
                'success' => true,
                'message' => 'Impact analysis completed successfully.',
                'report' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
