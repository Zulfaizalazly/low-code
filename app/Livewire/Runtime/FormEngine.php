<?php

namespace App\Livewire\Runtime;

use App\Runtime\UI\PageLoader;
use App\Runtime\UI\BindingResolver;
use App\Studio\Registry\PageDefinition;
use Livewire\Component;
use Exception;

class FormEngine extends Component
{
    public ?PageDefinition $page = null;
    public array $formData = [];
    public int $currentStepIndex = 0;
    public bool $isSubmitted = false;

    public function mount(string $featureKey, string $pageKey = null)
    {
        $loader = app(PageLoader::class);
        $page = $loader->load($featureKey, $pageKey);

        if (!$page) {
            session()->flash('error', 'Feature not available for your branch.');
            $this->redirect(route('runtime.portal'));
            return;
        }

        $this->page = $page;

        $this->initializeFormData();
    }

    private function initializeFormData()
    {
        foreach ($this->page->steps as $step) {
            foreach ($step->fields as $field) {
                $this->formData[$field->field_key] = $field->default_value;
            }
        }
    }

    public function next()
    {
        // Validate current step fields
        $currentStep = $this->page->steps[$this->currentStepIndex] ?? null;
        if ($currentStep) {
            $rules = [];
            $messages = [];
            foreach ($currentStep->fields as $field) {
                $fieldRules = [];
                if ($field->is_required) {
                    $fieldRules[] = 'required';
                    $messages["formData.{$field->field_key}.required"] = "{$field->label} is required.";
                }
                match ($field->data_type ?? 'string') {
                    'integer' => $fieldRules[] = 'integer',
                    'decimal' => $fieldRules[] = 'numeric',
                    'date' => $fieldRules[] = 'date',
                    'boolean' => $fieldRules[] = 'boolean',
                    default => null,
                };
                if (!empty($fieldRules)) {
                    $rules["formData.{$field->field_key}"] = $fieldRules;
                }
            }
            if (!empty($rules)) {
                $this->validate($rules, $messages);
            }
        }

        if ($this->currentStepIndex < count($this->page->steps) - 1) {
            $this->currentStepIndex++;
        } else {
            $this->submit();
        }
    }

    public function back()
    {
        if ($this->currentStepIndex > 0) {
            $this->currentStepIndex--;
        }
    }

    public function submit()
    {
        $resolver = new BindingResolver();
        $payload = $resolver->resolve($this->page, $this->formData);

        // Log submission to ui_submission_logs
        $featureVersion = $this->page->featureVersion;
        try {
            \DB::table('ui_submission_logs')->insert([
                'page_definition_id' => $this->page->id,
                'page_version' => $featureVersion?->version_no ?? 1,
                'form_data' => json_encode($payload),
                'submitted_by' => auth()->id(),
                'submitted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to log form submission', ['error' => $e->getMessage()]);
        }

        // Bridging Phase 3 (UI) to Phase 2 (Automation)
        $primaryFlow = $featureVersion?->flows()->where('is_primary', true)->first();

        if ($primaryFlow) {
            $orchestrator = app(\App\Runtime\Automation\FlowOrchestrator::class);
            $orchestrator->execute($primaryFlow, ['form' => $payload]);
        }

        $this->isSubmitted = true;
        $this->dispatch('form-submitted', payload: $payload);
    }

    public function render()
    {
        if (!$this->page) {
            return view('livewire.runtime.form-engine', [
                'currentStep' => null,
            ])->layout('layouts.app');
        }

        return view('livewire.runtime.form-engine', [
            'currentStep' => $this->page->steps[$this->currentStepIndex] ?? null,
        ])->layout('layouts.app');
    }
}
