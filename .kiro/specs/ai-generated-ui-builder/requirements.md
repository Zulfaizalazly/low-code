# Requirements Document: AI-Generated UI Builder

## Introduction

The AI-Generated UI Builder is a feature for Arrahnumation V3 that leverages Claude Opus 4.7 to automatically generate frontend UI definitions from workflow designs. This feature eliminates the need for HQ users to manually design forms using a complex drag-drop builder, reducing UI creation time from 10-30 minutes to 1-2 minutes while maintaining compliance with domain constraints and validation rules.

The system analyzes workflow context (nodes, commands, data models, business rules) and generates valid PageDefinition JSON schemas that integrate seamlessly with the existing V3 runtime layer (PageLoader, BindingResolver, FormEngine).

**Key Innovation: AI-Assisted Visual Refinement Engine**

Instead of requiring users to type natural language refinement requests, the system automatically detects all editable aspects of the generated UI and presents them as structured, clickable options in a visual modal. This hybrid approach combines the speed of AI generation with the precision of visual selection, providing:

- **Faster refinement**: Click options instead of typing descriptions
- **Reduced errors**: Only valid options are presented based on domain constraints
- **Better discoverability**: Users see all available modifications without guessing
- **Predictable results**: Structured selections produce consistent AI refinements

**Design System Enforcement**

All AI-generated UI strictly adheres to V3 UI Design Guidelines (Apple/iOS inspired, Liquid Glass Design Language) to ensure visual consistency and professional appearance across all features:

- **Typography**: SF Pro font, defined hierarchy (12pt-34pt), weight-based variations
- **Spacing**: 8pt base unit, consistent padding (16/24/32pt), minimum 44x44pt touch targets
- **Colors**: Semantic colors only (systemBlue, systemGray, etc.), automatic light/dark mode
- **Components**: Approved Flux UI library with Liquid Glass material (translucent, backdrop-filter)
- **Icons**: SF Symbols preferred, 2pt stroke, monochrome or limited palette
- **Validation**: 90%+ design compliance score required before publish

**Refinement Flow:**
```
Workflow → AI Generate UI (with Design Constraints) → Validate Design Compliance → 
System Detect Editable Aspects → Auto-Generate Modal → User Select Options → 
AI Update JSON (maintaining Design System) → Validate Compliance → Preview
```

## Glossary

- **AI_Generator**: The service that orchestrates AI-powered UI generation using Claude Opus 4.7 API
- **Flow_Builder**: The existing drag-drop workflow designer where HQ users create operational flows
- **PageDefinition**: JSON schema that defines form structure, fields, bindings, and validations
- **Registry**: Database tables storing page definitions, form steps, form fields, and field bindings
- **Runtime_Layer**: The execution environment that loads and renders published UI (PageLoader, BindingResolver, FormEngine)
- **HQ_User**: Headquarters administrator who designs workflows and features
- **Branch_User**: Branch staff who use the published features in daily operations
- **PublishGateValidator**: Service that validates PageDefinition schemas against 14 validation checks
- **Workflow_Context**: The complete set of flow nodes, commands, data models, and business rules
- **Refinement_Request**: Natural language instruction from user to modify generated UI
- **Generation_Session**: A single AI generation attempt with associated context and results
- **Preview_Mode**: Display mode showing generated UI before publishing
- **Domain_Constraint**: Business rules specific to Arrahnu industry (compliance, audit, data models)
- **Binding_Integrity**: Guarantee that form fields correctly map to database columns and domain models
- **Cost_Budget**: Monthly allocation for AI API usage ($0.30 per generation target)
- **Visual_Refinement_Engine**: AI-assisted system that detects editable aspects of generated UI and presents them as clickable options in a modal
- **Editable_Aspect**: A modifiable element of the PageDefinition (field property, step order, validation rule, etc.)
- **Refinement_Modal**: Auto-generated UI that displays editable aspects as structured options for user selection
- **Aspect_Detector**: Component that analyzes PageDefinition to identify all editable aspects and their valid options
- **Option_Generator**: Component that generates valid choices for each editable aspect based on domain constraints
- **Design_System_Enforcer**: Component that ensures all AI-generated UI adheres to V3 UI Design Guidelines (Apple/iOS inspired, Liquid Glass)
- **UI_Consistency_Validator**: Component that validates generated UI against design system rules (typography, spacing, colors, components)
- **Component_Library**: Approved set of Flux UI components that follow V3 design guidelines
- **Design_Prompt_Template**: Structured prompt template that includes design system constraints for AI generation

## Requirements

### Requirement 1: AI Generation Trigger

**User Story:** As an HQ user, I want to trigger AI-powered UI generation from my workflow design, so that I can quickly create forms without manual building.

#### Acceptance Criteria

1. WHEN an HQ user clicks "Generate UI with AI" button in Flow Builder, THE AI_Generator SHALL initiate a generation session
2. THE Flow_Builder SHALL provide a "Generate UI with AI" button that is visible when a workflow has at least one form submission node
3. WHEN the generation button is clicked, THE AI_Generator SHALL extract complete Workflow_Context including all nodes, commands, data models, and business rules
4. THE AI_Generator SHALL validate that Workflow_Context contains sufficient information before calling the AI API
5. IF Workflow_Context is incomplete, THEN THE AI_Generator SHALL display specific missing information to the HQ user

