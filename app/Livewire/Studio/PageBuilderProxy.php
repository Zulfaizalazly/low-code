<?php

namespace App\Livewire\Studio;

use App\Studio\Registry\FeatureVersion;
use App\Studio\Registry\FieldBinding;
use App\Studio\Registry\FormField;
use App\Studio\Registry\FormStep;
use App\Studio\Registry\PageDefinition;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.studio-fullscreen')]
class PageBuilderProxy extends Component
{
    public int $featureVersionId;
    public int $pageId;
    public string $pageName = '';
    public array $steps = [];
    public array $entities = [];
    public string $saveStatus = '';
    public string $featureName = '';
    public string $flowName = '';

    public function mount(int $featureVersionId, int $pageId)
    {
        $this->featureVersionId = $featureVersionId;
        $this->pageId = $pageId;

        $this->entities = [
            'customers' => ['id', 'name', 'ic_number', 'phone', 'email', 'address'],
            'facilities' => ['id', 'facility_no', 'principal_amount', 'tenure', 'margin', 'status'],
            'facility_items' => ['id', 'facility_id', 'gold_type', 'weight_gross', 'weight_net', 'value_market'],
            'facility_nominees' => ['id', 'facility_id', 'name', 'ic_number', 'relationship'],
            'valuations' => ['id', 'valuation_no', 'total_gold_value', 'total_loan_eligible'],
            'approval_tasks' => ['id', 'task_type', 'status', 'assigned_role'],
            'payment_transactions' => ['id', 'transaction_no', 'amount', 'payment_method'],
        ];

        $this->loadPageState();
    }

    /**
     * Load the current page definition with steps, fields, and bindings.
     */
    public function loadPageState(): void
    {
        $page = PageDefinition::with(['featureVersion.feature', 'featureVersion.flows', 'steps.fields.binding'])
            ->findOrFail($this->pageId);

        $this->pageName = $page->name;
        $this->featureName = $page->featureVersion->feature->name ?? 'Unknown Feature';
        $flow = $page->featureVersion->flows->first();
        $this->flowName = $flow ? $flow->name : '';

        $this->steps = $page->steps->map(function ($step) {
            return [
                'id' => $step->id,
                'step_key' => $step->step_key,
                'title' => $step->title,
                'description' => $step->description ?? '',
                'entity_binding' => $step->entity_binding ?? '',
                'sort_order' => $step->sort_order,
                'fields' => $step->fields->map(function ($field) {
                    return [
                        'id' => $field->id,
                        'field_key' => $field->field_key,
                        'label' => $field->label,
                        'component_type' => $field->component_type,
                        'data_type' => $field->data_type,
                        'is_required' => (bool) $field->is_required,
                        'default_value' => $field->default_value,
                        'placeholder' => $field->placeholder ?? '',
                        'help_text' => $field->help_text ?? '',
                        'sort_order' => $field->sort_order,
                        'config' => $field->config ?? [],
                        'binding' => $field->binding ? [
                            'binding_type' => $field->binding->binding_type,
                            'target_entity' => $field->binding->target_entity ?? '',
                            'target_path' => $field->binding->target_path ?? '',
                        ] : [
                            'binding_type' => 'direct',
                            'target_entity' => '',
                            'target_path' => '',
                        ],
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    /**
     * Save the page state from the Vue Page Builder.
     * Called via Livewire bridge from the Vue component.
     */
    public function savePageState(array $stepsData): void
    {
        DB::transaction(function () use ($stepsData) {
            $page = PageDefinition::findOrFail($this->pageId);

            // Delete existing steps, fields, and bindings (cascade)
            $page->steps()->delete();

            foreach ($stepsData as $stepData) {
                $step = FormStep::create([
                    'page_definition_id' => $page->id,
                    'step_key' => $stepData['step_key'],
                    'title' => $stepData['title'],
                    'description' => $stepData['description'] ?? null,
                    'entity_binding' => $stepData['entity_binding'] ?? null,
                    'sort_order' => $stepData['sort_order'] ?? 0,
                ]);

                foreach ($stepData['fields'] ?? [] as $fieldData) {
                    $field = FormField::create([
                        'form_step_id' => $step->id,
                        'field_key' => $fieldData['field_key'],
                        'label' => $fieldData['label'],
                        'component_type' => $fieldData['component_type'],
                        'data_type' => $fieldData['data_type'] ?? 'string',
                        'is_required' => $fieldData['is_required'] ?? false,
                        'default_value' => $fieldData['default_value'] ?? null,
                        'placeholder' => $fieldData['placeholder'] ?? null,
                        'help_text' => $fieldData['help_text'] ?? null,
                        'sort_order' => $fieldData['sort_order'] ?? 0,
                        'config' => $fieldData['config'] ?? null,
                    ]);

                    // Create binding if target is specified
                    $binding = $fieldData['binding'] ?? null;
                    if ($binding && !empty($binding['target_entity'])) {
                        FieldBinding::create([
                            'form_field_id' => $field->id,
                            'binding_type' => $binding['binding_type'] ?? 'direct',
                            'target_entity' => $binding['target_entity'],
                            'target_path' => $binding['target_path'] ?? null,
                        ]);
                    }
                }
            }
        });

        $this->saveStatus = 'saved';
        $this->loadPageState(); // Reload to get fresh IDs
    }

    public function generateUI(\App\Studio\AI\AIUIGenerator $generator): void
    {
        try {
            $page = PageDefinition::with('featureVersion.flows')->findOrFail($this->pageId);
            $flow = $page->featureVersion->flows->first();
            
            if (!$flow) {
                $this->dispatch('ui-generation-failed', message: 'No flow found for this feature to infer UI from.');
                return;
            }

            $definition = $generator->generateFromFlow($flow);
            
            $this->dispatch('ui-generated', [
                'definition' => $definition
            ]);
        } catch (\Exception $e) {
            $this->dispatch('ui-generation-failed', message: $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.studio.page-builder-proxy');
    }
}
