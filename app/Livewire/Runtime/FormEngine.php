<?php

namespace App\Livewire\Runtime;

use App\Runtime\UI\PageLoader;
use App\Runtime\UI\BindingResolver;
use App\Studio\Registry\PageDefinition;
use Livewire\Component;
use Exception;

class FormEngine extends Component
{
    public PageDefinition $page;
    public array $formData = [];
    public int $currentStepIndex = 0;

    public function mount(string $featureKey, string $pageKey = null)
    {
        $loader = new PageLoader();
        $this->page = $loader->load($featureKey, $pageKey);

        if (!$this->page) {
            throw new Exception("Page not found for feature: {$featureKey}");
        }

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
        // Validation would happen here based on field rules
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

        // Bridging Phase 3 (UI) to Phase 2 (Automation)
        $featureVersion = $this->page->featureVersion;
        $primaryFlow = $featureVersion->flows()->where('is_primary', true)->first();

        if ($primaryFlow) {
            $orchestrator = app(\App\Runtime\Automation\FlowOrchestrator::class);
            $orchestrator->execute($primaryFlow, ['form' => $payload]);
        }

        $this->dispatch('form-submitted', payload: $payload);
    }

    public function render()
    {
        return view('livewire.runtime.form-engine', [
            'currentStep' => $this->page->steps[$this->currentStepIndex] ?? null,
        ])->layout('layouts.app');
    }
}
