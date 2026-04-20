<?php

namespace App\Runtime\UI;

use App\Studio\Registry\PageDefinition;

class BindingResolver
{
    /**
     * Resolve the flat form data into a structured payload based on field bindings.
     */
    public function resolve(PageDefinition $page, array $formData): array
    {
        $payload = [];

        foreach ($page->steps as $step) {
            foreach ($step->fields as $field) {
                $binding = $field->binding;
                $value = $formData[$field->field_key] ?? null;

                // Map to payload based on binding target path or fallback to field_key
                if ($binding && $binding->target_path) {
                    data_set($payload, $binding->target_path, $value);
                } else {
                    $payload[$field->field_key] = $value;
                }
            }
        }

        return $payload;
    }
}
