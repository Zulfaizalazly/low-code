# Visual Builders - Requirements Document

**Feature Name:** Visual Builders (Flow Builder + Page Builder)  
**Priority:** CRITICAL (P0)  
**Estimated Effort:** 8-10 weeks  
**Dependencies:** Registry models (✅ Complete), Runtime engine (✅ Complete)  
**Status:** Draft

---

## 1. Executive Summary

### Problem Statement
Currently, HQ users **cannot design features without writing code**. The system has:
- ✅ Registry models to store flow/page definitions
- ✅ Runtime engine to execute flows
- ❌ **NO VISUAL INTERFACE** for HQ users to design flows and pages

This breaks the core product promise: "Low-code platform where clients can build features without code."

### Solution Overview
Build two visual builders:
1. **Flow Builder** - Drag-and-drop canvas for designing operational workflows
2. **Page Builder** - Drag-and-drop interface for designing forms and pages

Both builders must:
- Save to existing registry tables (no schema changes needed)
- Integrate with existing runtime engine
- Support real-time validation
- Provide preview functionality

### Success Criteria
- ✅ HQ user can design a complete flow without touching code
- ✅ HQ user can design a complete page without touching code
- ✅ Designed flows execute correctly in runtime
- ✅ Designed pages render correctly for branch users
- ✅ All designs save to registry tables
- ✅ Round-trip works: design → save → reload → edit

---

## 2. User Stories

### Epic 1: Flow Builder

#### US-FB-001: View Flow Canvas
**As a** HQ Designer  
**I want to** see a visual canvas for flow design  
**So that** I can understand the flow structure at a glance

**Acceptance Criteria:**
- Canvas displays with grid background
- Zoom controls (25%, 50%, 100%, 150%, 200%)
- Pan functionality (drag canvas)
- Minimap for navigation
- Fit-to-screen button

#### US-FB-002: Add Nodes from Palette
**As a** HQ Designer  
**I want to** drag nodes from a palette onto the canvas  
**So that** I can build my workflow

**Acceptance Criteria:**
- Node palette organized by categories:
  - Entry (Trigger, Manual Start)
  - Commands (Execute Domain Command)
  - Decision (Branch, Condition)
  - Approval (Create Approval Task)
  - Notification (Send SMS, Send Email)
  - Document (Generate Document)
  - Financial (Post GL Entry)
  - System (Delay, End)
- Drag node from palette to canvas
- Node appears at drop location
- Node has unique ID
- Node shows icon and label

#### US-FB-003: Connect Nodes
**As a** HQ Designer  
**I want to** connect nodes with edges  
**So that** I can define the flow sequence

**Acceptance Criteria:**
- Click and drag from node output handle
- Drop on another node's input handle
- Edge appears with arrow
- Edge can have label (condition)
- Edge can be deleted
- Invalid connections are prevented (e.g., End node has no output)

#### US-FB-004: Configure Node Properties
**As a** HQ Designer  
**I want to** configure each node's settings  
**So that** I can define what the node does

**Acceptance Criteria:**
- Click node to open properties panel
- Properties panel shows:
  - Node type
  - Node label (editable)
  - Node-specific config fields
  - Validation errors (if any)
- Changes save automatically
- Invalid config shows warning icon on node

#### US-FB-005: Validate Flow
**As a** HQ Designer  
**I want to** see validation errors in real-time  
**So that** I can fix issues before saving

**Acceptance Criteria:**
- Flow validates on every change
- Validation checks:
  - Has trigger/start node
  - Has end node
  - All nodes connected
  - No orphaned nodes
  - No circular dependencies (optional)
  - All required node configs complete
- Errors show in validation panel
- Invalid nodes highlighted in red
- Save button disabled if validation fails

#### US-FB-006: Save Flow
**As a** HQ Designer  
**I want to** save my flow design  
**So that** it persists in the system

**Acceptance Criteria:**
- Save button in toolbar
- Saves to `flow_definitions`, `flow_nodes`, `flow_edges` tables
- Shows success message
- Auto-save every 30 seconds (draft)
- Version number increments

#### US-FB-007: Load Existing Flow
**As a** HQ Designer  
**I want to** load and edit existing flows  
**So that** I can make changes

