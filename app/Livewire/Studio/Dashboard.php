<?php

namespace App\Livewire\Studio;

use App\Studio\Registry\Feature;
use App\Studio\Registry\FlowDefinition;
use App\Studio\Registry\PageDefinition;
use App\Services\BlueprintRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class Dashboard extends Component
{
    // Create Feature Modal
    public bool $showCreateModal = false;
    public string $newFeatureName = '';
    public string $newFeatureKey = '';
    public string $newFeatureDomain = 'General';
    public string $selectedBlueprint = 'blank';

    // Auto-fill when blueprint changes
    public function updatedSelectedBlueprint($val): void
    {
        $autoFill = [
            'pledge_intake'      => ['Pledge Intake', 'pledge-intake', 'Facility'],
            'pledge_renewal'     => ['Pledge Renewal', 'pledge-renewal', 'Facility'],
            'pledge_redemption'  => ['Pledge Redemption', 'pledge-redemption', 'Facility'],
            'additional_margin'  => ['Additional Margin', 'additional-margin', 'Facility'],
            'margin_call'        => ['Margin Call', 'margin-call', 'Risk'],
            'auction_process'    => ['Auction Process', 'auction-process', 'Auction'],
            'payment_collection' => ['Payment Collection', 'payment-collection', 'Finance'],
            'vault_recon'        => ['Vault Reconciliation', 'vault-recon', 'Operations'],
            'kyc_update'         => ['Customer KYC Update', 'kyc-update', 'Customer'],
            'bnm_report'         => ['BNM Compliance Report', 'bnm-report', 'Compliance'],
        ];

        if (isset($autoFill[$val])) {
            [$this->newFeatureName, $this->newFeatureKey, $this->newFeatureDomain] = $autoFill[$val];
        }
    }

    // Auto-generate slug key from name
    public function updatedNewFeatureName(string $value): void
    {
        $this->newFeatureKey = Str::slug($value);
    }

    public function openCreateModal(): void
    {
        $this->reset(['newFeatureName', 'newFeatureKey', 'newFeatureDomain', 'selectedBlueprint']);
        $this->newFeatureDomain = 'General';
        $this->selectedBlueprint = 'blank';
        $this->showCreateModal = true;
    }

    public function createFeature(): void
    {
        $this->validate([
            'newFeatureName' => 'required|string|min:3|max:100',
            'newFeatureKey'  => 'required|string|regex:/^[a-z0-9\-]+$/|unique:features,key',
            'newFeatureDomain' => 'required|string',
        ], [
            'newFeatureKey.unique' => 'This key already exists. Choose a different name.',
            'newFeatureKey.regex'  => 'Key must be lowercase letters, numbers, and hyphens only.',
        ]);

        DB::transaction(function () {
            // 1. Create Feature record
            $feature = Feature::create([
                'key'    => $this->newFeatureKey,
                'name'   => $this->newFeatureName,
                'domain' => $this->newFeatureDomain,
                'status' => 'draft',
            ]);

            // 2. Create Version 1
            $version = $feature->versions()->create([
                'version_no' => 1,
                'status'     => 'draft',
            ]);

            // 3. Bootstrap a blank primary Flow
            $flow = FlowDefinition::create([
                'feature_version_id' => $version->id,
                'key'         => $this->newFeatureKey . '-flow',
                'name'        => $this->newFeatureName . ' Flow',
                'trigger_type' => 'manual_entry',
                'entry_mode'  => 'user_launch',
                'is_primary'  => true,
            ]);

            // 4. Bootstrap a blank Page Definition
            $page = PageDefinition::create([
                'feature_version_id' => $version->id,
                'key'         => $this->newFeatureKey . '-form',
                'name'        => $this->newFeatureName . ' Form',
                'page_type'   => 'workflow_form',
                'is_entry_page' => true,
            ]);

            $blueprint = BlueprintRegistry::getBlueprint($this->selectedBlueprint);

            if ($blueprint) {
                // Generate nodes and edges from blueprint
                $nodeIdMap = []; // map node_key to DB id
                foreach ($blueprint['flow_definition']['nodes'] as $n) {
                    $node = $flow->nodes()->create($n);
                    $nodeIdMap[$n['node_key']] = $node->id;
                }
                foreach ($blueprint['flow_definition']['edges'] as $e) {
                    $flow->edges()->create([
                        'source_node_id' => $nodeIdMap[$e['source_node_key']],
                        'target_node_id' => $nodeIdMap[$e['target_node_key']],
                        'condition_type' => $e['condition_type']
                    ]);
                }

                // Generate steps and fields from blueprint
                foreach ($blueprint['page_definition']['steps'] as $s) {
                    $step = $page->steps()->create([
                        'step_key' => $s['step_key'],
                        'title' => $s['title'],
                        'description' => $s['description'] ?? '',
                        'entity_binding' => $s['entity_binding'] ?? '',
                        'sort_order' => $s['sort_order'],
                    ]);
                    
                    if (!empty($s['fields'])) {
                        foreach ($s['fields'] as $fi => $f) {
                            $bindingData = null;
                            if (isset($f['binding'])) {
                                $bindingData = $f['binding'];
                                unset($f['binding']);
                            }

                            $field = $step->fields()->create(array_merge($f, ['sort_order' => $fi]));

                            if ($bindingData) {
                                $field->binding()->create([
                                    'binding_type' => $bindingData['binding_type'] ?? 'entity',
                                    'target_entity' => $bindingData['target_entity'] ?? null,
                                    'target_path' => $bindingData['target_path'] ?? null,
                                ]);
                            }
                        }
                    }
                }
            } else {
                // Blank canvas fallback
                $trigger = $flow->nodes()->create([
                    'node_key'   => 'start',
                    'node_type'  => 'trigger',
                    'label'      => 'Form Submitted',
                    'position_x' => 100,
                    'position_y' => 200,
                    'config'     => [],
                ]);
                $end = $flow->nodes()->create([
                    'node_key'   => 'end',
                    'node_type'  => 'end',
                    'label'      => 'Finish',
                    'position_x' => 400,
                    'position_y' => 200,
                    'config'     => [],
                ]);
                $flow->edges()->create([
                    'source_node_id' => $trigger->id,
                    'target_node_id' => $end->id,
                    'condition_type' => 'always',
                ]);

                $page->steps()->create([
                    'step_key'   => 'step_1',
                    'title'      => 'Step 1',
                    'sort_order' => 1,
                ]);
            }
        });

        $this->showCreateModal = false;
        $this->dispatch('feature-created');
        session()->flash('success', "Feature '{$this->newFeatureName}' created successfully!");
    }

    public function render()
    {
        $mtdCost = DB::table('ai_usage_logs')
            ->whereMonth('used_at', now()->month)
            ->whereYear('used_at', now()->year)
            ->sum('cost_usd');

        $budget = config('ai.monthly_budget_usd', 50.0);
        $budgetUsedPercent = ($mtdCost / $budget) * 100;

        return view('livewire.studio.dashboard', [
            'features' => Feature::with(['versions' => function($q) {
                $q->latest('version_no')->with(['flows', 'pages']);
            }])->withCount('versions')->latest()->get(),
            'mtd_ai_cost' => $mtdCost,
            'budget_used_percent' => $budgetUsedPercent,
        ])->layout('layouts.studio');
    }
}

