<?php

namespace App\Studio\AI;

class OptionGenerator
{
    /**
     * Generate valid options for each editable aspect.
     */
    public function generate(array $aspects): array
    {
        $options = [];

        foreach ($aspects as $aspect) {
            $aspectOptions = [];

            if ($aspect['type'] === 'field_property') {
                // Component options based on data compatibility
                $aspectOptions['component'] = $this->getComponentOptions($aspect['current']['component'] ?? '');
                
                // Validation options
                $aspectOptions['validation'] = [
                    ['label' => 'None', 'value' => null],
                    ['label' => 'IC Number Format', 'value' => ['rule' => 'pattern', 'value' => '^[0-9]{6}-[0-9]{2}-[0-9]{4}$']],
                    ['label' => 'Phone Format (MY)', 'value' => ['rule' => 'pattern', 'value' => '^01[0-9]-[0-9]{7,8}$']],
                    ['label' => 'Email Format', 'value' => ['rule' => 'email']],
                    ['label' => 'Numeric Only', 'value' => ['rule' => 'numeric']],
                ];

                // Required options
                $aspectOptions['required'] = [
                    ['label' => 'Required', 'value' => true],
                    ['label' => 'Optional', 'value' => false],
                    ['label' => 'Conditional', 'value' => 'conditional'],
                ];
            }

            if ($aspect['type'] === 'step_property') {
                $aspectOptions['visibility'] = [
                    ['label' => 'Always Visible', 'value' => 'always'],
                    ['label' => 'Conditional', 'value' => 'conditional'],
                ];
            }

            $options[$aspect['target']] = $aspectOptions;
        }

        return $options;
    }

    /**
     * Get compatible component types.
     */
    protected function getComponentOptions(string $current): array
    {
        // Placeholder for component library logic
        return [
            ['label' => 'Text Input', 'value' => 'text_input'],
            ['label' => 'IC Input', 'value' => 'ic_input'],
            ['label' => 'Phone Input', 'value' => 'phone_input'],
            ['label' => 'Amount Input', 'value' => 'amount_input'],
            ['label' => 'Select Dropdown', 'value' => 'select'],
            ['label' => 'Date Picker', 'value' => 'date_picker'],
            ['label' => 'Checkbox', 'value' => 'checkbox'],
        ];
    }
}