**Acceptance Criteria:**
- Load flow from feature version
- Nodes appear at saved positions
- Edges render correctly
- Properties load correctly
- Can edit and re-save

#### US-FB-008: Delete Nodes/Edges
**As a** HQ Designer  
**I want to** delete nodes and edges  
**So that** I can correct mistakes

**Acceptance Criteria:**
- Select node → press Delete key
- Select edge → press Delete key
- Confirmation dialog for node deletion
- Deleting node also deletes connected edges
- Undo/Redo support (nice-to-have)

### Epic 2: Page Builder

#### US-PB-001: View Page Canvas
**As a** HQ Designer  
**I want to** see a visual canvas for page design  
**So that** I can design forms visually

**Acceptance Criteria:**
- Canvas shows page preview
- Component library on left
- Properties panel on right
- Responsive preview modes (desktop, tablet, mobile)

#### US-PB-002: Add Form Steps
**As a** HQ Designer  
**I want to** add steps to a multi-step form  
**So that** I can organize form into sections

**Acceptance Criteria:**
- "Add Step" button
- Step appears in stepper preview
- Step has title and description
- Steps can be reordered (drag-drop)
- Steps can be deleted

#### US-PB-003: Add Fields to Steps
**As a** HQ Designer  
**I want to** drag fields from component library onto steps  
**So that** I can build the form

**Acceptance Criteria:**
- Component library shows:
  - Input Fields (Text, Number, Date, Amount)
  - Domain Fields (IC Number, Phone)
  - Selection (Dropdown, Radio, Checkbox)
  - Repeaters (Gold Item, Nominee)
  - Display (Summary Panel, Timeline)
- Drag component to step
- Component appears in step
- Component has default label

#### US-PB-004: Configure Field Properties
**As a** HQ Designer  
**I want to** configure field settings  
**So that** I can customize field behavior

**Acceptance Criteria:**
- Click field to open properties panel
- Properties include:
  - Label
  - Field key
  - Component type
  - Required (yes/no)
  - Placeholder
  - Help text
  - Validation rules
  - Data binding (entity.field)
- Changes reflect in preview immediately

#### US-PB-005: Set Data Bindings
**As a** HQ Designer  
**I want to** bind fields to database entities  
**So that** data saves correctly

**Acceptance Criteria:**
- Binding selector shows available entities:
  - customers
  - facilities
  - facility_items
  - facility_nominees
  - valuations
- Select entity → shows fields
- Select field → binding saved
- Binding validation (entity exists, field exists)
- Unbound fields show warning

#### US-PB-006: Preview Page
**As a** HQ Designer  
**I want to** preview how the page will look  
**So that** I can verify design before saving

**Acceptance Criteria:**
- Preview button in toolbar
- Opens modal with full page preview
- Shows stepper navigation
- Shows all fields
- Interactive (can fill fields)
- Close preview returns to editor

#### US-PB-007: Save Page
**As a** HQ Designer  
**I want to** save my page design  
**So that** it persists in the system

**Acceptance Criteria:**
- Save button in toolbar
- Saves to `page_definitions`, `form_steps`, `form_fields`, `field_bindings` tables
- Shows success message
- Auto-save every 30 seconds (draft)
- Version number increments

#### US-PB-008: Load Existing Page
**As a** HQ Designer  
**I want to** load and edit existing pages  
**So that** I can make changes

**Acceptance Criteria:**
- Load page from feature version
- Steps render correctly
- Fields render correctly
- Bindings load correctly
- Can edit and re-save

#### US-PB-009: Reorder Fields
**As a** HQ Designer  
**I want to** reorder fields within a step  
**So that** I can control field sequence

**Acceptance Criteria:**
- Drag field up/down within step
- Field order updates
- Sort order saves to database

#### US-PB-010: Delete Fields/Steps
**As a** HQ Designer  
**I want to** delete fields and steps  
**So that** I can correct mistakes

**Acceptance Criteria:**
- Select field → press Delete key or click delete icon
- Select step → click delete icon
- Confirmation dialog
- Deleting step also deletes all fields in step

---

## 3. Functional Requirements

### FR-1: Flow Builder Core

#### FR-1.1: Canvas Rendering
- **MUST** use Vue Flow library (or similar React Flow equivalent)
- **MUST** support zoom (25% to 200%)
- **MUST** support pan (drag canvas)
- **MUST** show grid background
- **MUST** support minimap
- **SHOULD** support keyboard shortcuts (Delete, Ctrl+Z, Ctrl+Y)

