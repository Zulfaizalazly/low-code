<?php

namespace App\Studio\Publishing;

use App\Studio\Registry\FeatureVersion;
use Illuminate\Support\Facades\DB;

class ImpactAnalyzer
{
    /**
     * Analyze the impact of publishing a feature version.
     *
     * Returns a structured report of all affected components:
     * - branches, roles, documents, notifications, GL entries
     */
    public function analyze(FeatureVersion $version): array
    {
        $version->load([
            'flows.nodes',
            'pages.steps.fields',
            'menuItems',
            'feature',
        ]);

        $report = [
            'feature_key' => $version->feature->key,
            'feature_name' => $version->feature->name,
            'version_no' => $version->version_no,
            'analyzed_at' => now()->toISOString(),
            'affected_roles' => $this->analyzeAffectedRoles($version),
            'affected_branches' => $this->analyzeAffectedBranches($version),
            'automation_outputs' => $this->analyzeAutomationOutputs($version),
            'ui_impact' => $this->analyzeUIImpact($version),
            'data_impact' => $this->analyzeDataImpact($version),
            'risk_level' => 'low', // computed below
        ];

        $report['risk_level'] = $this->computeRisk($report);

        // Persist the report
        DB::table('impact_analysis_reports')->insert([
            'feature_version_id' => $version->id,
            'report_data' => json_encode($report),
            'risk_level' => $report['risk_level'],
            'analyzed_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $report;
    }

    /**
     * Determine which roles are affected by this feature.
     */
    private function analyzeAffectedRoles(FeatureVersion $version): array
    {
        $roles = [];

        // From menu items — which roles can see/access this feature
        foreach ($version->menuItems as $menuItem) {
            // Menu items may have allowed_roles in a config/visibility_rule
            $roles[$menuItem->label] = [
                'role' => 'Any (Unrestricted)',
                'source' => 'menu_item',
                'permission' => 'view',
                'is_new' => false, // Would need comparison with previous version to be accurate
            ];
        }

        // From approval nodes — which roles process approvals
        foreach ($version->flows as $flow) {
            foreach ($flow->nodes->where('node_type', 'approval') as $node) {
                $assignedRole = $node->config['assigned_role'] ?? 'any';
                $roles[$assignedRole] = [
                    'role' => $assignedRole,
                    'source' => 'approval_node',
                    'permission' => 'approve',
                    'flow' => $flow->name,
                    'is_new' => true,
                ];
            }
        }

        return array_values($roles);
    }

    /**
     * Determine which branches are affected.
     */
    private function analyzeAffectedBranches(FeatureVersion $version): array
    {
        // Check if there are scope overrides for this version
        $overrides = DB::table('scoped_overrides') // Fixed table name from previous research
            ->where('feature_version_id', $version->id)
            ->get();

        if ($overrides->isEmpty()) {
            return [
                'scope' => 'all_branches',
                'note' => 'No scope overrides — feature applies to all branches.',
                'count' => 'all',
                'branches' => ['*'],
            ];
        }

        $branches = $overrides->where('scope_type', 'branch')->pluck('scope_id')->toArray();

        return [
            'scope' => 'scoped',
            'branches' => $branches,
            'count' => count($branches),
        ];
    }

    /**
     * Analyze what automation outputs this feature produces.
     */
    private function analyzeAutomationOutputs(FeatureVersion $version): array
    {
        $outputs = [
            'documents' => [],
            'notifications' => [],
            'gl_entries' => [],
            'approvals' => [],
            'formulas' => [],
        ];

        foreach ($version->flows as $flow) {
            foreach ($flow->nodes as $node) {
                match ($node->node_type) {
                    'document' => $outputs['documents'][] = $this->extractDocumentImpact($node, $flow),
                    'notification' => $outputs['notifications'][] = [
                        'node_key' => $node->node_key,
                        'channel' => $node->config['channel'] ?? 'email',
                        'flow' => $flow->name,
                    ],
                    'gl_action' => $outputs['gl_entries'][] = [
                        'node_key' => $node->node_key,
                        'description' => $node->config['description'] ?? '',
                        'lines_count' => count($node->config['lines'] ?? []),
                        'flow' => $flow->name,
                    ],
                    'approval' => $outputs['approvals'][] = [
                        'node_key' => $node->node_key,
                        'tier' => $node->config['approval_tier'] ?? 'tier_1',
                        'role' => $node->config['assigned_role'] ?? 'any',
                        'flow' => $flow->name,
                    ],
                    'formula' => $outputs['formulas'][] = [
                        'node_key' => $node->node_key,
                        'formula' => $node->config['formula'] ?? '',
                        'output_key' => $node->config['output_key'] ?? '',
                        'flow' => $flow->name,
                    ],
                    default => null,
                };
            }
        }

        return $outputs;
    }

    /**
     * Helper to extract document impact and verify template existence.
     */
    private function extractDocumentImpact($node, $flow): array
    {
        $templateKey = $node->config['document_type'] ?? null;
        $exists = false;
        
        if ($templateKey) {
            $exists = DB::table('document_templates')
                ->where('key', $templateKey)
                ->where('is_active', true)
                ->exists();
        }

        return [
            'node_key' => $node->node_key,
            'document_type' => $templateKey,
            'template_exists' => $exists,
            'risk' => $exists ? 'low' : 'high',
            'flow' => $flow->name,
        ];
    }

    /**
     * Analyze UI impact — pages, fields, menu items.
     */
    private function analyzeUIImpact(FeatureVersion $version): array
    {
        $pages = [];

        foreach ($version->pages as $page) {
            $fieldCount = $page->steps->flatMap->fields->count();
            $stepCount = $page->steps->count();

            $pages[] = [
                'page_key' => $page->key,
                'page_name' => $page->name,
                'page_type' => $page->page_type,
                'steps' => $stepCount,
                'fields' => $fieldCount,
                'is_entry_page' => $page->is_entry_page,
            ];
        }

        return [
            'pages' => $pages,
            'menu_items' => $version->menuItems->map(fn($m) => [
                'label' => $m->label,
                'route_key' => $m->route_key,
                'parent' => $m->parent_menu_key,
            ])->toArray(),
        ];
    }

    /**
     * Analyze data impact — which entities/tables are affected.
     */
    private function analyzeDataImpact(FeatureVersion $version): array
    {
        $entities = [];

        foreach ($version->pages as $page) {
            foreach ($page->steps as $step) {
                if ($step->entity_binding) {
                    $entities[] = $step->entity_binding;
                }

                foreach ($step->fields as $field) {
                    if ($field->binding && $field->binding->target_entity) {
                        $entities[] = $field->binding->target_entity;
                    }
                }
            }
        }

        return [
            'affected_entities' => array_values(array_unique($entities)),
            'count' => count(array_unique($entities)),
            'affected_reports' => $this->analyzeAffectedReports($entities),
        ];
    }

    /**
     * Identify reports that query affected entities.
     */
    private function analyzeAffectedReports(array $entities): array
    {
        if (empty($entities)) return [];

        // Skeleton: In a real system, we would query a 'reports' registry
        // for reports associated with these entities.
        return [
            [
                'name' => 'General Activity Report',
                'risk' => 'low',
                'note' => 'Queries multiple entities, may need refreshing.',
            ]
        ];
    }

    /**
     * Compute risk level based on FR-2.5 spec.
     */
    private function computeRisk(array $report): string
    {
        $score = 0;

        // 1. Branch Scope
        $branchCount = $report['affected_branches']['count'];
        if ($branchCount === 'all') {
            $score += 4;
        } elseif ($branchCount > 20) {
            $score += 3;
        } elseif ($branchCount > 5) {
            $score += 2;
        }

        // 2. Critical Side Effects (GL, Documents)
        $glCount = count($report['automation_outputs']['gl_entries']);
        if ($glCount > 0) $score += 3;

        $missingTemplates = collect($report['automation_outputs']['documents'])
            ->where('template_exists', false)->count();
        if ($missingTemplates > 0) $score += 4; // Missing templates is critical

        // 3. Data & Permissions
        if (count($report['affected_roles']) > 3) $score += 2;
        if ($report['data_impact']['count'] > 5) $score += 2;

        return match (true) {
            $score >= 10 => 'critical',
            $score >= 7 => 'high',
            $score >= 4 => 'medium',
            default => 'low',
        };
    }
}
