# Arrahnumation V3 — Delivery Roadmap, Team Structure & Definition of Done

> **Companion to:** V3-BLUEPRINT.md
> **Purpose:** This document defines how V3 gets built, in what order, by whom, and what "done" actually means at every level.
> **Date:** 19 April 2026

---

## Table of Contents

1. [Delivery Philosophy](#1-delivery-philosophy)
2. [Team Structure & Roles](#2-team-structure--roles)
3. [Phase Overview](#3-phase-overview)
4. [Priority Build Order](#4-priority-build-order)
5. [Phase 0: Foundation Sprint](#5-phase-0-foundation-sprint)
6. [Phase 1: Kernel & Domain Core](#6-phase-1-kernel--domain-core)
7. [Phase 2: Registry & Builder Layer](#7-phase-2-registry--builder-layer)
8. [Phase 3: Publish Pipeline & Runtime](#8-phase-3-publish-pipeline--runtime)
9. [Phase 4: Studio UX & Visual Builders](#9-phase-4-studio-ux--visual-builders)
10. [Phase 5: Integration, Polish & Hardening](#10-phase-5-integration-polish--hardening)
11. [Phase 6: Pilot & Production Launch](#11-phase-6-pilot--production-launch)
12. [Definition of Done — All Levels](#12-definition-of-done--all-levels)
13. [Risk Register](#13-risk-register)
14. [Milestone Calendar](#14-milestone-calendar)
15. [Exit Criteria: When Is V3 "Shippable"?](#15-exit-criteria-when-is-v3-shippable)

---

## 1. Delivery Philosophy

### Core Principles

| Principle | What It Means |
|---|---|
| **Vertical slices, not horizontal layers** | Every phase delivers a working slice top-to-bottom, not "all database first, then all UI later" |
| **Runtime-first, studio-second** | Build the engine that executes before building the canvas that designs. A beautiful builder with no runtime is a toy. |
| **Prove with Pledge** | The New Pledge feature is the reference implementation. Every phase must prove itself through this one feature end-to-end. |
| **No fake configurability** | If a config field exists in the studio, it must affect runtime behaviour. If it doesn't, delete it. |
| **QA gates are real** | Each phase has explicit acceptance criteria. If criteria are not met, the phase does not close. |
| **Ship incrementally, validate continuously** | Internal demo every 2 weeks. Stakeholder review every phase. No "big reveal at the end". |

### The Golden Rule

> **Nothing is "done" in V3 until a feature designed in the studio executes correctly in the runtime, is traceable node-by-node, and produces real outputs (documents, notifications, GL entries, audit trail).**

---

## 2. Team Structure & Roles

### Core Team

| Role | Count | Responsibility | Key Skills |
|---|---|---|---|
| **Technical Lead / Architect** | 1 | Architecture decisions, code review, technical direction, integration oversight | Laravel expert, system design, domain modelling |
| **Backend Engineer (Kernel)** | 1–2 | Auth, permissions, audit, scoping, compliance, transaction integrity | Laravel, database design, security |
| **Backend Engineer (Domain)** | 1–2 | Domain modules (Facility, Payment, Accounting, Approval, etc.), command/event pattern | Laravel, DDD patterns, business logic |
| **Backend Engineer (Runtime)** | 1 | Automation engine, flow executor, node runners, queue management, execution logging | Laravel, queue systems, state machines |
| **Backend Engineer (Registry)** | 1 | Registry tables, schema validation, publish pipeline, versioning, rollback | Laravel, JSON schema, data integrity |
| **Frontend Engineer (Studio)** | 1–2 | HQ Studio shell, Livewire pages, Flux UI, studio UX | Livewire 4, Flux UI, Tailwind, Alpine.js |
| **Frontend Engineer (Builders)** | 1 | Flow Builder canvas, Page Builder drag-drop (JS islands) | Vue.js/React, canvas libraries, drag-drop |
| **Frontend Engineer (Ops App)** | 1 | Branch/operations app, dynamic page renderer, sidebar registry | Livewire 4, Flux UI, dynamic component rendering |
| **QA Engineer** | 1 | Test strategy, acceptance testing, publish gate validation, regression | Laravel testing, browser testing, API testing |
| **Product Owner / BA** | 1 | Requirements, feature prioritisation, client communication, acceptance criteria | Arrahnu domain knowledge, product management |
| **DevOps / Infrastructure** | 0.5 | CI/CD, staging, deployment, monitoring setup | Laravel Forge/Vapor, GitHub Actions, server management |

### Extended Team (Phase 5–6)

| Role | Count | When Needed |
|---|---|---|
| **UX/UI Designer** | 0.5 | Phase 4–5 for builder polish and studio refinement |
| **Security Reviewer** | 0.5 | Phase 5 for audit, penetration testing |
| **Domain Expert / Arrahnu SME** | 0.5 | Phase 1–2 for domain model validation, Phase 6 for pilot |
| **Technical Writer** | 0.5 | Phase 5–6 for user documentation |

### Team Size Summary

| Phase | Active Team Size |
|---|---|
| Phase 0 (Foundation) | 3–4 |
| Phase 1 (Kernel & Domain) | 5–6 |
| Phase 2 (Registry & Builder) | 7–8 |
| Phase 3 (Publish & Runtime) | 8–9 |
| Phase 4 (Studio UX) | 8–10 |
| Phase 5 (Hardening) | 8–10 + extended |
| Phase 6 (Pilot) | 6–8 + extended |

### RACI Matrix

| Decision | Responsible | Accountable | Consulted | Informed |
|---|---|---|---|---|
| Architecture decisions | Tech Lead | Tech Lead | Backend Engineers | All |
| Domain model changes | Domain Engineer | Tech Lead | Product Owner, SME | All |
| UX/builder design | Frontend Engineers | Tech Lead | Product Owner | All |
| Publish gate rules | QA Engineer | Tech Lead | Product Owner | All |
| Feature prioritisation | Product Owner | Product Owner | Tech Lead, SME | All |
| Go/No-Go for pilot | Product Owner | Product Owner | Tech Lead, QA | All |
| Security sign-off | Security Reviewer | Tech Lead | DevOps | All |

---

## 3. Phase Overview

```
Phase 0 ──► Phase 1 ──► Phase 2 ──► Phase 3 ──► Phase 4 ──► Phase 5 ──► Phase 6
Foundation   Kernel &    Registry    Publish &   Studio UX   Integration  Pilot &
Sprint       Domain      & Builder   Runtime     & Visual    Polish &     Production
                Core      Layer                  Builders    Hardening    Launch
 2 weeks     4 weeks     5 weeks     5 weeks     4 weeks     4 weeks      4 weeks
```

**Total estimated timeline: 28 weeks (~7 months)**

> This is an honest estimate. Compressing below 24 weeks risks cutting corners on runtime, publish gates, or audit — all of which will be caught by QA.

---

## 4. Priority Build Order

### Why This Order Matters

The build order follows one rule: **dependencies flow downward**. You cannot build a builder if there's nothing to save to. You cannot build a publisher if there's no runtime to publish into. You cannot test end-to-end if the kernel doesn't enforce integrity.

### Priority Sequence

```
┌─────────────────────────────────────────────────────────────────┐
│  PRIORITY 1: Things that everything else depends on            │
│                                                                 │
│  ► Laravel 13 project scaffold                                  │
│  ► Database migrations (all 41 tables)                          │
│  ► Kernel: Auth, Permissions, Scoping, Audit                    │
│  ► Domain Models: Customer, Facility, FacilityItem, Nominee     │
│  ► Command/Event bus infrastructure                             │
└─────────────────────────────────┬───────────────────────────────┘
                                  │
┌─────────────────────────────────▼───────────────────────────────┐
│  PRIORITY 2: Things that make features possible                 │
│                                                                 │
│  ► Domain Modules: Valuation, Approval, Payment, Accounting     │
│  ► Registry: Features, Versions, Flows, Pages, Fields, Bindings │
│  ► Registry schema validation                                   │
│  ► Feature Bundle CRUD (API + basic UI)                         │
└─────────────────────────────────┬───────────────────────────────┘
                                  │
┌─────────────────────────────────▼───────────────────────────────┐
│  PRIORITY 3: Things that make features live                     │
│                                                                 │
│  ► Publish Pipeline: validation, simulation, versioning         │
│  ► Runtime Engine: flow executor, node runners                  │
│  ► Dynamic Page Renderer (Livewire)                             │
│  ► Action Executors: document, notification, GL, task           │
│  ► Execution logging + audit trail                              │
└─────────────────────────────────┬───────────────────────────────┘
                                  │
┌─────────────────────────────────▼───────────────────────────────┐
│  PRIORITY 4: Things that make building pleasant                 │
│                                                                 │
│  ► HQ Studio shell (Livewire + Flux)                            │
│  ► Flow Builder canvas (JS island)                              │
│  ► Page Builder drag-drop (JS island)                           │
│  ► Rule Builder (Livewire)                                      │
│  ► Formula Builder (Livewire)                                   │
│  ► Feature Workspace with progress bar                          │
└─────────────────────────────────┬───────────────────────────────┘
                                  │
┌─────────────────────────────────▼───────────────────────────────┐
│  PRIORITY 5: Things that make the product shippable             │
│                                                                 │
│  ► Sidebar/Menu dynamic registry                                │
│  ► Impact analysis engine                                       │
│  ► Release Center                                               │
│  ► Runtime Monitor                                              │
│  ► Control Tower dashboard                                      │
│  ► Scope overrides engine                                       │
│  ► Rollback mechanism                                           │
│  ► End-to-end New Pledge feature (full proof)                   │
│  ► Security hardening                                           │
│  ► Documentation                                                │
└─────────────────────────────────────────────────────────────────┘
```

---

## 5. Phase 0: Foundation Sprint

**Duration:** 2 weeks
**Goal:** Project scaffold, infrastructure, and development environment ready.

### Deliverables

| # | Deliverable | Owner |
|---|---|---|
| 0.1 | Laravel 13 project initialised with Livewire 4 + Flux UI + Tailwind 4 starter kit | Tech Lead |
| 0.2 | Database migrations created for all 41 tables (all 5 domains) | Backend (Kernel) |
| 0.3 | Git repository, branching strategy, PR conventions | Tech Lead + DevOps |
| 0.4 | CI/CD pipeline: lint, test, build | DevOps |
| 0.5 | Staging environment provisioned | DevOps |
| 0.6 | Folder structure implemented (`Kernel/`, `Domain/`, `Runtime/`, `Studio/`) | Tech Lead |
| 0.7 | Base model traits: `HasAuditTrail`, `HasScoping`, `HasVersioning` | Backend (Kernel) |
| 0.8 | Seeder: demo tenant, demo branches, demo users with roles | Backend (Kernel) |
| 0.9 | Tailwind + Flux design tokens configured | Frontend (Studio) |
| 0.10 | Test infrastructure: PHPUnit, Pest, browser test setup | QA |

### Phase 0 Definition of Done

- [ ] `php artisan migrate` runs cleanly with all 41 tables
- [ ] `php artisan test` passes with ≥1 smoke test
- [ ] Staging URL accessible with login screen
- [ ] Folder structure matches blueprint specification
- [ ] CI/CD pipeline triggers on push and blocks on failure

---

## 6. Phase 1: Kernel & Domain Core

**Duration:** 4 weeks
**Goal:** All foundational systems are operational. Core domain modules can process commands and emit events.

### Week 1–2: Kernel Systems

| # | Deliverable | Owner |
|---|---|---|
| 1.1 | Authentication system (login, logout, session, remember) | Backend (Kernel) |
| 1.2 | Role & Permission engine (role_key based, policy-driven) | Backend (Kernel) |
| 1.3 | Organisation / Entity / Branch scoping (tenant isolation) | Backend (Kernel) |
| 1.4 | Audit trail service (`AuditTrail::record()` on all writes) | Backend (Kernel) |
| 1.5 | Command bus infrastructure (`dispatch(new CreateFacility(...))`) | Backend (Kernel) |
| 1.6 | Event bus infrastructure (`event(new FacilityCreated(...))`) | Backend (Kernel) |
| 1.7 | Transaction integrity wrapper (DB transactions on commands) | Backend (Kernel) |
| 1.8 | Queue contract (job dispatch, retry, dead letter) | Backend (Kernel) |

### Week 3–4: Domain Modules (Core)

| # | Deliverable | Owner |
|---|---|---|
| 1.9 | Customer module: CRUD, contacts, search | Backend (Domain) |
| 1.10 | Facility module: create, status lifecycle, items, nominees | Backend (Domain) |
| 1.11 | Valuation module: gold price, weight, purity, LTV calculation | Backend (Domain) |
| 1.12 | Approval module: create task, assign, decide, escalate | Backend (Domain) |
| 1.13 | Payment module: receive, disburse, allocate, receipt | Backend (Domain) |
| 1.14 | Accounting module: journal entry, lines, balance check | Backend (Domain) |
| 1.15 | Document module: template registration, render, store | Backend (Domain) |
| 1.16 | Notification module: SMS/email dispatch, logging | Backend (Domain) |

### Phase 1 Definition of Done

- [ ] User can log in, scoped to their org/branch/role
- [ ] `CreateFacilityApplication` command creates a facility with items
- [ ] `facility.created` event fires and is logged in `event_logs`
- [ ] Audit trail records all CRUD operations with old/new values
- [ ] `CreateApprovalTask` assigns an approval to the correct role
- [ ] `ReceivePayment` creates a payment and posts a balanced journal entry
- [ ] Journal entry balancing is enforced (debit = credit, else exception)
- [ ] All domain module actions are covered by unit tests (≥80% coverage)
- [ ] Commands wrapped in DB transactions (failure = full rollback)
- [ ] Scoping works: Branch A data is invisible to Branch B

---

## 7. Phase 2: Registry & Builder Layer

**Duration:** 5 weeks
**Goal:** Feature Bundle CRUD is functional. Registry stores valid schemas. Basic CRUD UI exists for all registry entities.

### Week 5–6: Registry Core

| # | Deliverable | Owner |
|---|---|---|
| 2.1 | Feature CRUD (create, read, update, archive) | Backend (Registry) |
| 2.2 | Feature Version lifecycle (draft, in_review, approved, published, archived, rolled_back) | Backend (Registry) |
| 2.3 | Flow Definition CRUD with nodes and edges | Backend (Registry) |
| 2.4 | Page Definition CRUD with sections | Backend (Registry) |
| 2.5 | Form Step and Form Field CRUD | Backend (Registry) |
| 2.6 | Field Binding CRUD with validation | Backend (Registry) |
| 2.7 | Rule Set and Rule Row CRUD | Backend (Registry) |
| 2.8 | Formula Definition CRUD | Backend (Registry) |
| 2.9 | Document Template registration | Backend (Registry) |
| 2.10 | Feature Permission CRUD | Backend (Registry) |
| 2.11 | Feature Menu Item CRUD | Backend (Registry) |

### Week 7–8: Schema Validation

| # | Deliverable | Owner |
|---|---|---|
| 2.12 | Flow schema validator: connected graph, valid trigger, has end node | Backend (Registry) |
| 2.13 | Page schema validator: all fields have bindings, required fields present | Backend (Registry) |
| 2.14 | Field binding validator: target entity exists, target path valid | Backend (Registry) |
| 2.15 | Rule validator: conditions parseable, results actionable | Backend (Registry) |
| 2.16 | Formula validator: expression parseable, inputs/outputs match | Backend (Registry) |
| 2.17 | Feature completeness checker: all 14 publish gate checks (returns pass/fail per check) | Backend (Registry) |

### Week 9: Basic Studio UI (Livewire)

| # | Deliverable | Owner |
|---|---|---|
| 2.18 | Feature listing page (all features with status badges) | Frontend (Studio) |
| 2.19 | Feature create wizard (name, domain, type, entry mode, outputs) | Frontend (Studio) |
| 2.20 | Feature workspace with progress bar (Info/Flow/Page/Rules/Permissions/Menu/Outputs/Simulation/Publish) | Frontend (Studio) |
| 2.21 | Flow definition editor (table-based CRUD, not visual canvas yet) | Frontend (Studio) |
| 2.22 | Page/Form editor (step + field management, not drag-drop yet) | Frontend (Studio) |
| 2.23 | Rule editor (decision table UI) | Frontend (Studio) |
| 2.24 | Permission editor (role × action matrix) | Frontend (Studio) |
| 2.25 | Menu item editor | Frontend (Studio) |

### Proof Point: New Pledge Feature Bundle

At the end of Phase 2, the team must be able to:

1. Create a "New Pledge" feature via the wizard
2. Define a flow (intake → valuation → approval → disbursement) via CRUD UI
3. Define a page with 4 steps (Customer, Marhun, Nominee, Summary) via CRUD UI
4. Define fields with valid bindings to `facilities`, `facility_items`, `facility_nominees`
5. Define LTV rule and nominee-required rule
6. Set permissions for branch_staff, branch_manager
7. Set a sidebar menu item
8. Run the completeness checker and see all 14 checks pass

### Phase 2 Definition of Done

- [ ] Feature Bundle can be created with all components via UI
- [ ] Flow definition with nodes/edges can be saved and loaded
- [ ] Page with steps, fields, and bindings can be saved and loaded
- [ ] Schema validation catches: missing trigger, unbound field, incomplete permissions
- [ ] Feature completeness checker returns correct pass/fail for each of the 14 gates
- [ ] New Pledge bundle passes all completeness checks
- [ ] All registry operations emit audit trail events
- [ ] Feature versioning works (create v2 from v1, both persist)
- [ ] All registry CRUD covered by tests (≥80% coverage)

---

## 8. Phase 3: Publish Pipeline & Runtime

**Duration:** 5 weeks
**Goal:** Complete publish-to-runtime pipeline. A published feature actually executes. This is the most critical phase.

### Week 10–11: Publish Pipeline

| # | Deliverable | Owner |
|---|---|---|
| 3.1 | Publish validation engine (runs all 14 gate checks, blocks on failure) | Backend (Registry) |
| 3.2 | Version snapshot (immutable copy of feature version at publish time) | Backend (Registry) |
| 3.3 | Publish approval workflow (submit → review → approve → publish) | Backend (Registry) |
| 3.4 | Release batch: group multiple features into one release | Backend (Registry) |
| 3.5 | Rollback mechanism: revert to previous published version | Backend (Registry) |
| 3.6 | Published Feature Registry (runtime reads only from here) | Backend (Runtime) |

### Week 12–13: Runtime Engine

| # | Deliverable | Owner |
|---|---|---|
| 3.7 | Flow Executor: load published flow, walk nodes, execute sequentially | Backend (Runtime) |
| 3.8 | Node Runner: trigger node | Backend (Runtime) |
| 3.9 | Node Runner: form_submit node (validate, persist via command) | Backend (Runtime) |
| 3.10 | Node Runner: decision node (evaluate conditions, choose path) | Backend (Runtime) |
| 3.11 | Node Runner: approval node (create approval task, wait) | Backend (Runtime) |
| 3.12 | Node Runner: notification node (dispatch SMS/email) | Backend (Runtime) |
| 3.13 | Node Runner: document node (render template, store) | Backend (Runtime) |
| 3.14 | Node Runner: gl_action node (create journal entry) | Backend (Runtime) |
| 3.15 | Node Runner: formula node (evaluate expression) | Backend (Runtime) |
| 3.16 | Node Runner: end node (mark execution complete) | Backend (Runtime) |
| 3.17 | Execution logger: log every node entry/exit/result to `automation_node_logs` | Backend (Runtime) |
| 3.18 | Error handling: node failure → mark, log, skip/retry per config | Backend (Runtime) |

### Week 14: Dynamic Page Renderer & Sidebar

| # | Deliverable | Owner |
|---|---|---|
| 3.19 | Dynamic Page Renderer: Livewire component that reads published page schema and renders fields | Frontend (Ops) |
| 3.20 | Form submission handler: collect field data, map via bindings, dispatch command | Frontend (Ops) |
| 3.21 | Dynamic Sidebar Registry: read published `feature_menu_items`, render by role | Frontend (Ops) |
| 3.22 | Feature launch: click sidebar → open published page → submit → flow executes | Frontend (Ops) |

### Proof Point: New Pledge End-to-End

At the end of Phase 3, the following must work **without any hardcoded logic**:

1. New Pledge feature is published
2. Branch staff user logs in → sees "New Pledge" in sidebar
3. Clicks it → page renders with Customer, Marhun, Nominee, Summary steps
4. Fills form → submits → command dispatched → facility created
5. Automation flow runs: valuation → approval task created → notification sent
6. Manager approves → document generated → GL entry posted
7. Entire run traceable node-by-node in `automation_node_logs`
8. Full audit trail present

**If this does not work end-to-end, Phase 3 is not complete.**

### Phase 3 Definition of Done

- [ ] Published feature can be loaded by runtime (draft versions ignored)
- [ ] Flow executor walks all node types correctly
- [ ] Each node runner produces correct side-effects (document, SMS, GL, audit)
- [ ] Every node execution is logged with input/output data
- [ ] Dynamic page renderer displays all field component types
- [ ] Form submission maps field data to correct entities via bindings
- [ ] Sidebar shows published features filtered by user role
- [ ] Rollback reverts to previous version; runtime immediately uses rolled-back version
- [ ] New Pledge works end-to-end (the proof point above)
- [ ] All runtime code covered by tests (≥80% coverage)
- [ ] No command executes without transaction wrapper
- [ ] No side-effect occurs without audit trail entry

---

## 9. Phase 4: Studio UX & Visual Builders

**Duration:** 4 weeks
**Goal:** Replace CRUD-based studio with premium visual builders. This is the "wow" phase for client demos.

### Week 15–16: HQ Studio Shell

| # | Deliverable | Owner |
|---|---|---|
| 4.1 | Studio layout: sidebar with Control Tower, Features, Builders, Runtime Monitor, Release Center, Governance | Frontend (Studio) |
| 4.2 | Control Tower dashboard: draft features, pending reviews, failed automations, recent publishes | Frontend (Studio) |
| 4.3 | Features listing with visual status (draft/published/archived), domain filter, search | Frontend (Studio) |
| 4.4 | Feature Workspace redesign: visual progress bar with completion status per step | Frontend (Studio) |
| 4.5 | Release Center: draft releases, pending, published, rollback history | Frontend (Studio) |
| 4.6 | Governance section: role management, scope overrides admin | Frontend (Studio) |

### Week 17: Flow Builder (JS Island)

| # | Deliverable | Owner |
|---|---|---|
| 4.7 | Flow Builder canvas: node placement, edge drawing, zoom, pan | Frontend (Builder) |
| 4.8 | Node palette: categorised by Entry, Form, Decision, Approval, Notification, Document, Financial, Task, System | Frontend (Builder) |
| 4.9 | Node properties panel: config, bindings, validation indicators | Frontend (Builder) |
| 4.10 | Edge conditions: always, expression, outcome | Frontend (Builder) |
| 4.11 | Flow validation indicators: incomplete nodes highlighted, invalid connections flagged | Frontend (Builder) |
| 4.12 | Save flow: sync canvas state to registry tables via API | Frontend (Builder) + Backend (Registry) |

### Week 18: Page Builder (JS Island) + Rule Builder

| # | Deliverable | Owner |
|---|---|---|
| 4.13 | Page Builder drag-drop: component library → canvas → reorder | Frontend (Builder) |
| 4.14 | Step management: add/remove/reorder form steps | Frontend (Builder) |
| 4.15 | Field properties panel: label, type, required, binding, validation | Frontend (Builder) |
| 4.16 | Page preview: live preview of how the page will render for branch users | Frontend (Builder) |
| 4.17 | Rule Builder: decision table UI with Business Mode (if/then) and Advanced Mode (expression) | Frontend (Studio) |
| 4.18 | Formula Builder: expression editor with input/output schema | Frontend (Studio) |

### Phase 4 Definition of Done

- [ ] HQ Studio shell looks professional (Flux UI, consistent layout, responsive)
- [ ] Flow Builder canvas: user can drag nodes, connect edges, configure properties, save
- [ ] Saved flow matches registry schema exactly (round-trip: canvas → save → reload → canvas)
- [ ] Page Builder: user can add steps, drag fields, set bindings, preview
- [ ] Saved page matches registry schema exactly (round-trip)
- [ ] Rule Builder: user can create rules in Business Mode, saved correctly
- [ ] Feature Workspace progress bar accurately reflects completion status
- [ ] Release Center shows publish history with version details
- [ ] All builders save via API to the same registry tables used by runtime
- [ ] No builder has a "save" that doesn't reach the database

---

## 10. Phase 5: Integration, Polish & Hardening

**Duration:** 4 weeks
**Goal:** Complete integration of all layers. Security hardening. Performance tuning. Documentation.

### Week 19–20: Integration & Advanced Features

| # | Deliverable | Owner |
|---|---|---|
| 5.1 | Simulation engine: run flow with sample data, return node-by-node results | Backend (Runtime) |
| 5.2 | Simulation UI: select scenario, run, view results, see doc/SMS preview | Frontend (Studio) |
| 5.3 | Impact analysis engine: compute affected branches, roles, documents | Backend (Registry) |
| 5.4 | Impact analysis UI: show impact report before publish | Frontend (Studio) |
| 5.5 | Runtime Monitor: live event stream, failed runs, node traces | Frontend (Studio) |
| 5.6 | Runtime Monitor detail: click any run → see node-by-node execution log | Frontend (Studio) |
| 5.7 | Scope overrides engine: branch/product/entity level overrides | Backend (Registry) |
| 5.8 | Scope overrides UI: manage overrides per feature | Frontend (Studio) |
| 5.9 | Document template builder: bind data fields, preview render | Frontend (Studio) + Backend (Domain) |
| 5.10 | Menu Builder: visual sidebar arrangement | Frontend (Studio) |

### Week 21–22: Hardening & Quality

| # | Deliverable | Owner |
|---|---|---|
| 5.11 | Security audit: permission checks on all routes, CSRF, input sanitisation | Backend (Kernel) + Security |
| 5.12 | Performance profiling: Pulse integration, slow query identification | Backend (all) + DevOps |
| 5.13 | Horizon setup: queue dashboard, worker configuration, retry policies | DevOps |
| 5.14 | Integration test suite: end-to-end tests for New Pledge, Redemption | QA |
| 5.15 | Browser test suite: critical user flows (login → create feature → publish → use) | QA |
| 5.16 | Regression test suite: version rollback, permission enforcement, scope isolation | QA |
| 5.17 | Error handling: graceful error pages, validation messages, edge case handling | Frontend (all) |
| 5.18 | Data seeder: complete demo data for pilot (features, flows, pages with realistic content) | Backend (all) |
| 5.19 | User documentation: HQ Studio guide, builder tutorials, publish workflow guide | Tech Writer |
| 5.20 | API documentation: all registry endpoints, runtime endpoints | Tech Writer + Backend |

### Phase 5 Definition of Done

- [ ] Simulation runs a complete flow with sample data and returns correct node results
- [ ] Impact analysis correctly identifies affected branches/roles/documents
- [ ] Runtime Monitor shows real-time execution data
- [ ] Scope overrides: Branch A and Branch B see different configurations for the same feature
- [ ] All routes require authentication and check permissions
- [ ] No N+1 query issues on critical pages (verified via Pulse)
- [ ] Horizon shows queue health, no unprocessed dead letters
- [ ] Integration tests pass for all critical flows
- [ ] Browser tests pass for complete user journeys
- [ ] Documentation covers: getting started, feature creation, publishing, monitoring

---

## 11. Phase 6: Pilot & Production Launch

**Duration:** 4 weeks
**Goal:** Controlled pilot with one real client/entity. Fix issues. Prepare for production launch.

### Week 23–24: Pilot Preparation

| # | Deliverable | Owner |
|---|---|---|
| 6.1 | Production infrastructure provisioned (separate from staging) | DevOps |
| 6.2 | Production database with real org/branch/user setup | DevOps + Backend |
| 6.3 | Demo features pre-configured: New Pledge, Redemption (at minimum) | Product Owner + Backend |
| 6.4 | User accounts and roles provisioned for pilot branches | Backend (Kernel) |
| 6.5 | Pilot training session for HQ users (studio walkthrough) | Product Owner |
| 6.6 | Pilot training session for branch users (operations walkthrough) | Product Owner |
| 6.7 | Monitoring and alerting setup (Pulse, Horizon, error tracking) | DevOps |

### Week 25–26: Live Pilot & Stabilisation

| # | Deliverable | Owner |
|---|---|---|
| 6.8 | Pilot launch with controlled branch(es) | All |
| 6.9 | Daily monitoring of runtime execution, errors, queue health | QA + DevOps |
| 6.10 | Bug triage and fix cycle (daily standups during pilot) | All |
| 6.11 | Performance monitoring under real load | Backend + DevOps |
| 6.12 | User feedback collection and prioritisation | Product Owner |
| 6.13 | Critical fix deployment cycle | Backend + DevOps |
| 6.14 | Final QA sign-off audit (all 10 criteria from blueprint) | QA |
| 6.15 | Production launch decision (go / no-go) | Product Owner + Tech Lead |

### Phase 6 Definition of Done

- [ ] Pilot branch can create, publish, and use features without developer intervention
- [ ] New Pledge feature used in real operations for ≥1 week without critical failure
- [ ] All 10 QA sign-off criteria pass (see Section 12.5)
- [ ] No critical or high-severity bugs open
- [ ] Performance acceptable under pilot load (page load <2s, flow execution <5s)
- [ ] Runtime Monitor shows healthy execution metrics
- [ ] Rollback tested: can revert a feature and operations continue with previous version
- [ ] Go/no-go decision made with documented rationale

---

## 12. Definition of Done — All Levels

### 12.1 Definition of Done: Code Level (Every PR)

Every pull request must satisfy:

| # | Criterion |
|---|---|
| 1 | Code follows folder structure convention (`Kernel/`, `Domain/`, `Runtime/`, `Studio/`) |
| 2 | All new public methods have PHPDoc comments |
| 3 | All commands wrapped in database transactions |
| 4 | All significant writes produce audit trail entries |
| 5 | All new routes check authentication and permissions |
| 6 | New database queries use eager loading (no N+1) |
| 7 | Unit tests written and passing (minimum 1 test per command/action) |
| 8 | No hardcoded values that should come from config or registry |
| 9 | Code review approved by at least 1 other engineer |
| 10 | CI pipeline passes (lint, test, build) |

### 12.2 Definition of Done: Feature Level (Every Feature Bundle Component)

| # | Criterion |
|---|---|
| 1 | Feature can be created via studio UI |
| 2 | Feature data persists correctly in registry tables |
| 3 | Feature can be versioned (create v2 from v1) |
| 4 | Feature passes all applicable publish gate checks |
| 5 | Published feature is loaded by runtime correctly |
| 6 | Published feature renders in operations app |
| 7 | Published feature executes flow with correct side-effects |
| 8 | Published feature appears in sidebar for correct roles |
| 9 | Feature can be rolled back to previous version |
| 10 | All operations produce complete audit trail |

### 12.3 Definition of Done: Phase Level

| # | Criterion |
|---|---|
| 1 | All phase deliverables completed |
| 2 | Phase-specific Definition of Done checklist satisfied |
| 3 | Proof point demonstrated (where applicable) |
| 4 | Test coverage ≥80% for new code |
| 5 | No critical or high-severity bugs open |
| 6 | Internal demo conducted and feedback addressed |
| 7 | Phase retrospective completed |
| 8 | Documentation updated (architecture, API, user guide as applicable) |

### 12.4 Definition of Done: Sprint Level (Every 2-Week Sprint)

| # | Criterion |
|---|---|
| 1 | Sprint goal achieved (or explicitly deprioritised with justification) |
| 2 | All completed work has passing tests |
| 3 | Internal demo or showcase of sprint output |
| 4 | Sprint board cleaned (no abandoned in-progress items) |
| 5 | Any blockers escalated and documented |

### 12.5 Definition of Done: Product Level (V3 Shippable)

This is the **ultimate** Definition of Done. V3 is only "done" when ALL of these are true:

| # | Criterion | How To Verify |
|---|---|---|
| 1 | Flows designed in studio **execute in runtime** | Create flow in builder → publish → trigger → verify execution log |
| 2 | Pages designed in builder **render for end users** | Create page in builder → publish → login as branch user → verify page renders |
| 3 | Published features **appear in sidebar by role** | Publish feature with role X → login as role X → verify sidebar → login as role Y → verify NOT in sidebar |
| 4 | All primary outputs work: **document, notification, GL, audit** | Run feature → verify document generated, SMS sent, journal entry balanced, audit trail present |
| 5 | All changes have **versioning and rollback** | Publish v1 → modify → publish v2 → rollback to v1 → verify runtime uses v1 |
| 6 | All overrides follow **scope with clear precedence** | Set platform default → set branch override → verify branch uses override, other branches use default |
| 7 | All flows can be **simulated before publish** | Create flow → run simulation → verify node-by-node results match expected |
| 8 | All runs can be **traced node-by-node** | Execute feature → open Runtime Monitor → verify each node shows input, output, timing, status |
| 9 | No major action runs **directly from UI without runtime contract** | Code audit: verify all significant writes go through commands, not direct Eloquent calls from controllers |
| 10 | No config "appears to exist" but **isn't actually used** | Schema audit: every registry field has a corresponding runtime consumer |
| 11 | **New Pledge** feature works end-to-end without any hardcoded logic | Full walkthrough: create → design → publish → use → trace → rollback |
| 12 | **At least one additional feature** (e.g., Redemption) works end-to-end | Proves the platform is generic, not just a New Pledge wrapper |

> **If any of these fail, V3 is not shippable.** This is the honest line.

---

## 13. Risk Register

| # | Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|---|
| R1 | Runtime engine complexity exceeds estimates | High | High | Start runtime early (Phase 3), build node runners incrementally, test each in isolation |
| R2 | Dynamic page renderer struggles with complex form types | Medium | High | Limit component types to domain-safe set, prototype repeater fields early |
| R3 | Flow Builder canvas (JS island) takes longer than expected | High | Medium | Begin with table-based CRUD (Phase 2), canvas is a UX upgrade (Phase 4), not a blocker |
| R4 | Schema validation complexity for publish gates | Medium | Medium | Define validation rules per gate explicitly in Phase 2, don't leave to Phase 3 |
| R5 | Team lacks Livewire 4 / Flux UI experience | Medium | Medium | Allocate Phase 0 for spiking, use Laravel starter kit as baseline |
| R6 | Scope overrides create confusing behaviour | Medium | High | Build scope resolution engine with clear precedence (platform < entity < branch), extensive tests |
| R7 | Performance under real load (many features, many users) | Low | High | Defer Octane, but profile from Phase 3 onwards with Pulse, fix N+1 early |
| R8 | Domain model changes mid-build (new entities needed) | Medium | Medium | Keep domain modules loosely coupled, use command/event contracts as stable interfaces |
| R9 | Client expects more "free-form" customisation | Medium | Low | Blueprint explicitly defines open vs locked areas. Align expectations in Phase 6 pilot training. |
| R10 | Publish pipeline too strict — blocks legitimate releases | Low | Medium | Make gate checks configurable (warning vs blocking), but never bypass audit/compliance checks |

---

## 14. Milestone Calendar

> Based on a start date of **Week 1 = Project Kickoff**.
> Adjust dates based on actual start date.

| Week | Phase | Milestone | Gate |
|---|---|---|---|
| **Week 2** | Phase 0 | ✅ Foundation complete: migrations run, CI green, staging live | DevOps sign-off |
| **Week 4** | Phase 1 | ✅ Kernel operational: auth, permissions, audit, scoping | Tech Lead review |
| **Week 6** | Phase 1 | ✅ Domain core complete: `CreateFacility` command works E2E | Internal demo |
| **Week 8** | Phase 2 | ✅ Registry core: Feature Bundle CRUD functional | Tech Lead review |
| **Week 9** | Phase 2 | ✅ Schema validators pass on New Pledge bundle | QA validation |
| **Week 11** | Phase 2 | ✅ Studio CRUD UI live: features, flows, pages, rules manageable | Internal demo |
| **Week 13** | Phase 3 | ✅ Publish pipeline: feature can be published with all 14 gates | QA validation |
| **Week 14** | Phase 3 | ✅ Runtime engine: flow executor runs with all node types | Tech Lead review |
| **Week 15** | Phase 3 | 🎯 **KEY MILESTONE: New Pledge works end-to-end** | Full team demo |
| **Week 16** | Phase 3 | ✅ Dynamic page renderer + sidebar registry operational | Internal demo |
| **Week 18** | Phase 4 | ✅ Flow Builder canvas operational (design → save → reload) | Internal demo |
| **Week 19** | Phase 4 | ✅ Page Builder drag-drop operational | Internal demo |
| **Week 20** | Phase 4 | ✅ HQ Studio shell complete with all sections | Stakeholder demo |
| **Week 22** | Phase 5 | ✅ Simulation, impact analysis, runtime monitor complete | QA sign-off |
| **Week 23** | Phase 5 | ✅ Security audit, performance profiling, documentation | Tech Lead + Security review |
| **Week 24** | Phase 6 | ✅ Pilot environment ready | DevOps + Product Owner sign-off |
| **Week 26** | Phase 6 | ✅ Pilot feedback collected, critical fixes applied | Daily triage |
| **Week 28** | Phase 6 | 🎯 **FINAL MILESTONE: Go/No-Go production decision** | Product Owner + Tech Lead + QA |

### Key Milestone Visualization

```
Week 2        Week 6         Week 11        Week 15          Week 20         Week 24         Week 28
  │              │              │              │                │               │               │
  ▼              ▼              ▼              ▼                ▼               ▼               ▼
Foundation   Domain Core    Studio CRUD    NEW PLEDGE       Studio UX      Pilot Ready    GO/NO-GO
 Ready        Works E2E      UI Live       END-TO-END ★     Complete       Environment     DECISION ★
                                           (critical)                                     (final)
```

---

## 15. Exit Criteria: When Is V3 "Shippable"?

### Mandatory Conditions (All Must Be True)

| # | Condition | Verified By |
|---|---|---|
| 1 | All 12 Product-Level Definition of Done criteria pass | QA Engineer |
| 2 | No critical bugs open | QA Engineer |
| 3 | No high-severity bugs open for >48 hours | QA Engineer |
| 4 | New Pledge feature works in pilot without developer intervention for ≥1 week | Product Owner |
| 5 | At least one additional feature works end-to-end | QA Engineer |
| 6 | Performance: average page load <2s, flow execution <5s | DevOps |
| 7 | Security: no critical or high vulnerabilities | Security Reviewer |
| 8 | Rollback tested and functional | QA Engineer |
| 9 | User documentation available for HQ Studio and Operations App | Product Owner |
| 10 | Monitoring operational: Pulse, Horizon, error alerting | DevOps |

### Desirable Conditions (Recommended But Not Blocking)

| # | Condition | Priority |
|---|---|---|
| 1 | ≥3 feature bundles working end-to-end | High |
| 2 | Test coverage ≥85% across all modules | Medium |
| 3 | Load tested with projected user count | Medium |
| 4 | Oktane enabled and benchmarked | Low |
| 5 | Reverb real-time for runtime monitor | Low |
| 6 | Mobile responsive for operations app | Medium |

### The Final Honest Statement

> **V3 is shippable when a non-technical HQ user can create a feature bundle, design its flow and forms in the builders, publish it through the gates, and a branch staff member can immediately see and use that feature in their sidebar — with the entire execution producing real documents, real notifications, real GL entries, and a complete audit trail — without a single line of code being written or deployed.**
>
> Anything less than that is a demo, not a product.

---

*Document authored: 19 April 2026*
*Companion to: V3-BLUEPRINT.md, V3-MIGRATIONS-REFERENCE.md*