#### FR-1.2: Node Management
- **MUST** support 9 node types:
  1. Trigger (entry point)
  2. Command (execute domain command)
  3. Decision (branching logic)
  4. Approval (create approval task)
  5. Notification (send SMS/email)
  6. Document (generate document)
  7. GL Action (post journal entry)
  8. Formula (calculate values)
  9. End (terminal node)
- **MUST** allow drag-and-drop from palette
- **MUST** allow node deletion
- **MUST** show node icon and label
- **MUST** support node selection (single and multiple)

#### FR-1.3: Edge Management
- **MUST** support connecting nodes
- **MUST** support edge deletion
- **MUST** support edge labels (conditions)
- **MUST** prevent invalid connections
- **SHOULD** support edge styling (color, thickness)

#### FR-1.4: Node Configuration
- **MUST** show properties panel on node selection
- **MUST** support node-specific configuration:
  - **Trigger:** trigger type, event name
  - **Command:** command class, arguments
  - **Decision:** condition expression
  - **Approval:** role, tier
  - **Notification:** channel, template, recipient
  - **Document:** template key
  - **GL Action:** account codes, amounts
  - **Formula:** formula key, inputs
  - **End:** (no config)
- **MUST** validate configuration
- **MUST** show validation errors

#### FR-1.5: Flow Validation
- **MUST** validate flow structure:
  - Has trigger/start node
  - Has end node
  - All nodes connected (no orphans)
  - All required configs complete
- **MUST** show validation errors in panel
- **MUST** highlight invalid nodes
- **MUST** disable save if validation fails

#### FR-1.6: Flow Persistence
- **MUST** save to existing registry tables:
  - `flow_definitions` (flow metadata)
  - `flow_nodes` (nodes with config and position)
  - `flow_edges` (connections)
- **MUST** support auto-save (every 30s)
- **MUST** support manual save
- **MUST** support load existing flow
- **MUST** preserve node positions

### FR-2: Page Builder Core

#### FR-2.1: Canvas Rendering
- **MUST** show page preview
- **MUST** show component library (left sidebar)
- **MUST** show properties panel (right sidebar)
- **MUST** support responsive preview (desktop, tablet, mobile)
- **SHOULD** support live preview mode

#### FR-2.2: Step Management
- **MUST** support multi-step forms
- **MUST** allow adding steps
- **MUST** allow deleting steps
- **MUST** allow reordering steps (drag-drop)
- **MUST** show stepper navigation in preview

#### FR-2.3: Field Management
- **MUST** support 15+ component types:
  - Text Input
  - Number Input
  - Date Picker
  - Amount Input
  - IC Number Input (domain-specific)
  - Phone Input (domain-specific)
  - Dropdown
  - Radio Group
  - Checkbox
  - Textarea
  - File Upload
  - Gold Item Repeater (domain-specific)
  - Nominee Repeater (domain-specific)
  - Summary Panel (display)
  - Timeline (display)
- **MUST** allow drag-and-drop from library
- **MUST** allow field deletion
- **MUST** allow field reordering within step

#### FR-2.4: Field Configuration
- **MUST** show properties panel on field selection
- **MUST** support field properties:
  - Label
  - Field key (unique)
  - Component type
  - Required (boolean)
  - Placeholder
  - Help text
  - Default value
  - Validation rules
  - Data binding (entity.field)
- **MUST** validate field configuration
- **MUST** show validation errors

#### FR-2.5: Data Binding
- **MUST** support binding to entities:
  - customers
  - facilities
  - facility_items
  - facility_nominees
  - valuations
  - approval_tasks
  - payment_transactions
- **MUST** validate binding (entity exists, field exists)
- **MUST** show unbound fields warning
- **MUST** support binding modes:
  - Direct (model column)
  - Computed (formula)
  - Lookup (reference data)

#### FR-2.6: Page Validation
- **MUST** validate page structure:
  - Has at least one step
  - All fields have bindings
  - All required fields marked
  - No duplicate field keys
- **MUST** show validation errors in panel
- **MUST** highlight invalid fields
- **MUST** disable save if validation fails

