# Implementation Plan: Simulation Preflight Check

## Overview

Transform the existing single-phase `FlowSimulationModal` into a two-phase modal with preflight readiness checking. Extract shared config field logic into a composable, build a preflight scanner with animated progress, add an inline fix form with writeback, and wire everything through `FlowCanvas`. All changes are client-side Vue 3 + TypeScript.

## Tasks

- [x] 1. Extract shared `useConfigFields` composable
  - [x] 1.1 Create `resources/js/builders/flow/composables/useConfigFields.ts`
    - Define the `ConfigFieldDef` interface with `key`, `label`, `type`, `options`, and `placeholder` fields
    - Implement `getConfigFields(nodeType, config, commands)` function by extracting the switch-case logic verbatim from `NodeInspector.vue`'s `configFields` computed
    - Handle all 13 node types: trigger, command, approval, notification, document, gl_action, decision, formula, payment_gateway, tawarruq_calc, generate_pdf, vault_action, api_request
    - Handle conditional fields (provider-specific credentials for payment_gateway, auth-type fields for api_request, method-dependent body for api_request, command argument mapping)
    - Export `getConfigFields` and `ConfigFieldDef`
    - _Requirements: 7.1, 7.2, 7.4_

  - [x] 1.2 Refactor `NodeInspector.vue` to import and use `getConfigFields` from the shared composable
    - Replace the inline `configFields` computed with a call to `getConfigFields(props.node.nodeType, localConfig.value, props.commands)`
    - Verify the inspector renders identically for all node types after refactoring
    - _Requirements: 7.3_

  - [ ]* 1.3 Write property test: Config field definition consistency (Property 2)
    - **Property 2: Config field definition consistency after extraction**
    - Install `vitest` and `fast-check` as dev dependencies, create `vitest.config.ts`
    - Generate random node type from the 13 known types + random config + random commands array
    - Assert `getConfigFields(type, config, commands)` output matches a reference snapshot of the original logic
    - **Validates: Requirements 7.2, 7.3**

- [x] 2. Checkpoint — Verify extraction
  - Ensure all tests pass, ask the user if questions arise.

- [x] 3. Implement `usePreflightChecker` composable
  - [x] 3.1 Create `resources/js/builders/flow/composables/usePreflightChecker.ts`
    - Define `PreflightNodeResult` and `PreflightResult` interfaces as specified in the design
    - Implement `checkNode(node, commands)` — calls `getConfigFields`, iterates non-divider fields, checks emptiness rules (undefined/null/empty string for strings, undefined/null for numbers, undefined/null/empty object for json)
    - Implement `runScan(nodes, commands, delayMs = 300)` — sequential async scan with configurable delay, populating reactive refs: `isScanning`, `scanProgress`, `currentNode`, `completedNodes`, `results`
    - Compute summary counts: `totalScanned`, `totalPassed`, `totalFailed` from `nodeResults`
    - Handle edge cases: nodes without `data.nodeType` (skip), nodes without `data.config` (treat as `{}`), nodes with no required fields like `end` (auto-pass)
    - _Requirements: 1.3, 2.1, 2.2, 2.3, 2.4, 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10, 3.11, 3.12, 3.13, 3.14, 3.15, 3.16, 3.17_

  - [ ]* 3.2 Write property test: Preflight validation detects missing required fields (Property 1)
    - **Property 1: Preflight validation detects missing required fields**
    - Generate random node type + random config with random subset of fields populated/empty
    - Assert `checkNode()` result's `missingFields` matches exactly the set of non-divider fields from `getConfigFields()` that are empty in config
    - **Validates: Requirements 3.2–3.17**

  - [ ]* 3.3 Write property test: Preflight summary counts consistency (Property 3)
    - **Property 3: Preflight summary counts are consistent with node results**
    - Generate random array of `PreflightNodeResult` objects with random `passed` values
    - Assert `totalScanned === nodeResults.length`, `totalPassed === count(passed===true)`, `totalFailed === count(passed===false)`, `totalPassed + totalFailed === totalScanned`
    - **Validates: Requirements 4.1**

