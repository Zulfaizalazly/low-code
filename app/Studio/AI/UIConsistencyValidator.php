<?php

namespace App\Studio\AI;

use Illuminate\Support\Facades\File;

class UIConsistencyValidator
{
    protected array $library;

    public function __construct()
    {
        $path = resource_path('data/component-library.json');
        $this->library = File::exists($path) ? json_decode(File::get($path), true) : [];
    }

    /**
     * Validate and score a PageDefinition.
     */
    public function validate(array $definition): array
    {
        $results = [
            'score' => 100,
            'violations' => [],
            'is_valid' => true,
        ];

        $violations = [];
        $approvedKeys = collect($this->library['components'] ?? [])->pluck('key')->toArray();
        
        foreach ($definition['steps'] ?? [] as $stepIdx => $step) {
            // Step validation
            if (empty($step['title'])) {
                $violations[] = [
                    'type' => 'structure',
                    'target' => "step_{$stepIdx}",
                    'message' => "Step #".($stepIdx + 1)." is missing a title",
                    'severity' => 'critical'
                ];
            }

            foreach ($step['fields'] ?? [] as $fieldIdx => $field) {
                $fieldLabel = $field['label'] ?? "Field #".($fieldIdx + 1);
                
                // 1. Component Compliance
                if (!in_array($field['component_type'], $approvedKeys)) {
                    $violations[] = [
                        'type' => 'compliance',
                        'target' => $field['field_key'],
                        'message' => "Unapproved component type: {$field['component_type']}",
                        'severity' => 'critical'
                    ];
                }

                // 2. Data Binding Check for Inputs
                if ($this->isInputComponent($field['component_type'])) {
                    if (empty($field['binding']['target_entity']) || empty($field['binding']['target_path'])) {
                        $violations[] = [
                            'type' => 'binding',
                            'target' => $field['field_key'],
                            'message' => "Input field '{$fieldLabel}' is missing a data binding",
                            'severity' => 'error'
                        ];
                    }
                }

                // 3. Configuration Check
                if ($field['component_type'] === 'select' && empty($field['config']['options'])) {
                    $violations[] = [
                        'type' => 'config',
                        'target' => $field['field_key'],
                        'message' => "Dropdown '{$fieldLabel}' has no options defined",
                        'severity' => 'warning'
                    ];
                }
            }
        }

        // Calculate score
        $criticalCount = collect($violations)->where('severity', 'critical')->count();
        $errorCount = collect($violations)->where('severity', 'error')->count();
        
        $results['score'] = 100 - ($criticalCount * 20) - ($errorCount * 10);
        $results['score'] = max(0, $results['score']);
        $results['violations'] = $violations;
        $results['is_valid'] = $criticalCount === 0;

        return $results;
    }

    protected function isInputComponent(string $type): bool
    {
        return in_array($type, [
            'text_input', 'ic_input', 'phone_input', 'amount_input', 
            'date_picker', 'checkbox', 'radio', 'textarea', 'select', 'file_upload',
            'signature_pad', 'camera_capture', 'scanner_input'
        ]);
    }

    protected function isSemantic(array $style): bool
    {
        // Check if colors use 'system' prefix
        return true; // Simplified for now
    }
}
