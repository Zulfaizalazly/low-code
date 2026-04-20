<?php

namespace App\Studio\Validation;

use App\Studio\Registry\PageDefinition;
use App\Studio\Registry\FormStep;
use App\Studio\Registry\FormField;

class PageValidator
{
    public function validate(PageDefinition $page): array
    {
        $errors = [];
        $warnings = [];

        $steps = $page->steps;
        
        // VR-2.1: Has steps
        if ($steps->isEmpty()) {
            $errors[] = "Page definition has no steps.";
        }

        $keys = [];

        foreach ($steps as $stepIdx => $step) {
            // VR-2.7: Step completeness
            if (empty($step->title)) {
                $errors[] = "Step #".($stepIdx + 1)." is missing a title.";
            }

            $fields = $step->fields;
            if ($fields->isEmpty()) {
                $warnings[] = "Step '{$step->title}' has no fields.";
            }

            foreach ($fields as $field) {
                // VR-2.3: Unique keys
                if (in_array($field->field_key, $keys)) {
                    $errors[] = "Duplicate field key detected: '{$field->field_key}'. Keys must be unique across the entire page.";
                }
                $keys[] = $field->field_key;

                // VR-2.4 & 2.5: Binding validation
                if ($this->requiresBinding($field->component_type)) {
                    $binding = $field->binding;
                    if (!$binding || empty($binding->target_entity) || empty($binding->target_path)) {
                        $errors[] = "Field '{$field->label}' (key: {$field->field_key}) is missing a data binding.";
                    }
                }

                // VR-2.6: Required marked (UI hint check)
                if ($field->is_required && empty($field->label)) {
                    $warnings[] = "Required field '{$field->field_key}' is missing a label for user guidance.";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    private function requiresBinding(string $type): bool
    {
        $displayOnly = ['alert', 'badge', 'summary_panel', 'timeline'];
        return !in_array($type, $displayOnly);
    }
}