### Requirement 2: Workflow Context Analysis

**User Story:** As a system architect, I want the AI Generator to analyze complete workflow context, so that generated UI accurately reflects operational requirements.

#### Acceptance Criteria

1. THE AI_Generator SHALL extract all flow nodes from the workflow definition
2. THE AI_Generator SHALL identify all domain commands referenced in command nodes
3. THE AI_Generator SHALL retrieve data model schemas for all entities referenced in the workflow
4. THE AI_Generator SHALL extract business rules and validation constraints from decision nodes
5. THE AI_Generator SHALL identify approval requirements from approval nodes
6. THE AI_Generator SHALL extract document generation requirements from document nodes
7. THE AI_Generator SHALL compile Workflow_Context into a structured format suitable for AI prompt construction
8. THE AI_Generator SHALL include Arrahnu domain constraints (compliance, audit, scoping) in the context

### Requirement 3: AI API Integration

**User Story:** As a system architect, I want to integrate with OpenAI GPT-5.4 API securely and reliably using direct HTTP calls, so that UI generation requests are processed correctly without external SDK dependencies.

#### Acceptance Criteria

1. THE AI_Generator SHALL authenticate with OpenAI API using secure credentials stored in environment configuration (OPENAI_API_KEY)
2. THE AI_Generator SHALL use Laravel's built-in HTTP client (Guzzle) for direct API calls to https://api.openai.com/v1/chat/completions
3. WHEN calling the AI API, THE AI_Generator SHALL construct a structured prompt containing Workflow_Context, V3 UI Design Guidelines, domain constraints, and PageDefinition schema requirements
4. THE AI_Generator SHALL set appropriate API parameters: model (gpt-5.4-turbo), temperature (0.7 for consistency), max_tokens (4000), stream (false for synchronous generation)
5. THE AI_Generator SHALL implement timeout handling with a maximum wait time of 60 seconds
6. IF the API call fails, THEN THE AI_Generator SHALL retry up to 3 times with exponential backoff (1s, 2s, 4s)
7. IF all retries fail, THEN THE AI_Generator SHALL log the error with full context and display a user-friendly error message
8. THE AI_Generator SHALL track API usage metrics (request count, input tokens, output tokens, cost in USD) for budget monitoring
9. THE AI_Generator SHALL validate that API responses contain valid JSON before processing
10. THE AI_Generator SHALL store API configuration in config/ai.php with support for multiple providers (openai as primary)

### Requirement 4: PageDefinition Schema Generation

**User Story:** As a system architect, I want the AI to generate valid PageDefinition JSON schemas, so that generated UI integrates seamlessly with the existing runtime layer.

#### Acceptance Criteria

1. THE AI_Generator SHALL parse AI API responses to extract PageDefinition JSON
2. THE AI_Generator SHALL validate that generated PageDefinition conforms to the V3 schema structure (page_key, steps, fields)
3. THE AI_Generator SHALL ensure all form fields have valid component types from the approved Flux UI component library
4. THE AI_Generator SHALL ensure all field bindings reference valid entity columns or workflow variables
5. THE AI_Generator SHALL include appropriate validation rules (required, format, range) for each field
6. THE AI_Generator SHALL generate multi-step forms when workflow complexity requires it
7. THE AI_Generator SHALL include conditional visibility rules when workflow has branching logic
8. THE AI_Generator SHALL ensure generated schemas respect Arrahnu domain constraints (IC format, gold weight validation, LTV caps)

### Requirement 5: Publish Gate Validation

**User Story:** As a system architect, I want generated UI to pass all validation checks, so that only compliant schemas are published to production.

#### Acceptance Criteria

1. WHEN a PageDefinition is generated, THE AI_Generator SHALL submit it to PublishGateValidator
2. THE PublishGateValidator SHALL execute all 14 validation checks on the generated schema
3. THE PublishGateValidator SHALL verify that all field bindings reference existing database columns
4. THE PublishGateValidator SHALL verify that all component types are supported by the Flux UI library
5. THE PublishGateValidator SHALL verify that all validation rules are syntactically correct
6. THE PublishGateValidator SHALL verify that all referenced entities exist in the domain model
7. THE PublishGateValidator SHALL verify that all workflow variable references are valid
8. IF any validation check fails, THEN THE AI_Generator SHALL capture the specific validation errors
9. THE AI_Generator SHALL display validation errors to the HQ user with actionable guidance

### Requirement 6: UI Preview

**User Story:** As an HQ user, I want to preview generated UI before publishing, so that I can verify it meets my requirements.

#### Acceptance Criteria

