<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\BlueprintRegistry;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FeatureVersion;
use App\Studio\Registry\FlowDefinition;
use App\Studio\Registry\PageDefinition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferenceFeatureSeeder extends Seeder
{
    /**
     * Seed 4 published features using BlueprintRegistry — same code path as Studio UI.
     *
     * Features:
     * 1. New Pledge (pledge_intake) — Facility domain, full intake flow
     * 2. Pledge Redemption (pledge_redemption) — Facility domain, tebus barang
     * 3. Pledge Renewal (pledge_renewal) — Facility domain, sambung pajak
     * 4. Payment Collection (payment_collection) — Finance domain, kutipan bayaran
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@arrahnu.com')->first();

        if (!$admin) {
            $this->command->error('Admin user not found. Run UserSeeder first.');
            return;
        }

        $features = [
            [
                'key' => 'new-pledge',
                'name' => 'New Pledge Intake',
                'blueprint' => 'pledge_intake',
                'icon' => 'plus-circle',
            ],
            [
                'key' => 'pledge-redemption',
                'name' => 'Pledge Redemption',
                'blueprint' => 'pledge_redemption',
                'icon' => 'arrow-uturn-left',
            ],
            [
                'key' => 'pledge-renewal',
                'name' => 'Pledge Renewal',
                'blueprint' => 'pledge_renewal',
                'icon' => 'arrow-path',
            ],
            [
                'key' => 'payment-collection',
                'name' => 'Payment Collection',
                'blueprint' => 'payment_collection',
                'icon' => 'banknotes',
            ],
        ];

        foreach ($features as $idx => $featureData) {
            $blueprint = BlueprintRegistry::getBlueprint($featureData['blueprint']);

            if (!$blueprint) {
                $this->command->warn("Blueprint '{$featureData['blueprint']}' not found. Skipping.");
                continue;
            }

            DB::transaction(function () use ($featureData, $blueprint, $admin, $idx) {
                // 1. Create Feature
                $feature = Feature::create([
                    'key' => $featureData['key'],
                    'name' => $featureData['name'],
                    'domain' => $blueprint['domain'],
                    'status' => 'published',
                ]);

                // 2. Create Version 1 (published)
                $version = $feature->versions()->create([
                    'version_no' => 1,
                    'status' => 'published',
                    'published_at' => now(),
                    'published_by' => $admin->id,
                ]);

                // 3. Create Flow Definition
                $flow = FlowDefinition::create([
                    'feature_version_id' => $version->id,
                    'key' => $featureData['key'] . '-flow',
                    'name' => $featureData['name'] . ' Flow',
                    'trigger_type' => 'manual_entry',
                    'entry_mode' => 'user_launch',
                    'is_primary' => true,
                ]);

                // 4. Create Flow Nodes & Edges (same as Studio UI createFeature)
                $nodeIdMap = [];
                foreach ($blueprint['flow_definition']['nodes'] as $n) {
                    $node = $flow->nodes()->create($n);
                    $nodeIdMap[$n['node_key']] = $node->id;
                }
                foreach ($blueprint['flow_definition']['edges'] as $e) {
                    $flow->edges()->create([
                        'source_node_id' => $nodeIdMap[$e['source_node_key']],
                        'target_node_id' => $nodeIdMap[$e['target_node_key']],
                        'condition_type' => $e['condition_type'],
                    ]);
                }

                // 5. Create Page Definition
                $page = PageDefinition::create([
                    'feature_version_id' => $version->id,
                    'key' => $featureData['key'] . '-form',
                    'name' => $featureData['name'] . ' Form',
                    'page_type' => 'workflow_form',
                    'is_entry_page' => true,
                ]);

                // 6. Create Steps, Fields & Bindings (same as Studio UI createFeature)
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

                // 7. Create Menu Item (for teller sidebar)
                $version->menuItems()->create([
                    'menu_key' => Str::snake($featureData['key']),
                    'label' => $featureData['name'],
                    'icon' => $featureData['icon'],
                    'parent_menu_key' => $blueprint['domain'],
                    'route_key' => '/portal/operations/' . $featureData['key'],
                    'sort_order' => $idx + 1,
                    'is_enabled' => true,
                ]);

                // 8. Auto-create bindings for required fields without one
                $page->load('steps.fields.binding');
                foreach ($page->steps as $step) {
                    foreach ($step->fields as $field) {
                        if ($field->is_required && !$field->binding) {
                            $field->binding()->create([
                                'binding_type' => 'command_argument',
                                'target_path' => $field->field_key,
                            ]);
                        }
                    }
                }

                $this->command->info("  ✓ {$featureData['name']} ({$blueprint['domain']})");
            });
        }

        $this->command->info('Reference features seeded successfully!');
    }
}