- [x] 4. Checkpoint — Verify composables
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Redesign `FlowSimulationModal.vue` as two-phase modal
  - [x] 5.1 Add new props and emits to `FlowSimulationModal.vue`
    - Add props: `nodes: Array`, `commands: Array`
    - Add emit: `update-node-config` with payload `{ nodeId, key, value }`
    - Add internal `phase` ref: `'preflight' | 'simulation'`, default `'preflight'`
    - Import and use `usePreflightChecker` composable
    - _Requirements: 1.1, 1.2_

  - [x] 5.2 Implement Phase 1 — Preflight scanning UI
    - Auto-trigger `runScan()` when modal opens (watch `show` prop)
    - Display animated progress bar advancing as each node is scanned
    - Show current node label and type during scanning
    - Show pass (✅) / fail (❌) indicator per completed node in `completedNodes`
    - Display summary (total scanned, passed, failed) when scan completes
    - Show "Proceed to Simulation" button when all nodes pass
    - Show "Re-check" button when failures exist
    - Cancel scan if modal is closed mid-scan
    - _Requirements: 1.1, 1.2, 1.3, 2.1, 2.2, 2.3, 2.4, 4.1, 4.2, 4.3, 4.4, 9.1, 9.2, 9.3_

  - [x] 5.3 Implement inline fix form for failed nodes
    - Render when `results.totalFailed > 0`, grouped by node (label + type as section header)
    - Render each missing field using the correct input type from `ConfigFieldDef`: `select` (string options), `select` (command objects), `text`, `textarea`, `json`, `number`
    - Track form values in a `Record<string, Record<string, any>>` keyed by nodeId
    - Add "Save & Re-check" button that emits `update-node-config` for each filled field, then re-runs scan
    - Validate JSON fields inline before save
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7_

  - [x] 5.4 Implement Phase 2 — Simulation phase (preserve existing UI)
    - Move existing simulation UI (payload editor, launch button, execution path timeline) into Phase 2 section
    - Show Phase 2 only when `phase === 'simulation'`
    - Add "Back to Preflight" button to return to Phase 1
    - Retain all existing simulation functionality unchanged
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

- [x] 6. Update `FlowCanvas.vue` to wire props, emits, and writeback
  - [x] 6.1 Pass `nodes` and `commands` props to `FlowSimulationModal`
    - Add `:nodes="nodes"` and `:commands="commands"` bindings on the `<FlowSimulationModal>` component
    - _Requirements: 7.2_

  - [x] 6.2 Implement `handleModalConfigUpdate` for writeback
    - Handle `update-node-config` emit from `FlowSimulationModal`
    - Implement dot-notation support for nested keys (e.g., `credentials.api_key`)
    - Set `isDirty = true` after writeback to trigger auto-save
    - Display error toast if target node not found
    - _Requirements: 8.1, 8.2, 8.3, 8.4_

  - [ ]* 6.3 Write property test: Writeback correctly updates config (Property 4)
    - **Property 4: Writeback correctly updates config at flat and nested paths**
    - Generate random config object + random key (flat or dot-notation) + random value
    - Assert after writeback: reading the key path returns the written value; other keys unchanged
    - **Validates: Requirements 8.1, 8.2**

- [x] 7. Checkpoint — Verify full integration
  - Ensure all tests pass, ask the user if questions arise.

- [ ]* 8. Write property test: Inline form renders correct field types (Property 5)
  - **Property 5: Inline form renders correct field types matching ConfigFieldDef**
  - Generate random `ConfigFieldDef` objects with various types and options
  - Assert rendered element type matches field definition type; select options match
  - **Validates: Requirements 5.3, 5.4**

- [x] 9. Final checkpoint — Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document using fast-check
- All changes are client-side only — no backend modifications needed
- The design uses TypeScript for composables and Vue 3 SFC for components