1. WHEN PageDefinition generation succeeds, THE AI_Generator SHALL display the generated UI in Preview_Mode
2. THE Preview_Mode SHALL render the form using the same FormEngine component used in production runtime
3. THE Preview_Mode SHALL display all form steps, fields, labels, and validation rules
4. THE Preview_Mode SHALL allow the HQ user to interact with form fields to test behavior
5. THE Preview_Mode SHALL display field bindings and data model mappings for verification
6. THE Preview_Mode SHALL highlight any validation warnings or recommendations
7. THE Preview_Mode SHALL provide action buttons: "Accept and Publish", "Request Refinement", "Manual Override"

### Requirement 7: Natural Language Refinement

**User Story:** As an HQ user, I want to refine generated UI using natural language requests, so that I can adjust the form without manual editing.

#### Acceptance Criteria

1. WHEN an HQ user clicks "Request Refinement" in Preview_Mode, THE AI_Generator SHALL display a text input for Refinement_Request
2. THE AI_Generator SHALL accept natural language instructions such as "add nominee step", "make IC field required", "change field order"
3. WHEN a Refinement_Request is submitted, THE AI_Generator SHALL call the AI API with the original Workflow_Context, current PageDefinition, and the refinement instruction
4. THE AI_Generator SHALL generate an updated PageDefinition that incorporates the requested changes
5. THE AI_Generator SHALL validate the updated PageDefinition using PublishGateValidator
6. THE AI_Generator SHALL display the refined UI in Preview_Mode for user review
7. THE AI_Generator SHALL maintain a refinement history showing all modification requests and results
8. THE AI_Generator SHALL limit refinement iterations to 5 per generation session to control costs

### Requirement 8: Manual Override Option

**User Story:** As an HQ user, I want to manually edit generated UI if needed, so that I have full control when AI generation doesn't meet my exact needs.

#### Acceptance Criteria

1. WHEN an HQ user clicks "Manual Override" in Preview_Mode, THE AI_Generator SHALL save the generated PageDefinition as a draft
2. THE AI_Generator SHALL redirect the HQ user to the Page Builder interface with the draft PageDefinition loaded
3. THE Page_Builder SHALL allow the HQ user to edit all aspects of the PageDefinition (fields, bindings, validation rules)
4. THE Page_Builder SHALL preserve all valid elements from the AI-generated schema
5. THE Page_Builder SHALL validate manual edits using PublishGateValidator before allowing save

### Requirement 9: Publish and Registry Integration

**User Story:** As an HQ user, I want to publish accepted UI to production, so that branch users can use the new feature.

#### Acceptance Criteria

1. WHEN an HQ user clicks "Accept and Publish" in Preview_Mode, THE AI_Generator SHALL save the PageDefinition to the Registry
2. THE AI_Generator SHALL create records in page_definitions, form_steps, form_fields, and field_bindings tables
3. THE AI_Generator SHALL associate the PageDefinition with the correct feature version
4. THE AI_Generator SHALL trigger the standard publish pipeline for the feature
5. WHEN the feature is published, THE Runtime_Layer SHALL load the AI-generated PageDefinition for rendering
6. THE Runtime_Layer SHALL render the form using PageLoader, BindingResolver, and FormEngine
7. THE Runtime_Layer SHALL enforce all validation rules defined in the PageDefinition
8. THE Runtime_Layer SHALL correctly bind form submissions to domain commands and data models

### Requirement 10: Cost Monitoring and Budget Control

**User Story:** As a system administrator, I want to monitor OpenAI API costs with detailed tracking and automatic alerts, so that I can ensure usage stays within budget.

#### Acceptance Criteria

1. THE AI_Generator SHALL log every AI API call in ai_usage_logs table with: user_id, org_id, feature_type, provider (openai), model_used (gpt-5.4-turbo), tokens_input, tokens_output, cost_usd, used_at timestamp
2. THE AI_Generator SHALL calculate cost using OpenAI pricing: GPT-5.4 Turbo ($0.15 per 1M input tokens, $0.60 per 1M output tokens)
3. THE AI_Generator SHALL calculate cumulative monthly costs per organization and per user
4. THE AI_Generator SHALL display current month usage and cost in the HQ Studio dashboard with breakdown by: total requests, total cost, cost by feature type, cost by user
5. WHEN monthly costs approach 80% of budget (configurable via AI_WARN_COST_THRESHOLD), THE AI_Generator SHALL display a warning banner to administrators
6. WHEN monthly costs exceed budget, THE AI_Generator SHALL disable AI generation for that organization and display a budget exceeded message with contact information
7. THE AI_Generator SHALL provide a cost report accessible via artisan command (php artisan ai:check-costs) showing: today's cost, breakdown by model, request count, average cost per request
8. THE AI_Generator SHALL target an average cost of $0.30 per generation through prompt optimization and efficient token usage
9. THE AI_Generator SHALL implement rate limiting: 10 requests per minute per user, 100 requests per hour per user (configurable via AI_RATE_LIMIT_PER_MINUTE and AI_RATE_LIMIT_PER_HOUR)
10. THE AI_Generator SHALL schedule hourly cost checks via Laravel scheduler to send email alerts when thresholds are exceeded

### Requirement 11: Error Handling and Resilience

**User Story:** As an HQ user, I want clear error messages when generation fails, so that I can understand what went wrong and how to proceed.

