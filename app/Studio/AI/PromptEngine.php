<?php

namespace App\Studio\AI;

use App\Studio\Registry\FlowDefinition;

class PromptEngine
{
    /**
     * Construct the system prompt for UI generation.
     */
    public function getSystemPrompt(): string
    {
        return <<<PROMPT
You are an expert Frontend Architect for Arrahnumation V3, a high-end low-code platform for the Arrahnu industry.
Your task is to generate valid PageDefinition JSON schemas that adhere to the V3 UI Design Guidelines.

### DESIGN GUIDELINES (V3 UI / Apple-iOS Inspired)
1. **Design System**: Liquid Glass Design Language (Apple Human Interface Guidelines).
2. **Typography**: SF Pro font family.
   - Body: 17pt Regular.
   - Hierarchy: Display (34pt Bold), Title1 (28pt Bold), Title2 (22pt Bold), Headline (17pt Semibold), Subhead (15pt Regular), Footnote (13pt Regular).
   - Use font weight (Regular, Medium, Semibold, Bold) for emphasis.
3. **Spacing**: 8pt base unit grid.
   - Padding: 16pt, 24pt, or 32pt.
   - Margins: 16pt (mobile), 24pt (tablet).
4. **Colors**: Use semantic colors ONLY.
   - systemBlue (#007AFF) for primary actions.
   - systemRed (#FF3B30) for destructive actions.
   - systemGreen (#34C759) for success.
   - systemOrange (#FF9500) for warnings.
   - systemGray (#8E8E93) for neutral elements.
5. **Components**: Use approved Component Library:
   - `text_input`, `ic_input`, `phone_input`, `amount_input`, `textarea`, `select`, `date_picker`, `checkbox`, `radio`, `file_upload`, `repeater`, `card`, `button`, `badge`, `alert`, `modal`, `tabs`.
6. **Visual Style**:
   - Liquid Glass material: translucency (blur 20px), subtle border (1pt), shadow (rgba 0,0,0,0.1).
   - Corner Radius: 12pt for inputs, 16pt for cards, 20pt for modals.
   - Touch Targets: Minimum 44x44pt for interactive elements.
7. **Icons**: SF Symbols preferred. Style: 2pt stroke weight, rounded caps, monochrome.

### SCHEMA STRUCTURE (JSON)
The PageDefinition MUST follow this structure:
{
  "page_key": "unique_string",
  "steps": [
    {
      "step_key": "step_1",
      "title": "Step Title",
      "fields": [
        {
          "field_key": "field_1",
          "component_type": "text_input",
          "label": "Field Label",
          "placeholder": "...",
          "required": true,
          "binding": { "target_entity": "EntityName", "target_path": "column_name" },
          "validation": { "rule": "pattern", "value": "..." }
        }
      ]
    }
  ]
}

### OUTPUT REQUIREMENTS
- Respond with VALID JSON ONLY.
- Ensure all bindings map to relevant domain entities and columns mentioned in the workflow context.
- Respect all Arrahnu domain constraints (e.g. Gold Weight formatting, LTV limits).
PROMPT;
    }

    /**
     * Construct the user prompt for UI generation based on flow context.
     */
    public function buildGenerationPrompt(array $context): string
    {
        $contextJson = json_encode($context, JSON_PRETTY_PRINT);
        
        return <<<PROMPT
Generate a PageDefinition JSON for the following Workflow Context:

{$contextJson}

Instructions:
1. Analyze all workflow nodes (trigger, command, decision, etc.).
2. Map form fields to the data models and commands referenced.
3. If the workflow has multiple branching paths, create appropriate conditional visibility or multi-step logic.
4. Ensure the UI flows logically according to the node order.
PROMPT;
    }

    /**
     * Build refinement prompt.
     */
    public function buildRefinementPrompt(array $currentDefinition, string $instruction): string
    {
        $currentJson = json_encode($currentDefinition, JSON_PRETTY_PRINT);

        return <<<PROMPT
The user wants to refine the following PageDefinition:

CURRENT DEFINITION:
{$currentJson}

REFINEMENT INSTRUCTION:
"{$instruction}"

Update the JSON definition according to the instruction while maintaining strict adherence to the V3 Design System and Schema Structure.
PROMPT;
    }
}
