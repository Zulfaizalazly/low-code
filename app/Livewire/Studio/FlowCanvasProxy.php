<?php

namespace App\Livewire\Studio;

use App\Studio\Discovery\CommandDiscoverer;
use App\Studio\Registry\FlowDefinition;
use App\Studio\AI\AIUIGenerator;
use App\Studio\AI\AspectDetector;
use App\Studio\AI\OptionGenerator;
use App\Studio\Registry\PageDefinition;
use App\Studio\Registry\FormStep;
use App\Studio\Registry\FormField;
use App\Studio\Registry\FieldBinding;
use Livewire\Component;

class FlowCanvasProxy extends Component
{
    public FlowDefinition $flow;

    public array $commands = [];

    public function mount(int $flowId)
    {
        $this->flow = FlowDefinition::with(['nodes', 'edges'])->findOrFail($flowId);

        // Discover available commands for the node inspector
        $discoverer = new CommandDiscoverer();
        $this->commands = $discoverer->discover();
    }

    /**
     * Handle state update from the Vue Flow island.
     */
    public function saveFlowState(array $nodes, array $edges)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($nodes, $edges) {
            // 1. Clean old edges and nodes
            $this->flow->edges()->delete();
            $this->flow->nodes()->delete();

            // 2. Re-create Nodes
            $nodeMap = [];
            foreach ($nodes as $node) {
                $newNode = $this->flow->nodes()->create([
                    'node_key' => $node['node_key'] ?? $node['id'] ?? 'unknown',
                    'node_type' => $node['node_type'] ?? $node['type'] ?? 'command',
                    'label' => $node['label'] ?? $node['data']['label'] ?? 'Untitled',
                    'config' => $node['config'] ?? $node['data']['config'] ?? [],
                    'position_x' => (int) ($node['position_x'] ?? $node['position']['x'] ?? 0),
                    'position_y' => (int) ($node['position_y'] ?? $node['position']['y'] ?? 0),
                ]);
                $nodeMap[$node['node_key'] ?? $node['id']] = $newNode->id;
            }

            // 3. Re-create Edges
            foreach ($edges as $edge) {
                $sourceKey = $edge['source_node_key'] ?? $edge['source'] ?? null;
                $targetKey = $edge['target_node_key'] ?? $edge['target'] ?? null;

                $this->flow->edges()->create([
                    'source_node_id' => $nodeMap[$sourceKey] ?? null,
                    'target_node_id' => $nodeMap[$targetKey] ?? null,
                    'condition_type' => $edge['condition_type'] ?? $edge['data']['condition_type'] ?? 'always',
                    'condition_config' => $edge['condition_config'] ?? $edge['data']['condition_config'] ?? [],
                ]);
            }
        });

        $this->dispatch('flow-saved');
    }

    /**
     * Generate UI using AI from current flow.
     */
    public function generateUI(AIUIGenerator $generator)
    {
        try {
            $definition = $generator->generateFromFlow($this->flow);
            
            // Detect aspects for the visual refinement engine
            $detector = new AspectDetector();
            $aspects = $detector->detect($definition);
            
            $optionGenerator = new OptionGenerator();
            $options = $optionGenerator->generate($aspects);

            $this->dispatch('ui-generated', [
                'definition' => $definition,
                'aspects' => $aspects,
                'options' => $options
            ]);
        } catch (\Exception $e) {
            $this->dispatch('ui-generation-failed', [
                'message' => $e->getMessage(),
                'error_context' => [
                    'flow_id' => $this->flow->id,
                    'user_id' => auth()->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);
        }
    }

    /**
     * Refine UI using AI instruction.
     */
    public function refineUI(AIUIGenerator $generator, array $currentDefinition, string $instruction)
    {
        try {
            $definition = $generator->refineDefinition($currentDefinition, $instruction, $this->flow->feature_version_id);
            
            $detector = new AspectDetector();
            $aspects = $detector->detect($definition);
            
            $optionGenerator = new OptionGenerator();
            $options = $optionGenerator->generate($aspects);

            $this->dispatch('ui-refined', [
                'definition' => $definition,
                'aspects' => $aspects,
                'options' => $options
            ]);
        } catch (\Exception $e) {
            $this->dispatch('ui-refinement-failed', [
                'message' => $e->getMessage(),
                'error_context' => [
                    'flow_id' => $this->flow->id,
                    'user_id' => auth()->id(),
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);
        }
    }

    /**
     * Persist AI-generated UI to the Registry.
     */
    public function publishAIUI(array $definition)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($definition) {
            // 1. Create/Update PageDefinition
            $page = PageDefinition::updateOrCreate(
                [
                    'feature_version_id' => $this->flow->feature_version_id,
                    'page_key' => $definition['page_key'] ?? 'ai_gen_' . time()
                ],
                [
                    'name' => $definition['name'] ?? 'AI Generated Page',
                    'config' => $definition, // Store full JSON in config for safety
                    'status' => 'draft'
                ]
            );

            // 2. Clear old children if updating
            $page->steps()->delete();

            // 3. Create Steps and Fields
            foreach ($definition['steps'] ?? [] as $stepIndex => $stepData) {
                $step = $page->steps()->create([
                    'step_key' => $stepData['step_key'] ?? "step_{$stepIndex}",
                    'title' => $stepData['title'] ?? "Step {$stepIndex}",
                    'sort_order' => $stepIndex,
                    'config' => $stepData
                ]);

                foreach ($stepData['fields'] ?? [] as $fieldIndex => $fieldData) {
                    $field = $step->fields()->create([
                        'field_key' => $fieldData['field_key'] ?? "field_{$fieldIndex}",
                        'component' => $fieldData['component'] ?? 'text_input',
                        'label' => $fieldData['label'] ?? 'Untitled Field',
                        'is_required' => $fieldData['required'] ?? false,
                        'sort_order' => $fieldIndex,
                        'config' => $fieldData
                    ]);

                    // 4. Create Binding
                    if (isset($fieldData['binding'])) {
                        $field->binding()->create([
                            'target_entity' => $fieldData['binding']['target_entity'] ?? null,
                            'target_path' => $fieldData['binding']['target_path'] ?? null,
                            'config' => $fieldData['binding']
                        ]);
                    }
                }
            }
        });

        $this->dispatch('ui-published');
    }

    /**
     * Create a draft from AI generation for Manual Override.
     */
    public function createAIDraft(array $definition): int
    {
        return DB::transaction(function () use ($definition) {
            // 1. Create PageDefinition as Draft
            $page = PageDefinition::create([
                'feature_version_id' => $this->flow->feature_version_id,
                'page_key' => 'ai_manual_' . time(),
                'name' => ($definition['name'] ?? 'AI Draft') . ' (Manual Override)',
                'config' => $definition,
                'status' => 'draft'
            ]);

            // 2. Create Steps and Fields (similar to publish AI UI)
            foreach ($definition['steps'] ?? [] as $stepIndex => $stepData) {
                $step = $page->steps()->create([
                    'step_key' => $stepData['step_key'] ?? "step_{$stepIndex}",
                    'title' => $stepData['title'] ?? "Step {$stepIndex}",
                    'sort_order' => $stepIndex,
                    'config' => $stepData
                ]);

                foreach ($stepData['fields'] ?? [] as $fieldIndex => $fieldData) {
                    $field = $step->fields()->create([
                        'field_key' => $fieldData['field_key'] ?? "field_{$fieldIndex}",
                        'component_type' => $fieldData['component'] ?? 'text', // Map to builder type
                        'label' => $fieldData['label'] ?? 'Untitled Field',
                        'is_required' => $fieldData['required'] ?? false,
                        'sort_order' => $fieldIndex,
                        'config' => $fieldData
                    ]);

                    if (isset($fieldData['binding'])) {
                        $field->binding()->create([
                            'target_entity' => $fieldData['binding']['target_entity'] ?? null,
                            'target_path' => $fieldData['binding']['target_path'] ?? null,
                        ]);
                    }
                }
            }

            return $page->id;
        });
    }

    public function render()
    {
        return view('livewire.studio.flow-canvas-proxy')
            ->layout('layouts.studio');
    }
}