#### Acceptance Criteria

1. IF the AI API is unavailable, THEN THE AI_Generator SHALL display "AI service temporarily unavailable, please try again later"
2. IF the AI API returns invalid JSON, THEN THE AI_Generator SHALL log the raw response and display "Generation failed due to invalid response format"
3. IF the generated PageDefinition fails validation, THEN THE AI_Generator SHALL display specific validation errors with guidance
4. IF Workflow_Context is incomplete, THEN THE AI_Generator SHALL display which required elements are missing
5. IF the generation timeout is exceeded, THEN THE AI_Generator SHALL cancel the request and display "Generation timed out, please try again"
6. THE AI_Generator SHALL log all errors with full context for debugging
7. THE AI_Generator SHALL provide a "Report Issue" button that captures error details for support

### Requirement 12: Audit Trail and Traceability

**User Story:** As a compliance officer, I want complete audit trails for AI-generated UI, so that I can verify system integrity and compliance.

#### Acceptance Criteria

1. THE AI_Generator SHALL create an audit record for every generation session
2. THE audit record SHALL include: timestamp, HQ user, workflow context, AI prompt, AI response, generated PageDefinition, validation results
3. THE AI_Generator SHALL record all refinement requests and their outcomes
4. THE AI_Generator SHALL record whether generated UI was accepted, refined, or manually overridden
5. THE AI_Generator SHALL link audit records to published feature versions
6. THE audit trail SHALL be immutable and stored in the audit_trails table
7. THE AI_Generator SHALL provide an audit report showing all AI-generated features and their generation history

### Requirement 13: Performance and Scalability

**User Story:** As a system administrator, I want AI generation to complete quickly and handle concurrent requests, so that HQ users have a smooth experience.

#### Acceptance Criteria

1. THE AI_Generator SHALL complete generation requests in under 2 minutes for typical workflows
2. THE AI_Generator SHALL handle up to 10 concurrent generation requests without performance degradation
3. THE AI_Generator SHALL queue generation requests when concurrent limit is reached
4. THE AI_Generator SHALL display queue position and estimated wait time to HQ users
5. THE AI_Generator SHALL cache Workflow_Context extraction to avoid redundant processing
6. THE AI_Generator SHALL optimize AI prompts to minimize token usage while maintaining quality
7. THE AI_Generator SHALL support 100+ feature generations per month within performance targets

### Requirement 14: Parser and Pretty Printer for PageDefinition

**User Story:** As a system architect, I want robust parsing and formatting of PageDefinition JSON, so that generated schemas are correctly processed and human-readable.

#### Acceptance Criteria

1. WHEN AI API returns a response, THE PageDefinition_Parser SHALL parse the JSON into a structured PageDefinition object
2. WHEN parsing fails, THE PageDefinition_Parser SHALL return descriptive error messages indicating the location and nature of the syntax error
3. THE Pretty_Printer SHALL format PageDefinition objects into valid, human-readable JSON with consistent indentation
4. FOR ALL valid PageDefinition objects, parsing then printing then parsing SHALL produce an equivalent object (round-trip property)
5. THE PageDefinition_Parser SHALL validate JSON structure against the V3 PageDefinition schema
6. THE Pretty_Printer SHALL preserve all semantic information while improving readability
7. THE PageDefinition_Parser SHALL handle nested structures (steps, fields, bindings) correctly

### Requirement 15: Integration with Existing Flow Builder

**User Story:** As an HQ user, I want AI generation to integrate seamlessly with my existing workflow design process, so that I have a unified experience.

#### Acceptance Criteria

1. THE Flow_Builder SHALL display the "Generate UI with AI" button in the toolbar when a workflow is open
2. THE Flow_Builder SHALL disable the button when the workflow has no form submission nodes
3. WHEN generation completes, THE Flow_Builder SHALL automatically link the generated PageDefinition to the workflow
4. THE Flow_Builder SHALL display a visual indicator showing which workflows have AI-generated UI
5. THE Flow_Builder SHALL allow HQ users to regenerate UI if the workflow is modified
6. WHEN regenerating, THE AI_Generator SHALL detect changes in Workflow_Context and highlight them in the preview
7. THE Flow_Builder SHALL preserve manual overrides when regenerating unless explicitly requested to discard them

### Requirement 16: AI-Assisted Visual Refinement Engine

**User Story:** As an HQ user, I want to refine generated UI by selecting from auto-detected options in a visual modal, so that I can make precise changes without typing natural language instructions.

#### Acceptance Criteria