#### FR-2.7: Page Persistence
- **MUST** save to existing registry tables:
  - `page_definitions` (page metadata)
  - `form_steps` (steps)
  - `form_fields` (fields with config)
  - `field_bindings` (data bindings)
- **MUST** support auto-save (every 30s)
- **MUST** support manual save
- **MUST** support load existing page
- **MUST** preserve field order

---

## 4. Non-Functional Requirements

### NFR-1: Performance
- **MUST** render canvas with 50+ nodes without lag
- **MUST** save flow/page in < 2 seconds
- **MUST** load flow/page in < 1 second
- **SHOULD** support undo/redo with < 100ms response

### NFR-2: Usability
- **MUST** be intuitive for non-technical users
- **MUST** provide clear error messages
- **MUST** support keyboard shortcuts
- **SHOULD** provide tooltips for all actions
- **SHOULD** provide onboarding tutorial

### NFR-3: Compatibility
- **MUST** work on Chrome, Firefox, Safari, Edge (latest versions)
- **MUST** work on desktop (1920x1080 minimum)
- **SHOULD** work on tablet (1024x768 minimum)
- **MUST NOT** require mobile support (HQ users use desktop)

### NFR-4: Reliability
- **MUST** auto-save to prevent data loss
- **MUST** handle network errors gracefully
- **MUST** validate before save
- **SHOULD** support offline mode (nice-to-have)

### NFR-5: Maintainability
- **MUST** use TypeScript for type safety
- **MUST** follow Vue 3 Composition API patterns
- **MUST** have unit tests for core logic
- **SHOULD** have E2E tests for critical flows

---

## 5. Technical Constraints

### TC-1: Technology Stack
- **MUST** use Vue 3 (already in project)
- **MUST** use Vue Flow library for Flow Builder
- **MUST** use Tailwind CSS for styling
- **MUST** integrate with existing Livewire components
- **MUST NOT** change existing registry table schemas

### TC-2: Integration Points
- **MUST** integrate with `FlowCanvasProxy` Livewire component
- **MUST** integrate with `PageBuilderProxy` Livewire component
- **MUST** use existing API endpoints (or create new ones)
- **MUST** respect existing authentication/authorization

### TC-3: Data Format
- **MUST** save node positions as `position_x`, `position_y` in `flow_nodes`
- **MUST** save node config as JSON in `config` column
- **MUST** save field config as JSON in `config` column
- **MUST** maintain compatibility with existing runtime engine

---

## 6. User Interface Requirements

### UI-1: Flow Builder Layout
```
┌─────────────────────────────────────────────────────────┐
│  [Feature: New Pledge] [Save] [Validate] [Preview]     │
├──────────┬──────────────────────────────────┬──────────┤
│  Node    │                                  │Properties│
│  Palette │         CANVAS                   │  Panel   │
│          │                                  │          │
│ ▸ Entry  │   [Trigger] ──► [Command] ──►   │ Selected:│
│ ▸ Command│              [Decision] ──►      │ Command  │
│ ▸ Decision│             [Approval] ──►     │          │
│ ▸ Approval│            [Document] ──►      │ Config:  │
│ ▸ Notif  │             [End]               │ ...      │
│ ▸ Doc    │                                  │          │
│ ▸ Finance│                                  │ Errors:  │
│ ▸ System │                                  │ None     │
│          │                                  │          │
└──────────┴──────────────────────────────────┴──────────┘
```

### UI-2: Page Builder Layout
```
┌─────────────────────────────────────────────────────────┐
│  [Feature: New Pledge] [Save] [Validate] [Preview]     │
├──────────┬──────────────────────────────────┬──────────┤
│Component │                                  │Properties│
│ Library  │      PAGE PREVIEW                │  Panel   │
│          │                                  │          │
│ ▸ Input  │   ┌──────────────────────┐      │ Selected:│
│ ▸ Domain │   │ Step 1: Customer     │      │ IC Field │
│ ▸ Select │   │ [Full Name]          │      │          │
│ ▸ Display│   │ [IC Number]          │      │ Label:   │
│ ▸ Action │   │ [Phone]              │      │ ...      │
│          │   ├──────────────────────┤      │          │
│          │   │ Step 2: Marhun       │      │ Binding: │
│          │   │ [Gold Items]         │      │ ...      │
│          │   └──────────────────────┘      │          │
│          │                                  │ Errors:  │
│          │                                  │ None     │
└──────────┴──────────────────────────────────┴──────────┘
```

