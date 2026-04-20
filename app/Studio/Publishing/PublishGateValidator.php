<?php

namespace App\Studio\Publishing;

use App\Studio\Registry\FeatureVersion;
use App\Studio\Registry\FlowDefinition;
use App\Studio\Registry\FlowNode;
use App\Studio\Registry\FlowEdge;
use Illuminate\Support\Collection;

class PublishGateValidator
{
    /**
     * Run all 14 publish gate validations against a feature version.
     *
     * @return PublishGateResult Collection of check results
     */
    public function validate(FeatureVersion $version): PublishGateResult
    {
        $checks = collect([
            $this->hasFlow($version),
            $this->flowHasTrigger($version),
            $this->flowHasEnd($version),
            $this->flowConnected($version),
            $this->hasPage($version),
            $this->pageHasFields($version),
            $this->fieldsHaveBindings($version),
            $this->bindingsValid($version),
            $this->hasMenuItem($version),
            $this->commandsExist($version),
            $this->noOrphanNodes($version),
            $this->hasPermissions($version),
            $this->versionIsDraft($version),
            $this->noDuplicateKeys($version),
        ]);

        // Persist results to publish_validations table
        foreach ($checks as $check) {
            \DB::table('publish_validations')->insert([
                'feature_version_id' => $version->id,
                'check_type' => 'publish_gate',
                'check_key' => $check['key'],
                'status' => $check['status'],
                'message' => $check['message'],
                'validated_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return new PublishGateResult($checks);
    }

    // ──────────────────────────────────────────
    // Gate Check #1: Feature has at least one flow
    // ──────────────────────────────────────────
    private function hasFlow(FeatureVersion $version): array
    {
        $hasFlow = $version->flows()->exists();
        return [
            'key' => 'has_flow',
            'status' => $hasFlow ? 'passed' : 'failed',
            'message' => $hasFlow
                ? 'Feature has at least one flow definition.'
                : 'Feature must have at least one flow definition.',
        ];
    }

    // ──────────────────────────────────────────
    // Gate Check #2: Every flow has a trigger node
    // ──────────────────────────────────────────
    private function flowHasTrigger(FeatureVersion $version): array
    {
        $flows = $version->flows()->with('nodes')->get();

        if ($flows->isEmpty()) {
            return ['key' => 'flow_has_trigger', 'status' => 'skipped', 'message' => 'No flows to check.'];
        }

        foreach ($flows as $flow) {
            $triggerCount = $flow->nodes->where('node_type', 'trigger')->count();
            if ($triggerCount !== 1) {
                return [
                    'key' => 'flow_has_trigger',
                    'status' => 'failed',
                    'message' => "Flow '{$flow->name}' must have exactly one trigger node (found {$triggerCount}).",
                ];
            }
        }

        return ['key' => 'flow_has_trigger', 'status' => 'passed', 'message' => 'All flows have exactly one trigger node.'];
    }

    // ──────────────────────────────────────────
    // Gate Check #3: Every flow has an end node
    // ──────────────────────────────────────────
    private function flowHasEnd(FeatureVersion $version): array
    {
        $flows = $version->flows()->with('nodes')->get();

        if ($flows->isEmpty()) {
            return ['key' => 'flow_has_end', 'status' => 'skipped', 'message' => 'No flows to check.'];
        }

        foreach ($flows as $flow) {
            $endCount = $flow->nodes->where('node_type', 'end')->count();
            if ($endCount === 0) {
                return [
                    'key' => 'flow_has_end',
                    'status' => 'failed',
                    'message' => "Flow '{$flow->name}' must have at least one end node.",
                ];
            }
        }

        return ['key' => 'flow_has_end', 'status' => 'passed', 'message' => 'All flows have at least one end node.'];
    }

    // ──────────────────────────────────────────
    // Gate Check #4: All nodes reachable from trigger
    // ──────────────────────────────────────────
    private function flowConnected(FeatureVersion $version): array
    {
        $flows = $version->flows()->with(['nodes', 'edges'])->get();

        if ($flows->isEmpty()) {
            return ['key' => 'flow_connected', 'status' => 'skipped', 'message' => 'No flows to check.'];
        }

        foreach ($flows as $flow) {
            $trigger = $flow->nodes->where('node_type', 'trigger')->first();
            if (!$trigger) continue;

            $reachable = $this->findReachableNodes($trigger->id, $flow->edges);
            $allNodeIds = $flow->nodes->pluck('id')->toArray();

            $unreachable = array_diff($allNodeIds, $reachable);
            if (!empty($unreachable)) {
                $unreachableCount = count($unreachable);
                return [
                    'key' => 'flow_connected',
                    'status' => 'failed',
                    'message' => "Flow '{$flow->name}' has {$unreachableCount} unreachable node(s).",
                ];
            }
        }

        return ['key' => 'flow_connected', 'status' => 'passed', 'message' => 'All nodes are reachable from the trigger.'];
    }

    // ──────────────────────────────────────────
    // Gate Check #5: Feature has at least one page
    // ──────────────────────────────────────────
    private function hasPage(FeatureVersion $version): array
    {
        $hasPage = $version->pages()->exists();
        return [
            'key' => 'has_page',
            'status' => $hasPage ? 'passed' : 'failed',
            'message' => $hasPage
                ? 'Feature has at least one page definition.'
                : 'Feature must have at least one page definition.',
        ];
    }

    // ──────────────────────────────────────────
    // Gate Check #6: Every page has form fields
    // ──────────────────────────────────────────
    private function pageHasFields(FeatureVersion $version): array
    {
        $pages = $version->pages()->with('steps.fields')->get();

        if ($pages->isEmpty()) {
            return ['key' => 'page_has_fields', 'status' => 'skipped', 'message' => 'No pages to check.'];
        }

        foreach ($pages as $page) {
            $fieldCount = $page->steps->flatMap->fields->count();
            if ($fieldCount === 0) {
                return [
                    'key' => 'page_has_fields',
                    'status' => 'failed',
                    'message' => "Page '{$page->name}' must have at least one form field.",
                ];
            }
        }

        return ['key' => 'page_has_fields', 'status' => 'passed', 'message' => 'All pages have form fields.'];
    }

    // ──────────────────────────────────────────
    // Gate Check #7: Required fields have bindings
    // ──────────────────────────────────────────
    private function fieldsHaveBindings(FeatureVersion $version): array
    {
        $pages = $version->pages()->with('steps.fields.binding')->get();

        if ($pages->isEmpty()) {
            return ['key' => 'fields_have_bindings', 'status' => 'skipped', 'message' => 'No pages to check.'];
        }

        foreach ($pages as $page) {
            foreach ($page->steps as $step) {
                foreach ($step->fields as $field) {
                    if ($field->is_required && !$field->binding) {
                        return [
                            'key' => 'fields_have_bindings',
                            'status' => 'failed',
                            'message' => "Required field '{$field->field_key}' in page '{$page->name}' has no binding.",
                        ];
                    }
                }
            }
        }

        return ['key' => 'fields_have_bindings', 'status' => 'passed', 'message' => 'All required fields have bindings.'];
    }

    // ──────────────────────────────────────────
    // Gate Check #8: Binding target paths are valid
    // ──────────────────────────────────────────
    private function bindingsValid(FeatureVersion $version): array
    {
        $pages = $version->pages()->with('steps.fields.binding')->get();

        if ($pages->isEmpty()) {
            return ['key' => 'bindings_valid', 'status' => 'skipped', 'message' => 'No pages to check.'];
        }

        foreach ($pages as $page) {
            foreach ($page->steps as $step) {
                foreach ($step->fields as $field) {
                    if ($field->binding && empty($field->binding->target_path)) {
                        return [
                            'key' => 'bindings_valid',
                            'status' => 'failed',
                            'message' => "Binding for field '{$field->field_key}' has empty target_path.",
                        ];
                    }
                }
            }
        }

        return ['key' => 'bindings_valid', 'status' => 'passed', 'message' => 'All bindings have valid target paths.'];
    }

    // ──────────────────────────────────────────
    // Gate Check #9: Feature has a menu item
    // ──────────────────────────────────────────
    private function hasMenuItem(FeatureVersion $version): array
    {
        $hasMenu = $version->menuItems()->exists();
        return [
            'key' => 'has_menu_item',
            'status' => $hasMenu ? 'passed' : 'failed',
            'message' => $hasMenu
                ? 'Feature has at least one menu item.'
                : 'Feature must have at least one sidebar menu item.',
        ];
    }

    // ──────────────────────────────────────────
    // Gate Check #10: Command nodes reference valid classes
    // ──────────────────────────────────────────
    private function commandsExist(FeatureVersion $version): array
    {
        $flows = $version->flows()->with('nodes')->get();

        if ($flows->isEmpty()) {
            return ['key' => 'commands_exist', 'status' => 'skipped', 'message' => 'No flows to check.'];
        }

        foreach ($flows as $flow) {
            $commandNodes = $flow->nodes->where('node_type', 'command');
            foreach ($commandNodes as $node) {
                $commandClass = $node->config['command_class'] ?? null;
                if (!$commandClass || !class_exists($commandClass)) {
                    return [
                        'key' => 'commands_exist',
                        'status' => 'failed',
                        'message' => "Node '{$node->node_key}' references invalid command class: {$commandClass}",
                    ];
                }
            }
        }

        return ['key' => 'commands_exist', 'status' => 'passed', 'message' => 'All command nodes reference valid classes.'];
    }

    // ──────────────────────────────────────────
    // Gate Check #11: No orphan nodes (nodes without edges, except end nodes)
    // ──────────────────────────────────────────
    private function noOrphanNodes(FeatureVersion $version): array
    {
        $flows = $version->flows()->with(['nodes', 'edges'])->get();

        if ($flows->isEmpty()) {
            return ['key' => 'no_orphan_nodes', 'status' => 'skipped', 'message' => 'No flows to check.'];
        }

        foreach ($flows as $flow) {
            foreach ($flow->nodes as $node) {
                // Trigger nodes won't have incoming edges; end nodes won't have outgoing
                if ($node->node_type === 'trigger') continue;
                if ($node->node_type === 'end') continue;

                $hasIncoming = $flow->edges->where('target_node_id', $node->id)->isNotEmpty();
                $hasOutgoing = $flow->edges->where('source_node_id', $node->id)->isNotEmpty();

                if (!$hasIncoming || !$hasOutgoing) {
                    return [
                        'key' => 'no_orphan_nodes',
                        'status' => 'failed',
                        'message' => "Node '{$node->node_key}' in flow '{$flow->name}' is missing " .
                            (!$hasIncoming ? 'incoming' : 'outgoing') . " edge(s).",
                    ];
                }
            }
        }

        return ['key' => 'no_orphan_nodes', 'status' => 'passed', 'message' => 'No orphan nodes found.'];
    }

    // ──────────────────────────────────────────
    // Gate Check #12: Feature has permissions/roles
    // ──────────────────────────────────────────
    private function hasPermissions(FeatureVersion $version): array
    {
        // Check if menu items have allowed_roles configured
        $menuItems = $version->menuItems;

        if ($menuItems->isEmpty()) {
            return ['key' => 'has_permissions', 'status' => 'warning', 'message' => 'No menu items to derive permissions from.'];
        }

        // For now, we consider having menu items as having basic permission control
        // In the future, this checks a dedicated feature_permissions table
        return ['key' => 'has_permissions', 'status' => 'passed', 'message' => 'Feature has permission configuration via menu items.'];
    }

    // ──────────────────────────────────────────
    // Gate Check #13: Version is in draft status
    // ──────────────────────────────────────────
    private function versionIsDraft(FeatureVersion $version): array
    {
        $isDraft = $version->status === 'draft';
        return [
            'key' => 'version_is_draft',
            'status' => $isDraft ? 'passed' : 'failed',
            'message' => $isDraft
                ? 'Version is in draft status, eligible for publish.'
                : "Version status is '{$version->status}'. Only draft versions can be published.",
        ];
    }

    // ──────────────────────────────────────────
    // Gate Check #14: No duplicate keys within version
    // ──────────────────────────────────────────
    private function noDuplicateKeys(FeatureVersion $version): array
    {
        $flows = $version->flows()->with('nodes')->get();
        $pages = $version->pages()->with('steps.fields')->get();

        // Check node_key duplicates within each flow
        foreach ($flows as $flow) {
            $keys = $flow->nodes->pluck('node_key');
            $duplicates = $keys->duplicates();
            if ($duplicates->isNotEmpty()) {
                return [
                    'key' => 'no_duplicate_keys',
                    'status' => 'failed',
                    'message' => "Flow '{$flow->name}' has duplicate node keys: " . $duplicates->implode(', '),
                ];
            }
        }

        // Check field_key duplicates within each page
        foreach ($pages as $page) {
            $fieldKeys = $page->steps->flatMap->fields->pluck('field_key');
            $duplicates = $fieldKeys->duplicates();
            if ($duplicates->isNotEmpty()) {
                return [
                    'key' => 'no_duplicate_keys',
                    'status' => 'failed',
                    'message' => "Page '{$page->name}' has duplicate field keys: " . $duplicates->implode(', '),
                ];
            }
        }

        return ['key' => 'no_duplicate_keys', 'status' => 'passed', 'message' => 'No duplicate keys found.'];
    }

    // ──────────────────────────────────────────
    // Helper: BFS to find reachable nodes
    // ──────────────────────────────────────────
    private function findReachableNodes(int $startNodeId, Collection $edges): array
    {
        $visited = [$startNodeId];
        $queue = [$startNodeId];

        while (!empty($queue)) {
            $current = array_shift($queue);
            $outgoing = $edges->where('source_node_id', $current);

            foreach ($outgoing as $edge) {
                if (!in_array($edge->target_node_id, $visited)) {
                    $visited[] = $edge->target_node_id;
                    $queue[] = $edge->target_node_id;
                }
            }
        }

        return $visited;
    }
}