1. WHEN an HQ user clicks "Refine UI" in Preview_Mode, THE Visual_Refinement_Engine SHALL analyze the generated PageDefinition to detect all Editable_Aspects
2. THE Aspect_Detector SHALL identify editable aspects including: field properties (label, placeholder, required, validation), step order, field order, component types, visibility rules, and data bindings
3. THE Option_Generator SHALL generate valid choices for each Editable_Aspect based on domain constraints and available options
4. THE Visual_Refinement_Engine SHALL display a Refinement_Modal containing all detected Editable_Aspects organized by category (Fields, Steps, Validation, Layout)
5. THE Refinement_Modal SHALL present each Editable_Aspect as a clickable option with clear labels and descriptions
6. WHEN an HQ user selects an option in the Refinement_Modal, THE Visual_Refinement_Engine SHALL highlight the selection and show a preview of the change
7. THE Refinement_Modal SHALL allow multiple selections across different aspects before applying changes
8. WHEN an HQ user clicks "Apply Changes", THE Visual_Refinement_Engine SHALL construct a structured refinement instruction from the selected options
9. THE Visual_Refinement_Engine SHALL call the AI API with the structured instruction to generate an updated PageDefinition
10. THE Visual_Refinement_Engine SHALL validate the updated PageDefinition using PublishGateValidator
11. THE Visual_Refinement_Engine SHALL display the refined UI in Preview_Mode with visual indicators showing what changed
12. THE Visual_Refinement_Engine SHALL maintain a refinement history showing all option selections and their outcomes

### Requirement 17: Editable Aspect Detection

**User Story:** As a system architect, I want the system to automatically detect all editable aspects of generated UI, so that users have comprehensive refinement options.

#### Acceptance Criteria

1. THE Aspect_Detector SHALL analyze PageDefinition structure to identify all form steps
2. FOR EACH form step, THE Aspect_Detector SHALL detect: step title, step description, step order, visibility conditions, and required status
3. FOR EACH form field, THE Aspect_Detector SHALL detect: field label, field type, placeholder text, help text, required status, default value, validation rules, and data binding
4. THE Aspect_Detector SHALL identify field ordering within each step
5. THE Aspect_Detector SHALL identify available component types from the Flux UI library that are compatible with each field's data type
6. THE Aspect_Detector SHALL identify validation rule options (required, min/max length, format patterns, custom rules)
7. THE Aspect_Detector SHALL identify visibility rule options based on workflow variables and field values
8. THE Aspect_Detector SHALL categorize detected aspects into logical groups: Field Properties, Step Configuration, Validation Rules, Layout Options, Data Bindings

### Requirement 18: Option Generation for Editable Aspects

**User Story:** As a system architect, I want the system to generate valid options for each editable aspect, so that users only see choices that comply with domain constraints.

#### Acceptance Criteria

1. FOR field label edits, THE Option_Generator SHALL provide: "Edit Label" with text input, "Use Field Name" option, "Use Domain Term" with dropdown of standard terms
2. FOR field type changes, THE Option_Generator SHALL provide only component types compatible with the field's data binding (e.g., text_input, ic_input, phone_input for string fields)
3. FOR required status, THE Option_Generator SHALL provide: "Required", "Optional", "Conditionally Required" with condition builder
4. FOR validation rules, THE Option_Generator SHALL provide: standard patterns (IC format, phone format, email), range constraints (min/max), custom regex with preview
5. FOR step ordering, THE Option_Generator SHALL provide: "Move Up", "Move Down", "Move to Position" with step list
6. FOR field ordering, THE Option_Generator SHALL provide: drag-drop reordering interface within the modal
7. FOR visibility rules, THE Option_Generator SHALL provide: "Always Visible", "Conditional" with workflow variable selector and condition builder
8. FOR data bindings, THE Option_Generator SHALL provide only valid entity columns and workflow variables based on the field's context
9. THE Option_Generator SHALL validate all generated options against domain constraints before displaying them
10. THE Option_Generator SHALL mark recommended options based on best practices and workflow context

### Requirement 19: Refinement Modal User Experience

**User Story:** As an HQ user, I want an intuitive refinement modal interface, so that I can easily understand and select refinement options.

#### Acceptance Criteria

1. THE Refinement_Modal SHALL display a tabbed interface with categories: Fields, Steps, Validation, Layout, Advanced
2. THE Refinement_Modal SHALL show a live preview pane that updates as options are selected
3. THE Refinement_Modal SHALL highlight changed elements in the preview with visual indicators (border, background color)
4. THE Refinement_Modal SHALL provide a search/filter function to quickly find specific fields or aspects
5. THE Refinement_Modal SHALL display option descriptions and examples for complex choices
6. THE Refinement_Modal SHALL show validation warnings if selected options conflict with domain constraints
7. THE Refinement_Modal SHALL provide "Reset" button to undo all selections and "Apply Changes" button to commit
8. THE Refinement_Modal SHALL display estimated AI API cost for the refinement operation
9. THE Refinement_Modal SHALL be responsive and work on different screen sizes
10. THE Refinement_Modal SHALL provide keyboard shortcuts for common actions (Esc to close, Enter to apply)

### Requirement 20: Structured Refinement Instruction Construction

**User Story:** As a system architect, I want selected options to be converted into structured instructions for the AI, so that refinements are precise and predictable.

#### Acceptance Criteria