---

## 7. Validation Rules

### VR-1: Flow Validation Rules
1. **Has Trigger:** Flow must have exactly one trigger/start node
2. **Has End:** Flow must have at least one end node
3. **Connected:** All nodes must be reachable from trigger
4. **No Orphans:** No nodes without incoming or outgoing edges (except trigger and end)
5. **Config Complete:** All nodes must have required config fields filled
6. **Valid Commands:** Command nodes must reference existing domain commands
7. **Valid Formulas:** Formula nodes must reference existing formulas
8. **Valid Templates:** Document nodes must reference existing templates

### VR-2: Page Validation Rules
1. **Has Steps:** Page must have at least one step
2. **Has Fields:** Each step must have at least one field
3. **Unique Keys:** All field keys must be unique within page
4. **Has Bindings:** All input fields must have data bindings
5. **Valid Bindings:** Bindings must reference existing entities and fields
6. **Required Marked:** Required fields must be marked as required
7. **Valid Components:** All components must be from allowed list

---

## 8. API Requirements

### API-1: Flow Builder APIs

#### GET /api/studio/flows/{flowId}
**Purpose:** Load existing flow  
**Response:**
```json
{
  "flow": {
    "id": 1,
    "key": "new_pledge_flow",
    "name": "New Pledge Flow",
    "nodes": [
      {
        "id": 1,
        "node_key": "trigger_1",
        "node_type": "trigger",
        "label": "Start",
        "config": {},
        "position_x": 100,
        "position_y": 100
      }
    ],
    "edges": [
      {
        "id": 1,
        "source_node_id": 1,
        "target_node_id": 2,
        "condition_type": "always"
      }
    ]
  }
}
```

#### POST /api/studio/flows/{flowId}/save
**Purpose:** Save flow  
**Request:**
```json
{
  "nodes": [...],
  "edges": [...]
}
```
**Response:**
```json
{
  "success": true,
  "flow_id": 1,
  "version": 2
}
```

#### POST /api/studio/flows/{flowId}/validate
**Purpose:** Validate flow  
**Response:**
```json
{
  "valid": false,
  "errors": [
    {
      "node_id": 3,
      "message": "Command node missing command class"
    }
  ]
}
```

### API-2: Page Builder APIs

#### GET /api/studio/pages/{pageId}
**Purpose:** Load existing page  
**Response:**
```json
{
  "page": {
    "id": 1,
    "key": "new_pledge_form",
    "name": "New Pledge Form",
    "steps": [
      {
        "id": 1,
        "step_key": "customer",
        "title": "Customer Info",
        "fields": [
          {
            "id": 1,
            "field_key": "full_name",
            "label": "Full Name",
            "component_type": "text_input",
            "is_required": true,
            "binding": {
              "target_entity": "customers",
              "target_path": "name"
            }
          }
        ]
      }
    ]
  }
}
```

#### POST /api/studio/pages/{pageId}/save
**Purpose:** Save page  
**Request:**
```json
{
  "steps": [...]
}
```
**Response:**
```json
{
  "success": true,
  "page_id": 1,
  "version": 2
}
```

#### POST /api/studio/pages/{pageId}/validate
**Purpose:** Validate page  
**Response:**
```json
{
  "valid": false,
  "errors": [
    {
      "field_id": 5,
      "message": "Field has no data binding"
    }
  ]
}
```

---

## 9. Dependencies

### External Libraries
- **Vue Flow** (or React Flow if using React) - Flow canvas
- **VueDraggable** - Drag-and-drop for page builder
- **Monaco Editor** (optional) - For expression editing
- **Vuelidate** or **Yup** - Form validation

### Internal Dependencies
- ✅ Registry models (`Feature`, `FlowDefinition`, `PageDefinition`, etc.)
- ✅ Runtime engine (`FlowOrchestrator`, node runners)
- ✅ Livewire proxy components (`FlowCanvasProxy`, `PageBuilderProxy`)
- ❌ API endpoints (need to create)

---

## 10. Out of Scope

The following are **explicitly out of scope** for this feature:

