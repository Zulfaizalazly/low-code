# Arrahnumation V3 — Official Blueprint

> **Arrahnu Operating Platform**
> A low-code platform purpose-built for the Arrahnu (Islamic pawn broking) industry, where HQ/clients can design operational flows, build forms and pages, set rules/formulas/approvals/documents, publish as live features/modules, and have them appear in the sidebar for branch users by role — all within strict guardrails for audit, compliance, accounting integrity, permissions, versioning, and rollback.

**Version:** 3.0
**Date:** 19 April 2026
**Status:** Blueprint / Pre-Implementation

---

## Table of Contents

1. [Product Vision & Positioning](#1-product-vision--positioning)
2. [Technology Stack](#2-technology-stack)
3. [System Architecture](#3-system-architecture)
4. [Core Concept: Feature Bundle](#4-core-concept-feature-bundle)
5. [The Four Core Engines](#5-the-four-core-engines)
6. [Data Model & Schema Design](#6-data-model--schema-design)
7. [Admin UX & Builder Experience](#7-admin-ux--builder-experience)
8. [Publish Pipeline & Release Governance](#8-publish-pipeline--release-governance)
9. [Runtime Platform](#9-runtime-platform)
10. [Customisation Boundaries](#10-customisation-boundaries)
11. [QA Sign-Off Criteria](#11-qa-sign-off-criteria)
12. [Worked Example: New Pledge with Nominee Step](#12-worked-example-new-pledge-with-nominee-step)
13. [Appendix: Complete Table Schemas](#13-appendix-complete-table-schemas)

---

## 1. Product Vision & Positioning

### What V3 Is

V3 is **not** "a system with an automation studio". V3 is an **Arrahnu Operating Platform** — a domain-specific low-code platform where HQ/clients can:

- Design operational flows
- Design forms and pages
- Define rules, formulas, approvals, and documents
- Publish as live features/modules
- Have features appear in the sidebar, usable by branch/users according to role

All of this happens within **strict guardrails**: audit, compliance, accounting integrity, permissions, versioning, and rollback.

### Honest Product Promise

> *"Clients can build and publish their own Arrahnu operational modules — complete with flows, forms, pages, rules, approvals, and automations — without modifying core application code, while remaining compliant with operational, audit, and financial guardrails."*

This is a strong USP that does not over-promise.

### What the Final Product Looks Like

When a client enters HQ Studio, they see **5 main workspaces**:

| Workspace | Purpose |
|---|---|
| **Feature Builder** | Create and manage feature bundles |
| **Flow Builder** | Design operational workflows |
| **Page Builder** | Design forms and UI pages |
| **Rule & Formula Studio** | Define business rules and calculations |
| **Release Center** | Publish, version, rollback |

**Client workflow:**

```
Create Feature → Design Flow → Design Page/Form → Bind Data & Rules
→ Set Approval/Permissions/Sidebar → Simulate → Submit Review
→ Publish → Feature appears in sidebar → Branch staff uses it
```

This is a sensible experience that can be sold honestly.

---

## 2. Technology Stack

### Primary Stack Decision

| Layer | Technology | Rationale |
|---|---|---|
| **Backend Framework** | Laravel 13 | Full-featured, mature, first-party ecosystem |
| **Frontend Engine** | Livewire 4 | SPA-feel without SPA complexity; ideal for form-heavy ops apps |
| **UI Component Library** | Flux UI | Official Livewire UI library; premium look out of the box |
| **CSS Framework** | Tailwind CSS 4 | Design system via utility classes |
| **Micro-interactions** | Alpine.js | Lightweight JS for small UI behaviours |
| **Queue Dashboard** | Laravel Horizon | Queue throughput, failures, retries, worker balance |
| **App Monitoring** | Laravel Pulse | Performance, slow queries/jobs/requests |
| **Real-time** | Laravel Reverb | First-party WebSocket server; use only when needed |
| **Performance** | Laravel Octane | Only after traffic and bottlenecks are proven |

### Architecture Split: 80/20 Rule

| Percentage | Scope | Technology |
|---|---|---|
| **80%** | Dashboard, admin studio, forms, approvals, reporting, runtime monitor | Livewire 4 + Flux UI |
| **20%** | Visual builders (Flow Builder canvas, Page Builder drag-drop) | Isolated JS islands (e.g., Vue/React component) |

We do **not** choose React/Vue for the entire product. We only use heavy JS where the UI type genuinely demands it (node-graph editors, drag-drop canvases).

### What We Explicitly Do NOT Use as Default

| Technology | Why Not |
|---|---|
| Inertia + React (full app) | Higher complexity, team skill burden, state management overhead |
| Inertia + Vue (full app) | Still more complex than Livewire for this type of product |
| Filament as core shell | Good for admin CRUD, but our product is a custom platform builder |
| Octane from day one | Adds operational complexity; defer until bottlenecks are proven |
| Volt as default style | Can use selectively, but class-based Livewire is the primary approach |

---

## 3. System Architecture

### Governing Principle

```
Studio Design → Publish → Runtime Execute → Feature Lives
```

**Never:** design looks good but runtime ignores it.

### Five Architectural Layers

```
┌─────────────────────────────────────────────────────────┐
│                    HQ STUDIO (UI)                       │
│  Feature Builder │ Flow Builder │ Page Builder │ Rules   │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────┐
│              REGISTRY & BUILDER LAYER                   │
│  Flow Registry │ Form Registry │ Rule Registry │ etc.   │
└──────────────────────┬──────────────────────────────────┘
                       │
┌──────────────────────▼──────────────────────────────────┐
│                  PUBLISH PIPELINE                       │
│  Validate → Simulate → Impact Analysis → Approve →     │
│  Publish Version → Snapshot                             │
└──────────────┬───────────────────┬──────────────────────┘
               │                   │
┌──────────────▼───────┐ ┌────────▼──────────────────────┐
│   RUNTIME LAYER      │ │     KERNEL (IMMUTABLE)        │
│  Load published ver. │ │  Auth, Roles, Permissions     │
│  Render page/form    │ │  Org/Entity/Branch Scoping    │
│  Accept commands     │ │  Audit Trail                  │
│  Execute domain logic│ │  Transaction Integrity        │
│  Emit events         │ │  Queue Contract               │
│  Run automation flow │ │  Event Bus                    │
│  Trigger side-effects│ │  Versioning & Rollback Gov.   │
│  Log everything      │ │  Compliance Hard-Stop         │
└──────────────────────┘ │  Accounting Safety Rules      │
                         └────────────────────────────────┘
```

### Layer 1: Kernel (Immutable)

The Kernel is the layer that **cannot** be opened to client configuration. It enforces:

- Authentication, roles, and permissions
- Organisation / entity / branch scoping
- Audit trail mechanism
- Transaction integrity (ACID)
- Queue contract enforcement
- Event bus infrastructure
- Versioning and rollback governance
- Compliance hard-stops
- Accounting safety rules (journal balancing, immutable history)

> **QA Rule:** If anything in the Kernel is freely configurable, QA will reject.

### Layer 2: Domain Modules

All operational features live inside clearly defined domain modules, not scattered by screen.

**Minimum modules:**

| Module | Responsibility |
|---|---|
| `Customer` | Customer identity, contacts, KYC |
| `Facility` | Pledge / facility lifecycle |
| `Valuation` | Gold valuation, weight, purity |
| `Vault` | Marhun (collateral) custody |
| `Approval` | Multi-tier approval engine |
| `Payment` | Receive, disburse, allocate |
| `Accounting` | GL, journal entries, balancing |
| `Document` | Template rendering, storage |
| `Notification` | SMS, email, push |
| `Reporting` | Report generation, queries |
| `AutomationRuntime` | Flow execution engine |
| `FeaturePublishing` | Publish pipeline |

**Every module must expose a consistent contract:**

```
Commands   → e.g., ReceivePayment
Events     → e.g., payment.received
Policies   → e.g., PaymentPolicy
Actions    → e.g., allocate_payment, generate_receipt, post_gl
ReadModels → e.g., PaymentSummary
```

### Layer 3: Registry & Builder Layer

This is where **all configurability lives**.

**Registries:**

- Flow Registry
- Form/Page Registry
- Rule Registry
- Formula Registry
- Document Registry
- Menu/Sidebar Registry
- Feature Registry

**Corresponding Builders (UI):**

- Flow Builder
- Page Builder
- Rule Builder
- Formula Builder
- Document Builder
- Feature Builder

> **Critical Rule:** Every builder saves definitions into a **strict schema**. No freeform config storage.

### Layer 4: Publish Pipeline

This layer prevents client deception.

```
Client designs feature → System validates completeness → Simulate
→ Impact analysis → Approval → Publish version
→ Runtime uses ONLY published version
```

**Drafts never go live directly.**

### Layer 5: Runtime Layer

The engine that makes all configuration real.

**Runtime responsibilities:**

1. Load published feature definition
2. Render page/form from published schema
3. Accept user submission as a command
4. Execute domain logic (validate, persist)
5. Emit domain events
6. Execute automation flow (published version)
7. Trigger side-effects: documents, notifications, accounting, tasks
8. Store node-by-node execution log
9. Write complete audit trail

> This layer is what separates a real platform from a demo studio.

### Command & Event Pattern

All significant write actions must go through commands:

```
CreateFacilityApplication → facility.created
SubmitForApproval         → facility.submitted
ApproveFacility           → facility.approved
ReceivePayment            → payment.received
```

The automation runtime **only listens to standard domain events**. This ensures builders, runtime, and domain all speak the same language.

### Recommended Folder Structure

```
app/
├── Kernel/
│   ├── Auth/
│   ├── Permissions/
│   ├── Audit/
│   ├── Scoping/
│   └── Compliance/
├── Domain/
│   ├── Customer/
│   ├── Facility/
│   ├── Valuation/
│   ├── Vault/
│   ├── Approval/
│   ├── Payment/
│   ├── Accounting/
│   ├── Document/
│   ├── Notification/
│   └── Reporting/
├── Runtime/
│   ├── Automation/
│   └── FeatureRegistry/
└── Studio/
    ├── Flows/
    ├── Pages/
    ├── Rules/
    └── Publishing/
```

**Purpose:** Everyone knows where domain logic lives, where studio config lives, and where runtime execution lives.

---

## 4. Core Concept: Feature Bundle

### The Central Unit

In V3, the primary unit is not a page, not a scenario. It is a **Feature Bundle**.

Every Feature Bundle **must** contain:

| Component | Description |
|---|---|
| **Feature Info** | Name, domain, icon, description, scope |
| **Flow** | Workflow definition (triggers, steps, decisions, approvals) |
| **UI / Page** | Frontend page/form schema |
| **Data Model Binding** | Which entities/tables the feature reads/writes |
| **Validation Rules** | Field-level and business-level rules |
| **Permissions** | Who can launch, view, approve, audit |
| **Sidebar / Menu** | Where and how the feature appears in the app |
| **Outputs** | Documents, notifications, GL entries, audit tags |
| **Version + Publish State** | Draft → Published → Archived lifecycle |

### Example: New Pledge Bundle

| Component | Content |
|---|---|
| Flow | Intake → Valuation → Approval → Disbursement |
| Page | Branch staff form for customer + marhun data |
| Fields | Customer info, gold item details |
| Validation | IC format, gold weight, LTV cap |
| Roles | Branch Staff / Manager / Approver |
| Menu | Sidebar item: "New Pledge" |
| Outputs | Agreement doc, SMS notification, GL entries, audit trail |

**When the bundle is complete and passes validation, it can be Published.** Only then does it become a live feature.

---

## 5. The Four Core Engines

### Engine A: Flow Builder

**Purpose:** Design operational processes.

**Capabilities:**

- Triggers (manual entry, domain event, schedule)
- Steps and sequential tasks
- Decision branching
- Required actions
- Escalation rules
- Notification triggers
- Document generation
- Accounting actions (GL)
- External integrations (API/webhook)

**Node Types:**

| Node Type | Description |
|---|---|
| `trigger` | Entry point for the flow |
| `form_submit` | Capture user input |
| `decision_table` | Conditional branching |
| `formula` | Calculate values (LTV, ujrah, etc.) |
| `task_assignment` | Assign work to a user/role |
| `approval` | Require sign-off |
| `document` | Generate documents |
| `notification` | Send SMS/email/push |
| `payment_action` | Process payment |
| `gl_action` | Create journal entries |
| `api_call` | External webhook/API |
| `delay_timer` | Wait for duration/date |
| `exception_handling` | Error/failure path |
| `end` | Terminal node |

### Engine B: Page / Form Builder

**Purpose:** Design frontend pages.

**Not** free HTML. **Not** a wild page builder. It uses **domain-safe components only**.

**Available Components:**

| Category | Components |
|---|---|
| **Layout** | Section, Tabs, Card |
| **Input (General)** | Text Field, Date Field, Amount Field |
| **Input (Domain)** | IC Field, Phone Field |
| **Repeaters** | Gold Item Repeater, Nominee Repeater |
| **Display** | Collateral Summary, Valuation Panel, Status Timeline |
| **Action** | Approval Panel, Payment Table, Task Checklist |
| **Document** | Document Viewer |

**Every component can bind to:**

- Entity field (e.g., `customer.ic_number`)
- Workflow variable (e.g., `flow.current_step`)
- Computed formula (e.g., `calc.ltv_ratio`)
- Lookup data (e.g., `lookup.branch_list`)

### Engine C: Rule & Formula Builder

**Purpose:** Define business logic without code.

**Rule types:**

| Rule | Example |
|---|---|
| LTV calculation | Max 70% for standard, 65% for high-risk |
| Profit/ujrah calculation | Based on tenure and amount |
| Approval tier | Amount-based routing |
| Branch-specific policy | Johor requires nominee, KL does not |
| Overdue logic | Grace period, penalty triggers |
| Auction eligibility | Criteria for defaulted items |
| Refund rule | Surplus calculation after auction |

**Two modes:**

1. **Business Mode** — Decision-table style (if/then)
2. **Advanced Mode** — Expression-based for power users

### Engine D: Feature Publisher

**Purpose:** Bundle everything and make it live.

**When flow and UI are ready, client must set:**

| Setting | Description |
|---|---|
| Feature name | Display name |
| Icon | Visual identifier |
| Sidebar location | Menu placement |
| Visibility | Which roles can see it |
| Default landing page | Entry page |
| Required dependencies | Other features needed |
| Publish scope | Org-wide, branch-specific, product-specific |

**After publish, the feature appears in the sidebar for eligible users.**

---

## 6. Data Model & Schema Design

### Four Data Worlds

V3 has four distinct but interconnected data domains:

| Domain | Purpose | Example Tables |
|---|---|---|
| **Operational Data** | Live business data | `customers`, `facilities`, `payments` |
| **Registry Data** | Configuration/design data | `features`, `flow_definitions`, `form_fields` |
| **Publish & Versioning Data** | Release governance | `feature_versions`, `release_batches` |
| **Runtime & Audit Data** | Execution logs, audit | `audit_trails`, `automation_execution_logs` |

### 6.1 Operational Data Tables

These are **real business data** — not config. This is what branches and customers interact with.

#### `customers`
```
id, name, ic_number, phone, email, address, date_of_birth,
customer_type, status, created_at, updated_at
```

#### `customer_contacts`
```
id, customer_id, contact_type, value, is_primary,
created_at, updated_at
```

#### `facilities`
```
id, customer_id, product_code, branch_id, entity_id,
facility_number, principal_amount, tenure_months,
profit_rate, status, approved_at, disbursed_at,
matured_at, created_at, updated_at
```

#### `facility_items` (marhun/collateral)
```
id, facility_id, item_type, description, weight_grams,
purity, valuation_amount, status, vault_location,
created_at, updated_at
```

#### `facility_nominees` (pewaris)
```
id, facility_id, name, ic_number, relationship, phone,
address, is_primary, created_at, updated_at
```

> **QA Decision:** For critical business entities like nominees, **always use structured domain tables**, not JSON blobs. JSON makes reporting, compliance, and document binding unreliable.

#### `valuations`
```
id, facility_id, facility_item_id, gold_price_per_gram,
weight_grams, purity_percentage, gross_value, ltv_percentage,
valuation_amount, valued_by, valued_at, created_at, updated_at
```

#### `approval_tasks`
```
id, approvable_type, approvable_id, approval_tier,
assigned_to, assigned_role, status, decision, remarks,
decided_at, created_at, updated_at
```

#### `payment_transactions`
```
id, facility_id, payment_type, amount, payment_method,
reference_number, received_by, branch_id, status,
paid_at, created_at, updated_at
```

#### `journal_entries`
```
id, entry_number, reference_type, reference_id, description,
posted_by, posted_at, is_balanced, created_at, updated_at
```

#### `journal_entry_lines`
```
id, journal_entry_id, account_code, account_name,
debit_amount, credit_amount, description, created_at
```

#### `documents`
```
id, documentable_type, documentable_id, document_type,
template_id, template_version, file_path, file_name,
generated_by, generated_at, created_at, updated_at
```

#### `notification_logs`
```
id, notifiable_type, notifiable_id, channel, recipient,
subject, body, status, sent_at, failed_reason,
created_at, updated_at
```

---

### 6.2 Registry Data Tables (Configuration / Design)

This is the **heart of configurability**. These tables store what clients design in the builders.

#### `features`
The primary identity of every Feature Bundle.

```
id              BIGINT PRIMARY KEY
key             VARCHAR UNIQUE        -- e.g., 'new_pledge'
name            VARCHAR               -- e.g., 'New Pledge'
description     TEXT
domain          VARCHAR               -- e.g., 'facility'
icon            VARCHAR               -- e.g., 'shield-check'
status          ENUM('draft','published','archived')
default_route_key VARCHAR             -- e.g., 'new-pledge'
scope_level     ENUM('platform','entity','branch')
created_by      BIGINT FK
updated_by      BIGINT FK
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

#### `feature_versions`
Every feature **must** be versioned. Runtime only reads **published** versions.

```
id                      BIGINT PRIMARY KEY
feature_id              BIGINT FK → features
version_no              INTEGER
status                  ENUM('draft','in_review','approved',
                             'published','archived','rolled_back')
checksum                VARCHAR           -- integrity check
change_summary          TEXT
published_at            TIMESTAMP
published_by            BIGINT FK
approved_at             TIMESTAMP
approved_by             BIGINT FK
rollback_from_version_id BIGINT FK NULLABLE
created_at              TIMESTAMP
updated_at              TIMESTAMP
```

#### `flow_definitions`
One feature can have one or more flows.

```
id                  BIGINT PRIMARY KEY
feature_version_id  BIGINT FK → feature_versions
key                 VARCHAR           -- e.g., 'pledge_intake_flow'
name                VARCHAR
trigger_type        ENUM('manual_entry','domain_event',
                         'schedule','api_trigger')
trigger_config      JSON              -- e.g., {"event":"facility.created"}
entry_mode          ENUM('user_launch','auto','event_driven')
is_primary          BOOLEAN
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### `flow_nodes`
The visual and executable nodes in a flow.

```
id                  BIGINT PRIMARY KEY
flow_definition_id  BIGINT FK → flow_definitions
node_key            VARCHAR           -- e.g., 'validate_ltv'
node_type           ENUM('trigger','step','decision','approval',
                         'notification','document','formula',
                         'gl_action','api_call','delay_timer',
                         'exception_handling','end')
label               VARCHAR
config              JSON              -- node-specific configuration
position_x          INTEGER           -- canvas X position
position_y          INTEGER           -- canvas Y position
sort_order          INTEGER
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### `flow_edges`
Connections between nodes.

```
id                  BIGINT PRIMARY KEY
flow_definition_id  BIGINT FK → flow_definitions
source_node_id      BIGINT FK → flow_nodes
target_node_id      BIGINT FK → flow_nodes
condition_type      ENUM('always','expression','outcome')
condition_config    JSON              -- e.g., {"expression":"ltv <= 0.7"}
priority            INTEGER
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### `page_definitions`
Frontend page schemas designed in the Page Builder.

```
id                  BIGINT PRIMARY KEY
feature_version_id  BIGINT FK → feature_versions
key                 VARCHAR           -- e.g., 'new-pledge-form'
name                VARCHAR
page_type           ENUM('workflow_form','dashboard','detail_view',
                         'listing','approval_view')
layout_type         ENUM('single_column','two_column','stepper',
                         'tabbed')
route_key           VARCHAR           -- e.g., 'new-pledge'
is_entry_page       BOOLEAN
config              JSON
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### `page_sections`
Pages are divided into sections — never raw HTML.

```
id                  BIGINT PRIMARY KEY
page_definition_id  BIGINT FK → page_definitions
section_key         VARCHAR
title               VARCHAR
component_type      ENUM('hero_header','form_stepper',
                         'summary_panel','document_panel',
                         'approval_sidebar','data_table',
                         'timeline','alert_banner')
layout_span         ENUM('full','half','third')
sort_order          INTEGER
config              JSON
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### `form_steps`
Steps within a form workflow — **this is how clients add steps like Nominee**.

```
id                  BIGINT PRIMARY KEY
page_definition_id  BIGINT FK → page_definitions
step_key            VARCHAR           -- e.g., 'pewaris' (nominee)
title               VARCHAR           -- e.g., 'Nominee Details'
description         TEXT
entity_binding      VARCHAR           -- e.g., 'facility_nominees'
is_required         BOOLEAN
visibility_rule_id  BIGINT FK NULLABLE → rule_sets
sort_order          INTEGER
config              JSON
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### `form_fields`
Fields within each form step.

```
id                  BIGINT PRIMARY KEY
form_step_id        BIGINT FK → form_steps
field_key           VARCHAR           -- e.g., 'ic_number'
label               VARCHAR           -- e.g., 'IC Number'
component_type      ENUM('text_input','ic_input','phone_input',
                         'textarea','select','date_picker',
                         'amount_input','repeater','checkbox',
                         'radio','file_upload')
data_type           ENUM('string','integer','decimal','date',
                         'boolean','json')
is_required         BOOLEAN
default_value       VARCHAR NULLABLE
placeholder         VARCHAR NULLABLE
help_text           TEXT NULLABLE
sort_order          INTEGER
config              JSON              -- component-specific config
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### `field_bindings`
**Critical table** — prevents config from being cosmetic.

```
id                  BIGINT PRIMARY KEY
form_field_id       BIGINT FK → form_fields
binding_type        ENUM('model_column','computed_variable',
                         'workflow_variable','lookup','constant')
target_entity       VARCHAR           -- e.g., 'facility_nominees'
target_path         VARCHAR           -- e.g., 'ic_number'
write_mode          ENUM('create','update','upsert')
read_mode           ENUM('direct','computed','lookup')
transformer_key     VARCHAR NULLABLE  -- e.g., 'uppercase_ic'
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

> This table ensures that **every field has a real storage contract** — not just a pretty UI box.

#### `rule_sets`
Validation and business logic rules.

```
id                  BIGINT PRIMARY KEY
feature_version_id  BIGINT FK → feature_versions
key                 VARCHAR           -- e.g., 'nominee_required_rule'
name                VARCHAR
rule_type           ENUM('validation','visibility','workflow_guard',
                         'approval_routing','document_requirement')
scope_type          ENUM('feature','step','field','entity')
target_type         VARCHAR           -- what this rule applies to
config              JSON
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### `rule_rows`
Decision-table style rule entries.

```
id                  BIGINT PRIMARY KEY
rule_set_id         BIGINT FK → rule_sets
priority            INTEGER
conditions          JSON              -- e.g., {"product_code":"GOLD_PLUS"}
result              JSON              -- e.g., {"require_nominee":true}
is_active           BOOLEAN
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### `formula_definitions`
Calculation definitions.

```
id                  BIGINT PRIMARY KEY
feature_version_id  BIGINT FK → feature_versions
key                 VARCHAR           -- e.g., 'ltv_ratio'
name                VARCHAR
expression          TEXT              -- e.g., 'valuation_amount * ltv_percentage'
input_schema        JSON              -- expected inputs
output_schema       JSON              -- expected outputs
rounding_policy     ENUM('none','round_half_up','round_down','ceil')
config              JSON
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### `document_templates`
Document templates bound to a feature version.

```
id                  BIGINT PRIMARY KEY
feature_version_id  BIGINT FK → feature_versions
key                 VARCHAR           -- e.g., 'pledge_agreement'
name                VARCHAR
template_type       ENUM('contract','receipt','letter','report',
                         'notification_template')
content_schema      JSON              -- template structure
render_engine       ENUM('blade','pdf_generator','docx')
bindings            JSON              -- data bindings for template vars
is_active           BOOLEAN
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### `feature_permissions`
Who can use a published feature.

```
id                  BIGINT PRIMARY KEY
feature_version_id  BIGINT FK → feature_versions
role_key            VARCHAR           -- e.g., 'branch_staff'
permission_key      VARCHAR           -- e.g., 'create'
access_mode         ENUM('full','read_only','conditional')
config              JSON
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

#### `feature_menu_items`
Sidebar/menu registration for published features.

```
id                  BIGINT PRIMARY KEY
feature_version_id  BIGINT FK → feature_versions
menu_key            VARCHAR           -- e.g., 'sidebar_new_pledge'
label               VARCHAR           -- e.g., 'New Pledge'
icon                VARCHAR
parent_menu_key     VARCHAR NULLABLE  -- for nested menus
route_key           VARCHAR           -- e.g., 'new-pledge'
sort_order          INTEGER
visibility_rule_id  BIGINT FK NULLABLE → rule_sets
is_enabled          BOOLEAN
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

> When a feature is published, sidebar items come from **this table**. Features designed by the client genuinely appear in the sidebar.

---

### 6.3 Publish & Versioning Data Tables

#### `release_batches`
```
id, batch_number, title, description, status, created_by,
approved_by, published_at, created_at, updated_at
```

#### `release_items`
```
id, release_batch_id, feature_version_id, change_type,
change_summary, created_at
```

#### `publish_validations`
```
id, feature_version_id, check_type, check_key, status,
message, validated_at
```

#### `impact_analysis_reports`
```
id, feature_version_id, affected_branches, affected_roles,
affected_documents, affected_reports, risk_level,
summary, generated_at
```

#### `rollback_logs`
```
id, feature_version_id, rolled_back_from_version, reason,
rolled_back_by, rolled_back_at
```

---

### 6.4 Runtime & Audit Data Tables

These prove the system is **actually alive**.

#### `event_logs`
```
id, event_type, event_payload, source_type, source_id,
emitted_at, created_at
```

#### `automation_execution_logs`
```
id, flow_definition_id, feature_version_id, trigger_type,
trigger_source, status, started_at, completed_at,
error_message, created_at
```

#### `automation_node_logs`
```
id, execution_log_id, flow_node_id, node_key, node_type,
input_data, output_data, status, started_at, completed_at,
error_message, created_at
```

#### `command_logs`
```
id, command_type, command_payload, user_id, status,
executed_at, created_at
```

#### `ui_submission_logs`
```
id, page_definition_id, page_version, form_data,
submitted_by, submitted_at, created_at
```

#### `audit_trails`
```
id, auditable_type, auditable_id, action, old_values,
new_values, user_id, ip_address, user_agent,
performed_at, created_at
```

#### `dead_letter_jobs`
```
id, queue, payload, exception, failed_at, created_at
```

---

### 6.5 Scope Override Model

Clients may need differences by branch, product, or entity.

#### `scoped_overrides`
```
id                  BIGINT PRIMARY KEY
feature_version_id  BIGINT FK → feature_versions
scope_type          ENUM('branch','product','entity','region')
scope_id            VARCHAR           -- e.g., branch_id or product_code
target_table        VARCHAR           -- which registry table to override
target_key          VARCHAR           -- which record key
override_value      JSON              -- the override
effective_from      DATE
effective_to        DATE NULLABLE
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

**Examples:**
- Branch Johor: nominee step is mandatory
- Branch KL: nominee step is optional
- Product A: show nominee step
- Product B: hide nominee step

This gives **real customisation without forking code**.

---

## 7. Admin UX & Builder Experience

### Guiding UX Principles

| Principle | Description |
|---|---|
| **Simple for operator** | Branch staff never sees builder complexity |
| **Powerful for HQ** | Full configuration capability |
| **Guarded for QA** | Guardrails are visible, not hidden |
| **Predictable for publish** | Clear progression from draft to live |

The client should feel they are **building a feature**, not fighting an ERP.

### Two Separate Applications

| Application | Users | Purpose |
|---|---|---|
| **HQ Studio** | HQ admins, designers, reviewers | Configure, design, simulate, publish |
| **Operations App** | Branch staff, managers | Use published features |

> **Critical:** These must be separate. If branch users see builder config, the system becomes chaotic. If HQ designs in the same space as live operations, publish risks increase.

### HQ Studio Sidebar Structure

```
📊 Control Tower
📦 Features
🛠️ Builders
   ├── Flow Builder
   ├── Page Builder
   ├── Rule Builder
   ├── Formula Builder
   ├── Document Builder
   └── Menu Builder
⚡ Runtime Monitor
🚀 Release Center
🔒 Governance
```

#### Control Tower
- Draft features overview
- Pending reviews
- Failed automations
- Latest publishes
- Config health check
- Branch overrides summary

#### Features
- List of all Feature Bundles (New Pledge, Redemption, Extension, etc.)
- Each feature shows completion status

#### Builders
- Entry to each specialised builder workspace

#### Runtime Monitor
- Live event stream
- Node failures
- Queue state
- Latest submissions
- Document generation status
- Notification delivery status

#### Release Center
- Draft releases
- Pending reviews
- Published releases
- Rollback history
- Warnings and risks

#### Governance
- Roles and permissions management
- Scope overrides
- Audit views
- Compliance locks

### Feature-First UX Philosophy

Users should **never** enter a blank builder and wonder what to do. They always start from:

**Create Feature →** Wizard:

1. Feature name
2. Domain category
3. Target users
4. Feature type
5. Entry mode
6. Expected outputs

**Example:**

```
Name:       New Pledge
Domain:     Facility
Users:      Branch Staff
Type:       Form Workflow
Entry:      Sidebar Launch
Outputs:    Approval + Document + SMS + GL
```

The system creates a **Feature Bundle shell**, then the user enters the builders.

### Feature Workspace Progress Bar

Every feature has a workspace with a visible progress tracker:

```
[Info] → [Flow] → [Page] → [Rules] → [Permissions] → [Menu] → [Outputs] → [Simulation] → [Publish]
  ✅       ✅       🔶       ❌          ❌             ❌        ❌           ❌             🔒
```

If a step is incomplete, the system clearly shows: **"Feature Incomplete"**. Publish remains locked.

### Flow Builder UX

**Layout:**
```
┌──────────┬──────────────────────────────────┬──────────────┐
│  Node    │                                  │  Properties  │
│  Palette │        CANVAS                    │  & Validation│
│          │                                  │              │
│ ▸ Entry  │   [Trigger] ──► [Form] ──►       │  Node Config │
│ ▸ Form   │               [Decision] ──►     │  Bindings    │
│ ▸ Decision│              [Approval] ──►     │  Rules       │
│ ▸ Approval│             [Document] ──►      │  Warnings    │
│ ▸ Notif  │              [End]               │              │
│ ▸ Doc    │                                  │              │
│ ▸ Finance│                                  │              │
│ ▸ Task   │                                  │              │
│ ▸ System │                                  │              │
└──────────┴──────────────────────────────────┴──────────────┘
```

**Node palette categories (domain-aware):**

| Category | Example Nodes |
|---|---|
| Entry | Start Feature |
| Form | Submit Form, Validate Input |
| Decision | Check LTV, Require Nominee |
| Approval | Create Approval, Escalate |
| Notification | Send SMS, Send Email |
| Document | Generate Agreement, Generate Receipt |
| Financial | Create GL Entry, Process Payment |
| Task | Assign Task, Wait for Task |
| Integration | API Call, Webhook |
| System | Delay, Exception, End |

**UX Rules:**
- Incomplete nodes must show a **warning colour**
- Invalid connections are **immediately flagged**
- Publish is **disabled** if the flow has errors
- Node config uses **business language**, not technical jargon

### Page Builder UX

**Layout:**
```
┌──────────────┬────────────────────────┬──────────────┐
│  Component   │                        │  Properties  │
│  Library     │    PAGE PREVIEW        │  & Bindings  │
│              │                        │              │
│ ▸ Layout     │   ┌──────────────┐     │  Selected:   │
│ ▸ Input      │   │ Page Header  │     │  IC Field    │
│ ▸ Domain     │   ├──────────────┤     │              │
│ ▸ Display    │   │ Step: Customer│     │  Label: __   │
│ ▸ Action     │   │ Step: Marhun │     │  Required: ☑ │
│ ▸ Document   │   │ Step: Nominee│     │  Bind to: __ │
│              │   │ Step: Summary│     │  Rule: __    │
│              │   └──────────────┘     │              │
└──────────────┴────────────────────────┴──────────────┘
```

**Component library (domain-safe):**

| Category | Components |
|---|---|
| Layout | Page Header, Stepper, Form Section |
| Input | Full Name, IC Number, Phone, Address, Amount, Date |
| Domain | Gold Item Repeater, Nominee Repeater |
| Display | Collateral Summary, Valuation Panel, Status Timeline |
| Action | Approval Box, Payment Table, Task Checklist, Declaration Checkbox |
| Document | Document Panel, Alert Banner, Action Footer |

> **Why domain-safe components?** So that data binding stays consistent, validation stays manageable, reporting stays meaningful, and support doesn't become a nightmare.

### Adding a New Step (e.g., Nominee) — UX Flow

1. Click **"Add Step"** in Page Builder
2. Select **"Nominee / Pewaris"**
3. System prompts:
   - Bind to which entity?
   - Single or multiple nominees?
   - Required or optional?
   - For which products/branches?
4. System generates step template with default fields:
   - Name, IC, Relationship, Phone, Address
5. In the properties panel, client can customise:
   - Labels, field order, required rules, repeatable count, visibility conditions
6. In Flow Builder, client adds corresponding nodes:
   - "Validate Nominee" → "Require Nominee before Approval"

### Rule Builder UX

**Business Mode** (default):
```
┌─────────────────────────────────────────────────────┐
│  IF   product_code  =  GOLD_PLUS                    │
│  AND  branch        =  JOHOR                        │
│  THEN nominee_required = true                       │
└─────────────────────────────────────────────────────┘
```

**Advanced Mode** (power users):
```
Expression: product.code == 'GOLD_PLUS' && branch.state == 'JOHOR'
Result:     { nominee_required: true, min_nominees: 1 }
```

### Menu Builder UX

| Setting | Example |
|---|---|
| Label | New Pledge |
| Icon | `shield-plus` |
| Parent | Pledges |
| Visible for | branch_staff, branch_manager |
| Condition | active_branch = true |

After publish, this menu item **auto-registers** in the sidebar.

### Permissions UX

Permissions are a **mandatory step** in the feature workspace, not an afterthought.

Client must set:

| Action | Who |
|---|---|
| Launch feature | branch_staff |
| View submission | branch_staff, branch_manager |
| Approve | branch_manager, regional_manager |
| Edit draft | branch_staff |
| Cancel | branch_manager |
| Audit | auditor |

> **If permissions are not set, publish is blocked.**

### Output Builder

Each feature clearly defines its operational results:

| Output | Configuration |
|---|---|
| SMS | Template, recipient, trigger condition |
| Document | Which template, data bindings |
| GL Action | Account codes, debit/credit mapping |
| Task | Assignment, due date rule |
| Audit Tags | Tags for compliance filtering |

### Studio User Roles

| Role | Capabilities |
|---|---|
| **Platform Owner** | Publish and rollback authority |
| **Business Designer** | Design features, flows, pages, rules |
| **Compliance Reviewer** | Review fields, docs, declarations, approval rules |
| **Finance Reviewer** | Review formulas, GL, payment logic |
| **QA Reviewer** | Run simulations, sign-off validation |
| **Branch Admin** | View branch-specific variants only |

---

## 8. Publish Pipeline & Release Governance

### Publish Flow

```
Draft → Validate → Simulate → Impact Review → Submit for Approval
→ Approve → Publish → Monitor → Rollback (if needed)
```

Every stage has **mandatory requirements**.

### Publish Gate Checklist

Before any feature can be published, the system **must verify all of the following**:

| # | Check | Description |
|---|---|---|
| 1 | Valid trigger/entry point | Flow has a valid start |
| 2 | Usable page/form exists | At least one renderable page |
| 3 | All required fields complete | No empty mandatory fields |
| 4 | All bindings valid | Every field has a storage contract |
| 5 | All rules valid | No broken rule references |
| 6 | All role access set | Permissions are defined |
| 7 | Sidebar registration complete | Menu item configured |
| 8 | Success path exists | Flow has at least one happy path |
| 9 | Failure path exists | Flow handles errors |
| 10 | Audit requirements met | Audit tags and trail configured |
| 11 | Dependencies exist | Referenced features/modules available |
| 12 | Simulation passed | At least one successful test run |
| 13 | Impact analysis passed | No unacceptable risks |
| 14 | Version snapshot saved | Immutable record created |

> **If even one check fails: publish is blocked.** That's honest.

### Impact Analysis

Before publish, the admin sees:

- Which branches are affected
- Which roles will see the new feature
- Which documents change
- Which approval flows change
- Which reports might be affected
- Whether existing submissions are impacted

This prevents "small publish, big effect" surprises.

### Release Center

| View | Content |
|---|---|
| Draft Releases | Work in progress |
| Pending Reviews | Awaiting approval |
| Published Releases | Live versions |
| Rollback History | Reverted versions with reasons |
| Warnings | Active risks and issues |

Each release shows: what changed, which features, who created it, who approved it, simulation results, and risks.

> **Without a Release Center, all builders look like playgrounds.**

---

## 9. Runtime Platform

### What Happens When a User Uses a Published Feature

```
1. UI renders from published page schema
2. User submits → action enters as a COMMAND
3. Domain service validates and persists data
4. Domain EVENT is emitted
5. Automation runtime executes published flow
6. Action executors perform real work:
   - Generate documents
   - Send notifications
   - Create GL entries
   - Assign tasks
7. All side-effects are recorded
8. Complete audit trail is saved
```

**This means:**
- The UI is real, not decorative
- The flow is executed, not just drawn
- The publish is operational, not cosmetic
- The sidebar feature is alive, not a placeholder

### Runtime Monitor

After publish, clients can monitor:

| Metric | Detail |
|---|---|
| Feature usage count | How many times launched |
| Latest submissions | Recent user submissions |
| Failed runs | Errors and exceptions |
| Failed nodes | Specific node failures |
| Average completion time | Performance metric |
| Pending approvals | Outstanding approval tasks |
| Generated documents | Document output count |
| Notification delivery | SMS/email delivery status |

**Click any single run to see:**
- Feature version used
- Flow version used
- Page version used
- User who triggered it
- Input data summary
- **Node-by-node execution trace**
- All outputs created

---

## 10. Customisation Boundaries

### What Clients CAN Customise (100%)

| Area | Examples |
|---|---|
| Flow/process | Steps, sequence, branching |
| Forms and pages | Layout, fields, sections |
| Field arrangement | Order, grouping, visibility |
| Required fields | Which fields are mandatory |
| Validation rules | IC format, amount ranges |
| Formulas | LTV, ujrah, tenure charges |
| Approval paths | Tiers, routing, escalation |
| Notifications | Templates, triggers, channels |
| Documents | Templates, data bindings |
| Task routing | Assignment rules |
| Dashboard widgets | Display panels |
| Sidebar/menu | Structure, order, visibility |
| Product-specific variants | Different rules per product |
| Branch/entity overrides | Location-specific behaviour |

### What Clients CANNOT Customise (Locked for Production Safety)

| Area | Why Locked |
|---|---|
| Audit trail mechanism | Compliance requirement |
| Permission engine core | Security integrity |
| Transaction integrity | ACID guarantees |
| Journal balancing | Accounting accuracy |
| Immutable history | Legal requirement |
| Compliance hard-stops | Regulatory enforcement |
| Publish governance | Prevents premature deployment |
| Rollback system | Safety net integrity |
| Secrets/integration credentials | Security |
| Tenant/org scoping rules | Data isolation |

> **If everything is open, QA will reject.** These boundaries are what make the platform trustworthy.

---

## 11. QA Sign-Off Criteria

### Mandatory Criteria for V3 Sign-Off

V3 will **only** be signed off when **all** of the following are verified:

| # | Criterion | Verification |
|---|---|---|
| 1 | Flows designed in studio **execute in runtime** | End-to-end test |
| 2 | Pages designed in builder **render for end users** | UI verification |
| 3 | Published features **appear in sidebar by role** | Role-based access test |
| 4 | All primary outputs work: **doc, notification, GL, audit** | Output verification |
| 5 | All changes have **versioning and rollback** | Version history test |
| 6 | All overrides follow **scope with clear precedence** | Override cascade test |
| 7 | All flows can be **simulated before publish** | Simulation test |
| 8 | All runs can be **traced node-by-node** | Trace log verification |
| 9 | No major action runs **directly from UI without runtime contract** | Command pattern audit |
| 10 | No config "appears to exist" but **isn't actually used** | Config-to-runtime audit |

> **If any designer/config does not affect runtime, QA will label it: `FAKE CONFIGURABILITY`**

### Anti-Patterns That Will Be Rejected

| Anti-Pattern | Why Rejected |
|---|---|
| Step added but no entity binding | Data goes nowhere |
| Field created but no storage contract | Cosmetic only |
| Feature published but no versioning | No rollback safety |
| Sidebar item exists but not linked to published feature | Orphaned menu |
| Runtime uses different config than studio | Config drift |
| Critical business data hidden in unstructured JSON | Reporting/compliance failure |

---

## 12. Worked Example: New Pledge with Nominee Step

### Scenario

Client wants to add a "Nominee (Pewaris)" step to their New Pledge feature.

### Step-by-Step Process

#### 1. Enter Feature Workspace
Client opens the **New Pledge** Feature Bundle in HQ Studio.

#### 2. Add Step in Page Builder
- Click **"Add Step"**
- Select **"Nominee / Pewaris"**
- Configure:
  - Entity binding: `facility_nominees`
  - Mode: Multiple nominees (repeater)
  - Required: Yes (for this product)
  - Products: Arrahnu Waris only

#### 3. Configure Fields
System generates default fields:
- Full Name (`text_input` → `facility_nominees.name`)
- IC Number (`ic_input` → `facility_nominees.ic_number`)
- Relationship (`select` → `facility_nominees.relationship`)
- Phone (`phone_input` → `facility_nominees.phone`)
- Address (`textarea` → `facility_nominees.address`)
- Primary Nominee (`checkbox` → `facility_nominees.is_primary`)

#### 4. Set Rules
- IC format validation (12 digits, valid format)
- Minimum 1 nominee for Arrahnu Waris product
- Maximum 3 nominees per facility
- At least one must be marked as primary

#### 5. Update Flow
In Flow Builder, add nodes:
- **Validate Nominee** (after Marhun step)
- **Require Nominee before Approval** (guard node)

#### 6. Update Documents
Bind nominee data to agreement template:
- `facility_nominees[0].name`
- `facility_nominees[0].ic_number`
- `facility_nominees[0].relationship`

#### 7. Set Scope Override (Optional)
- Branch Johor: nominee step mandatory
- Branch KL: nominee step optional

#### 8. Simulate
- Run test with sample data
- Verify nominee validation fires
- Verify approval flow includes nominee check
- Verify document renders nominee data
- Verify SMS preview includes nominee reference

#### 9. Publish
Feature passes all publish gate checks → New version published.

### Result After Publish

| What Happens | Verification |
|---|---|
| Branch user sees Nominee step in New Pledge form | ✅ UI renders |
| Data saves to `facility_nominees` table | ✅ Storage works |
| Approval flow checks nominee presence | ✅ Flow executes |
| Agreement document includes nominee details | ✅ Doc generates |
| Audit trail records nominee data entry | ✅ Audit complete |

> **This is what "real custom" means.** End-to-end, design-to-execution, traceable and audited.

### Three Levels of Customisation

| Level | Description | Who Can Do It |
|---|---|---|
| **Level 1** | Add fields/steps using existing components | Business Designer |
| **Level 2** | Add business rules, make step required by product/branch | Business Designer |
| **Level 3** | Add new entity/data model or domain logic | Technical Admin / Package Extension |

For the Nominee example: if the `facility_nominees` domain object already exists, Level 1-2 are sufficient. If it doesn't exist yet, Level 3 is needed (but only once).

---

## 13. Appendix: Complete Table Schemas

### Entity Relationship Summary

```
features
  └── feature_versions
        ├── flow_definitions
        │     ├── flow_nodes
        │     └── flow_edges
        ├── page_definitions
        │     ├── page_sections
        │     └── form_steps
        │           └── form_fields
        │                 └── field_bindings
        ├── rule_sets
        │     └── rule_rows
        ├── formula_definitions
        ├── document_templates
        ├── feature_permissions
        ├── feature_menu_items
        └── scoped_overrides
```

### Runtime Data Flow

```
Published Feature Version
        │
        ├──► Page Schema ──► Livewire Renderer ──► User UI
        │
        ├──► Flow Definition ──► Automation Engine
        │         │
        │         ├──► Node Executor (document, GL, SMS, task)
        │         └──► Node Execution Logs
        │
        ├──► Rule Sets ──► Validation Engine
        │
        ├──► Formula Defs ──► Calculation Engine
        │
        ├──► Menu Items ──► Sidebar Registry ──► User Navigation
        │
        └──► Permissions ──► Access Control ──► Role Gates
```

---

## Final Verdict

V3 that is **fit for sale** is not "an automation canvas with big promises".

V3 that is **fit for sale** is:

> ### Feature Operating Platform for Arrahnu
>
> Where:
> - **Flows can be built** — and they execute
> - **UI can be built** — and it renders
> - **Features can be published** — and they go live
> - **Sidebar can be generated** — and users see it
> - **Runtime truly executes** — traceable and audited
> - **Guardrails remain hard** — no shortcuts

This is the only version worth signing off.

---

*Blueprint authored: 19 April 2026*
*Stack: Laravel 13 + Livewire 4 + Flux UI + Tailwind CSS 4*
*Architecture: Kernel-led, Module-based, Registry-driven, Publish-controlled, Runtime-executed*