1. WHEN an HQ user selects options and clicks "Apply Changes", THE Visual_Refinement_Engine SHALL construct a structured JSON instruction containing all selected modifications
2. THE structured instruction SHALL include: aspect type (field_property, step_order, validation_rule), target element (field key, step key), modification type (update, add, remove), and new value
3. THE Visual_Refinement_Engine SHALL convert the structured instruction into a natural language prompt for the AI API
4. THE AI prompt SHALL include: current PageDefinition, structured instruction, domain constraints, and validation requirements
5. THE Visual_Refinement_Engine SHALL include examples of similar successful refinements in the prompt for consistency
6. THE Visual_Refinement_Engine SHALL validate the structured instruction for completeness before calling the AI API
7. THE Visual_Refinement_Engine SHALL log the structured instruction and AI prompt for audit and debugging

### Requirement 21: UI Design System Enforcement

**User Story:** As a design lead, I want all AI-generated UI to follow V3 Design Guidelines (Apple/iOS inspired, Liquid Glass), so that the system maintains visual consistency and professional appearance.

#### Acceptance Criteria

1. THE AI_Generator SHALL include V3 UI Design Guidelines in every AI prompt for UI generation
2. THE Design_Prompt_Template SHALL specify: typography scale (SF Pro font, 17pt body, defined hierarchy), color system (semantic colors, light/dark mode support), spacing rules (8pt base unit, 16/24/32pt padding), component styles (Liquid Glass material, rounded corners 8-20pt), and icon guidelines (SF Symbols, 2pt stroke, monochrome)
3. THE AI_Generator SHALL enforce approved Component_Library usage (only Flux UI components that follow V3 guidelines)
4. THE AI_Generator SHALL specify corner radius standards: 8-12pt for buttons, 16pt for cards, 20pt for modals
5. THE AI_Generator SHALL enforce minimum touch target size of 44x44pt for all interactive elements
6. THE AI_Generator SHALL specify spacing between interactive elements (minimum 8pt)
7. THE AI_Generator SHALL enforce typography hierarchy using weight variations (Regular, Medium, Semibold, Bold) rather than arbitrary size changes
8. THE AI_Generator SHALL specify color usage: semantic colors only (systemBlue, systemGray, systemRed, systemGreen, systemOrange), no arbitrary gradients, automatic light/dark mode support
9. THE AI_Generator SHALL enforce icon consistency: SF Symbols preferred, 2pt stroke weight, 24x24pt or 28x28pt size, monochrome or limited palette (2-3 colors max)
10. THE AI_Generator SHALL specify Liquid Glass material properties for containers: translucent background (rgba with 0.1-0.2 alpha), backdrop-filter blur (20px), subtle border (1px, rgba white 0.2), appropriate shadow (0 8px 32px rgba black 0.1)

### Requirement 22: UI Consistency Validation

**User Story:** As a quality assurance engineer, I want generated UI to be validated against design system rules, so that inconsistent or non-compliant UI is rejected before preview.

#### Acceptance Criteria

1. WHEN a PageDefinition is generated, THE UI_Consistency_Validator SHALL validate all components against the approved Component_Library
2. THE UI_Consistency_Validator SHALL verify that all text elements use approved typography scale (12pt to 34pt, defined weights)
3. THE UI_Consistency_Validator SHALL verify that all spacing values are multiples of 8pt base unit
4. THE UI_Consistency_Validator SHALL verify that all interactive elements meet minimum touch target size (44x44pt)
5. THE UI_Consistency_Validator SHALL verify that all colors are semantic colors from the approved palette
6. THE UI_Consistency_Validator SHALL verify that all corner radius values match design standards (8-12pt, 16pt, or 20pt)
7. THE UI_Consistency_Validator SHALL verify that all icons reference SF Symbols or approved custom icons
8. THE UI_Consistency_Validator SHALL verify that Liquid Glass material properties are correctly applied to containers
9. IF validation fails, THEN THE UI_Consistency_Validator SHALL provide specific design violations with guidance on how to fix them
10. THE UI_Consistency_Validator SHALL generate a design compliance report showing adherence percentage to V3 guidelines

### Requirement 23: Design System Prompt Engineering

**User Story:** As a system architect, I want AI prompts to include comprehensive design system constraints, so that generated UI is consistently compliant without manual corrections.

#### Acceptance Criteria