1. ❌ Rule Builder (separate feature)
2. ❌ Formula Builder (separate feature)
3. ❌ Document Template Builder (separate feature)
4. ❌ Publish workflow UI (separate feature)
5. ❌ Impact analysis UI (separate feature)
6. ❌ Simulation UI (separate feature)
7. ❌ Mobile support (HQ users use desktop)
8. ❌ Collaborative editing (multiple users editing same flow)
9. ❌ Version history UI (can be added later)
10. ❌ AI-assisted design (can be added later)

---

## 11. Risks & Mitigation

### Risk 1: Vue Flow Learning Curve
**Probability:** Medium  
**Impact:** High  
**Mitigation:** 
- Allocate 1 week for Vue Flow spike/prototype
- Use official examples as starting point
- Consider hiring Vue Flow expert for consultation

### Risk 2: Complex State Management
**Probability:** High  
**Impact:** Medium  
**Mitigation:**
- Use Pinia for state management
- Keep state simple (nodes, edges, selected)
- Avoid premature optimization

### Risk 3: Performance with Large Flows
**Probability:** Medium  
**Impact:** Medium  
**Mitigation:**
- Implement virtualization for large node lists
- Lazy load node configurations
- Optimize re-renders

### Risk 4: Integration with Livewire
**Probability:** Low  
**Impact:** High  
**Mitigation:**
- Use Livewire as thin proxy layer
- Keep Vue components independent
- Use Alpine.js for simple interactions

---

## 12. Success Metrics

### Quantitative Metrics
- ✅ HQ user can create flow in < 10 minutes (vs. 2 hours with code)
- ✅ HQ user can create page in < 5 minutes (vs. 1 hour with code)
- ✅ 90% of flows created via builder (vs. 0% now)
- ✅ 90% of pages created via builder (vs. 0% now)
- ✅ < 5% of designs fail validation
- ✅ < 1% of saved designs fail to execute in runtime

### Qualitative Metrics
- ✅ HQ users report "easy to use" (survey score > 4/5)
- ✅ HQ users can design without developer help
- ✅ Support tickets for "how to create feature" drop by 80%
- ✅ Time-to-market for new features reduced by 70%

---

## 13. Acceptance Criteria (Feature-Level)

### AC-1: Flow Builder Complete
- [ ] User can create new flow from scratch
- [ ] User can add all 9 node types
- [ ] User can connect nodes with edges
- [ ] User can configure each node type
- [ ] User can validate flow
- [ ] User can save flow
- [ ] User can load existing flow
- [ ] User can delete nodes/edges
- [ ] Saved flow executes correctly in runtime
- [ ] All tests passing

### AC-2: Page Builder Complete
- [ ] User can create new page from scratch
- [ ] User can add steps
- [ ] User can add all 15+ field types
- [ ] User can configure each field
- [ ] User can set data bindings
- [ ] User can validate page
- [ ] User can save page
- [ ] User can load existing page
- [ ] User can delete fields/steps
- [ ] Saved page renders correctly for branch users
- [ ] All tests passing

### AC-3: Integration Complete
- [ ] Flow Builder integrates with FlowCanvasProxy
- [ ] Page Builder integrates with PageBuilderProxy
- [ ] Both builders save to correct registry tables
- [ ] Both builders load from registry tables
- [ ] Both builders respect feature versioning
- [ ] Both builders work with existing runtime engine

---

## 14. Timeline Estimate

### Phase 1: Flow Builder (5-6 weeks)
- Week 1: Vue Flow spike + architecture
- Week 2: Node palette + canvas rendering
- Week 3: Node configuration + validation
- Week 4: Save/load + API integration
- Week 5: Testing + bug fixes
- Week 6: Polish + documentation

### Phase 2: Page Builder (3-4 weeks)
- Week 1: Component library + canvas
- Week 2: Field configuration + bindings
- Week 3: Save/load + API integration
- Week 4: Testing + bug fixes + polish

### Total: 8-10 weeks

---

## 15. Next Steps

1. **Review & Approve Requirements** (this document)
2. **Create Design Document** (architecture, components, data flow)
3. **Create Tasks Document** (detailed implementation tasks)
4. **Begin Implementation** (Flow Builder first, then Page Builder)

---

**Document Status:** Draft  
**Last Updated:** 20 April 2026  
**Author:** Kiro AI  
**Reviewers:** [To be assigned]
