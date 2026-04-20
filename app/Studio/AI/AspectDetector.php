<?php

namespace App\Studio\AI;

class AspectDetector
{
    /**
     * Detect all editable aspects of a PageDefinition.
     */
    public function detect(array $definition): array
    {
        $aspects = [];

        // 1. Detect Step Aspects
        foreach ($definition['steps'] ?? [] as $index => $step) {
            $stepKey = $step['step_key'] ?? "step_{$index}";
            
            $aspects[] = [
                'type' => 'step_property',
                'target' => $stepKey,
                'label' => "Step: {$step['title']}",
                'properties' => ['title', 'description', 'order', 'visibility'],
                'current' => [
                    'title' => $step['title'] ?? '',
                    'description' => $step['description'] ?? '',
                    'order' => $index,
                ]
            ];

            // 2. Detect Field Aspects
            foreach ($step['fields'] ?? [] as $fieldIndex => $field) {
                $fieldKey = $field['field_key'] ?? "field_{$fieldIndex}";
                
                $aspects[] = [
                    'type' => 'field_property',
                    'target' => $fieldKey,
                    'step' => $stepKey,
                    'label' => "Field: {$field['label']}",
                    'properties' => ['label', 'component', 'placeholder', 'required', 'validation', 'binding'],
                    'current' => [
                        'label' => $field['label'] ?? '',
                        'component' => $field['component'] ?? '',
                        'placeholder' => $field['placeholder'] ?? '',
                        'required' => $field['required'] ?? false,
                        'validation' => $field['validation'] ?? null,
                        'binding' => $field['binding'] ?? null,
                    ]
                ];
            }
        }

        return $aspects;
    }
}