1. THE Design_Prompt_Template SHALL include a "Design System Constraints" section in every AI prompt
2. THE design constraints section SHALL specify: "Follow V3 UI Design Guidelines based on Apple Human Interface Guidelines and Liquid Glass Design Language"
3. THE design constraints section SHALL include typography rules: "Use SF Pro font family. Body text: 17pt Regular. Hierarchy: Display 34pt Bold, Title1 28pt Bold, Title2 22pt Bold, Title3 20pt Semibold, Headline 17pt Semibold, Subhead 15pt Regular, Footnote 13pt Regular, Caption 12pt Regular"
4. THE design constraints section SHALL include spacing rules: "Use 8pt base unit. Padding: 16pt, 24pt, or 32pt. Margins: 16pt (mobile), 24pt (tablet). Minimum spacing between interactive elements: 8pt"
5. THE design constraints section SHALL include color rules: "Use semantic colors only: systemBlue (#007AFF) for primary actions, systemRed (#FF3B30) for destructive, systemGreen (#34C759) for success, systemOrange (#FF9500) for warning, systemGray (#8E8E93) for neutral. Support light and dark mode automatically"
6. THE design constraints section SHALL include component rules: "Use Liquid Glass material for containers: background rgba(255,255,255,0.1), backdrop-filter blur(20px) saturate(180%), border 1px rgba(255,255,255,0.2), border-radius 16pt, box-shadow 0 8px 32px rgba(0,0,0,0.1)"
7. THE design constraints section SHALL include button rules: "Primary buttons: filled with Liquid Glass, 8-12pt corner radius. Secondary: outlined, transparent. Minimum size: 44x44pt. Press animation: scale(0.95)"
8. THE design constraints section SHALL include icon rules: "Use SF Symbols. Style: line-based, 2pt stroke weight, rounded line caps. Size: 24x24pt or 28x28pt. Color: monochrome or limited palette (max 2-3 colors)"
9. THE design constraints section SHALL include form rules: "Input fields: 12pt corner radius, subtle border. Labels: above input, 15pt Subhead. Focus state: systemBlue border. Error state: systemRed border with message below"
10. THE AI_Generator SHALL include 2-3 examples of compliant UI components in the prompt for reference

### Requirement 24: Component Library Restriction

**User Story:** As a system architect, I want AI to only use approved Flux UI components, so that generated UI is technically compatible and visually consistent.

#### Acceptance Criteria

1. THE Component_Library SHALL define approved components: text_input, ic_input, phone_input, amount_input, textarea, select, date_picker, checkbox, radio, file_upload, repeater, card, button, badge, alert, modal, tabs
2. FOR EACH approved component, THE Component_Library SHALL specify: allowed properties, default styling (Liquid Glass material, corner radius, spacing), size variants (small, medium, large), state variants (default, hover, focus, disabled, error)
3. THE AI_Generator SHALL include the complete Component_Library specification in every generation prompt
4. THE AI_Generator SHALL explicitly instruct: "Only use components from the approved Component_Library. Do not invent new component types"
5. THE PageDefinition_Parser SHALL reject any component types not in the approved Component_Library
6. THE UI_Consistency_Validator SHALL verify that all component properties match the Component_Library specification
7. THE Component_Library SHALL be versioned and stored in the codebase (e.g., config/component-library.json)
8. THE Component_Library SHALL include visual examples and code snippets for each component
9. WHEN the Component_Library is updated, THE AI_Generator SHALL use the latest version for all new generations
10. THE Component_Library SHALL map to actual Flux UI components in the runtime layer for rendering

### Requirement 25: Design Compliance Reporting

**User Story:** As a design lead, I want to see design compliance metrics for AI-generated UI, so that I can monitor consistency and identify areas for improvement.

#### Acceptance Criteria

1. THE UI_Consistency_Validator SHALL generate a design compliance report for every generated PageDefinition
2. THE compliance report SHALL include: overall compliance score (0-100%), typography compliance (font usage, size adherence, hierarchy correctness), spacing compliance (8pt grid adherence, touch target sizes), color compliance (semantic color usage, palette adherence), component compliance (approved components only, correct properties), material compliance (Liquid Glass properties, corner radius standards)
3. THE compliance report SHALL highlight specific violations with severity levels: critical (blocks publish), warning (should fix), info (recommendation)
4. THE compliance report SHALL provide actionable guidance for each violation (e.g., "Button size 40x40pt is below minimum 44x44pt. Increase to 44x44pt")
5. THE AI_Generator SHALL display the compliance report in the Preview_Mode alongside the generated UI
6. THE HQ user SHALL be able to view detailed compliance metrics by clicking on highlighted violations
7. THE system SHALL track compliance scores over time and display trends in the HQ Studio dashboard
8. THE system SHALL calculate average compliance score across all AI-generated features
9. THE system SHALL identify common violation patterns and suggest prompt improvements to the system administrator
10. THE compliance report SHALL be stored in the audit trail for each generation session

## Correctness Properties for Testing

### Property 1: Round-Trip Parsing (PageDefinition Parser)
**Type:** Round Trip Property  
**Description:** Parsing a PageDefinition JSON, formatting it with Pretty_Printer, and parsing again SHALL produce an equivalent PageDefinition object.  
**Test:** FOR ALL valid PageDefinition JSON strings `json`, `parse(pretty_print(parse(json))) == parse(json)`

### Property 2: Validation Idempotence
**Type:** Idempotence Property  
**Description:** Running PublishGateValidator multiple times on the same PageDefinition SHALL produce identical validation results.  
**Test:** FOR ALL PageDefinition objects `pd`, `validate(pd) == validate(validate(pd))`

### Property 3: Binding Integrity Invariant
**Type:** Invariant Property  
**Description:** All field bindings in a generated PageDefinition SHALL reference existing database columns or valid workflow variables.  
**Test:** FOR ALL generated PageDefinition objects `pd`, ALL field bindings `fb` in `pd`, `exists(fb.target_entity, fb.target_path) == true`

### Property 4: Cost Budget Constraint
**Type:** Invariant Property  
**Description:** Monthly AI API costs SHALL NOT exceed the configured budget limit.  
**Test:** FOR ALL months `m`, `sum(generation_costs(m)) <= budget_limit`

### Property 5: Generation Determinism
**Type:** Metamorphic Property  
**Description:** Generating UI from identical Workflow_Context twice SHALL produce functionally equivalent PageDefinitions (same fields, bindings, validation rules).  
**Test:** FOR ALL Workflow_Context `wc`, `equivalent(generate(wc), generate(wc)) == true`

### Property 6: Refinement Convergence
**Type:** Metamorphic Property  
**Description:** Applying a refinement request SHALL produce a PageDefinition that is closer to the requested state than the original.  
**Test:** FOR ALL PageDefinition `pd`, Refinement_Request `rr`, `distance(refine(pd, rr), rr) < distance(pd, rr)`

### Property 7: Validation Error Completeness
**Type:** Model-Based Property  
**Description:** PublishGateValidator SHALL detect ALL schema violations, not just the first error.  
**Test:** FOR ALL invalid PageDefinition objects `pd`, `count(validate(pd).errors) >= count(actual_violations(pd))`

### Property 8: API Retry Resilience
**Type:** Error Condition Property  
**Description:** When AI API calls fail transiently, retry logic SHALL eventually succeed or exhaust retries.  
**Test:** FOR ALL transient API failures, `retry(api_call) == success OR retry_count == max_retries`

### Property 9: Concurrent Request Isolation
**Type:** Invariant Property  
**Description:** Concurrent generation requests SHALL NOT interfere with each other's Workflow_Context or results.  
**Test:** FOR ALL concurrent requests `r1, r2`, `generate(r1.context) == generate(r1.context)` regardless of `r2` execution

### Property 10: Audit Trail Completeness
**Type:** Invariant Property  
**Description:** Every generation session SHALL produce exactly one audit record with complete information.  
**Test:** FOR ALL generation sessions `gs`, `count(audit_records(gs)) == 1 AND audit_records(gs).is_complete == true`

### Property 11: Editable Aspect Detection Completeness
**Type:** Model-Based Property  
**Description:** The Aspect_Detector SHALL identify ALL modifiable elements in a PageDefinition.  
**Test:** FOR ALL PageDefinition objects `pd`, `count(detect_aspects(pd)) >= count(actual_modifiable_elements(pd))`

### Property 12: Option Validity Constraint
**Type:** Invariant Property  
**Description:** All options generated by Option_Generator SHALL comply with domain constraints and validation rules.  
**Test:** FOR ALL generated options `opt`, `validate_domain_constraints(opt) == true`

### Property 13: Refinement Modal Consistency
**Type:** Metamorphic Property  
**Description:** Opening the Refinement_Modal multiple times for the same PageDefinition SHALL show identical editable aspects and options.  
**Test:** FOR ALL PageDefinition objects `pd`, `detect_aspects(pd) == detect_aspects(pd)`

### Property 14: Structured Instruction Completeness
**Type:** Invariant Property  
**Description:** Structured refinement instructions SHALL contain all information needed to apply the requested changes.  
**Test:** FOR ALL option selections `sel`, `is_complete(construct_instruction(sel)) == true`

### Property 15: Visual Refinement Convergence
**Type:** Metamorphic Property  
**Description:** Applying refinement options SHALL produce a PageDefinition that matches the selected options.  
**Test:** FOR ALL PageDefinition `pd`, option selections `sel`, `matches(refine(pd, sel), sel) == true`

### Property 16: Design System Compliance
**Type:** Invariant Property  
**Description:** All AI-generated PageDefinitions SHALL achieve minimum 90% compliance with V3 UI Design Guidelines.  
**Test:** FOR ALL generated PageDefinition objects `pd`, `design_compliance_score(pd) >= 90`

### Property 17: Component Library Restriction
**Type:** Invariant Property  
**Description:** All components in generated PageDefinitions SHALL be from the approved Component_Library.  
**Test:** FOR ALL generated PageDefinition objects `pd`, ALL components `c` in `pd`, `c.type IN approved_components`

### Property 18: Typography Consistency
**Type:** Invariant Property  
**Description:** All text elements SHALL use approved typography scale and weights.  
**Test:** FOR ALL generated PageDefinition objects `pd`, ALL text elements `t` in `pd`, `t.font_size IN approved_sizes AND t.font_weight IN approved_weights`

### Property 19: Spacing Grid Adherence
**Type:** Invariant Property  
**Description:** All spacing values SHALL be multiples of the 8pt base unit.  
**Test:** FOR ALL generated PageDefinition objects `pd`, ALL spacing values `s` in `pd`, `s % 8 == 0`

### Property 20: Touch Target Compliance
**Type:** Invariant Property  
**Description:** All interactive elements SHALL meet minimum touch target size of 44x44pt.  
**Test:** FOR ALL generated PageDefinition objects `pd`, ALL interactive elements `e` in `pd`, `e.width >= 44 AND e.height >= 44`

---

*End of Requirements Document*
