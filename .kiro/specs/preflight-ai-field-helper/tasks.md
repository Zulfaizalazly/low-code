# Implementation Plan: Preflight AI Field Helper

## Overview

Add AI-powered contextual help to every field in the Inline Fix Form. The implementation creates a backend `AIFieldHintController` + `FieldHintPromptBuilder` for AI hint generation, a frontend `useFieldHintService` composable for API calls and caching, a `FieldHelperPopover.vue` component for the dark-glass popover UI, and integrates ℹ️ icons into `FlowSimulationModal.vue`. The backend uses PHP/Laravel; the frontend uses TypeScript + Vue 3.

## Tasks

- [x] 1. Create `FieldHintPromptBuilder` service
  - [x] 1.1 Create `app/Studio/AI/FieldHintPromptBuilder.php`
    - Implement `buildSystemPrompt()` returning Ar-Rahnu domain context: Islamic pawnbroking flow builder, all 13+ node types and their purposes, instruction to respond in English with practical domain examples
    - Implement `buildUserPrompt(string $nodeType, string $fieldKey, string $fieldLabel, string $mode, ?string $userQuestion)` with mode-specific instructions: `quick` → 20–50 token concise response; `detailed` → comprehensive explanation
    - When `userQuestion` is provided, append the user question to the detailed prompt
    - When `nodeType` is `payment_gateway` and `fieldKey` starts with `credentials.`, append provider-specific context for Billplz, Bayarcash, ToyyibPay, Stripe, and Chip
    - _Requirements: 7.5, 7.6, 7.7, 7.8, 8.1, 8.2, 8.3, 8.4_

  - [ ]* 1.2 Write property test for prompt construction (Property 5)
    - **Property 5: Prompt construction includes required context**
    - Use PHPUnit data providers to generate random combinations of `nodeType`, `fieldKey`, `fieldLabel`, `mode`, and optional `userQuestion`
    - Assert: system prompt contains "Ar-Rahnu" and "Islamic pawnbroking"; quick mode user prompt contains token limit instruction; detailed mode user prompt contains comprehensive instruction; `userQuestion` appears in prompt when provided; `payment_gateway` + `credentials.*` triggers provider context
    - **Validates: Requirements 7.5, 7.6, 7.7, 7.8, 8.1, 8.2, 8.3, 8.4**

- [x] 2. Create `AIFieldHintController`
  - [x] 2.1 Create `app/Http/Controllers/Api/Studio/AIFieldHintController.php`
    - Implement `hint(Request $request): JsonResponse` method
    - Validate request: `nodeType` (required|string|max:50), `fieldKey` (required|string|max:100), `fieldLabel` (required|string|max:200), `mode` (required|string|in:quick,detailed), `userQuestion` (nullable|string|max:500) — return 422 on failure
    - Rate limit: 30 requests per minute per authenticated user using `RateLimiter` facade with key `field-hint:{userId}` — return 429 when exceeded
    - Build prompt via `FieldHintPromptBuilder`, call OpenAI/Claude API via `Http::withToken()` (same pattern as `AIUIGenerator`), `max_tokens`: 60 for quick / 500 for detailed, `temperature`: 0.3, timeout: 15 seconds
    - Return `{ "hint": "..." }` on success, 504 on timeout, 500 on AI API failure
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.9, 7.10, 7.11, 7.12, 9.2, 9.3_

  - [ ]* 2.2 Write property test for request validation (Property 1)
    - **Property 1: Request validation accepts valid payloads and rejects invalid ones**
    - Use PHPUnit data providers to generate random payloads with random subsets of required fields present/absent, random `mode` values (valid and invalid), random string lengths
    - Assert: valid payloads pass validation; invalid payloads return 422 with appropriate error keys
    - **Validates: Requirements 2.4, 7.2, 7.3**

- [x] 3. Register route in `routes/web.php`
  - Add `Route::post('ai/field-hint', [AIFieldHintController::class, 'hint'])->middleware('permission:flows.edit')` inside the `api/studio` middleware group
  - Add the `use` import for `AIFieldHintController` at the top of the file
  - _Requirements: 7.1, 7.12_

- [x] 4. Checkpoint — Verify backend
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Create `useFieldHintService` composable
  - [x] 5.1 Create `resources/js/builders/flow/composables/useFieldHintService.ts`
    - Define `HintRequest`, `HintResponse`, and `PopoverState` interfaces as specified in the design
    - Implement in-memory `Map<string, string>` cache keyed by `${nodeType}::${fieldKey}` — only quick hints are cached
    - Implement `openHint(anchorEl, nodeType, fieldKey, fieldLabel)`: check cache first, if cached show immediately without API call; otherwise fetch from `POST /api/studio/ai/field-hint` with CSRF token
    - Implement `fetchDetailed()`: call endpoint with `mode: 'detailed'`, do NOT cache the response
    - Implement `askFollowUp(question)`: call endpoint with `mode: 'detailed'` and `userQuestion`, do NOT cache the response
    - Implement `retry()`: re-attempt the last failed API call
    - Implement `closePopover()`: reset popover state
    - Handle errors: network errors → "Unable to load hint. Please try again." with retry; 429 → "Too many requests. Please wait a moment."; other errors → generic message with retry
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 3.1, 3.2, 3.3, 3.4, 4.1, 4.2, 4.3, 4.4, 4.5, 5.1, 5.2, 5.3, 5.4, 5.5, 9.1, 9.2, 9.4_

  - [ ]* 5.2 Write property test for quick hint cache round-trip (Property 2)
    - **Property 2: Quick hint cache round-trip**
    - Use fast-check to generate random `nodeType` and `fieldKey` strings and random hint text
    - Assert: after storing a hint in cache, calling `openHint` with the same `nodeType+fieldKey` returns the cached text immediately and does NOT trigger `fetch()`
    - **Validates: Requirements 3.1, 3.2**

  - [ ]* 5.3 Write property test for cache isolation (Property 3)
    - **Property 3: Only quick hints are cached**
    - Use fast-check to generate random sequences of quick/detailed/follow-up operations with random `nodeType+fieldKey` combinations
    - Assert: after any sequence of operations, cache only contains entries from quick hint responses; cache size never increases from detailed or follow-up calls
    - **Validates: Requirements 4.5, 5.5**

- [x] 6. Create `FieldHelperPopover.vue` component
  - [x] 6.1 Create `resources/js/builders/flow/FieldHelperPopover.vue`
    - Accept `popover: PopoverState` prop; emit `fetch-detailed`, `ask-follow-up` (payload: string), `retry`, `close`
    - Use `<Teleport to="body">` to escape overflow clipping
    - Position using `getBoundingClientRect()` on `anchorEl`, prefer below-right, auto-flip if overflowing viewport; max width 320px
    - Dark glass theme: dark rgba background, subtle border, backdrop blur — matching `FlowSimulationModal` styling
    - Render conditionally: loading spinner (initial), quick hint text, "Complete explanation" button, loading spinner (detailed), detailed explanation text, follow-up text input + Send button, loading spinner (follow-up), follow-up answer text, error state with retry button
    - Minimum font size 12px with appropriate line height
    - Close on click outside
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 2.1, 2.2, 2.3, 2.6, 4.1, 4.2, 4.3, 4.4, 5.1, 5.2, 5.3, 5.4, 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 9.1, 9.4_

  - [ ]* 6.2 Write property test for popover viewport containment (Property 4)
    - **Property 4: Popover viewport containment**
    - Use fast-check to generate random anchor positions (x: 0–2000, y: 0–2000) and random viewport dimensions (width: 320–2560, height: 320–1440)
    - Extract the positioning logic into a pure function and test it
    - Assert: computed popover position keeps the entire popover rectangle (up to 320px wide) within viewport bounds
    - **Validates: Requirements 6.2**

- [x] 7. Integrate into `FlowSimulationModal.vue`
  - [x] 7.1 Add ℹ️ Helper Icons and wire popover into `FlowSimulationModal.vue`
    - Import and instantiate `useFieldHintService` composable
    - Import `FieldHelperPopover` component
    - Add a small circular ℹ️ icon button (24×24px min touch target, cursor pointer, white/light color) immediately after each `<label class="fix-field-label">` in the inline fix form
    - On icon click, call `openHint(anchorEl, nodeType, fieldKey, fieldLabel)` passing the node's type and the field's key/label
    - Render single `<FieldHelperPopover>` instance controlled by composable state, wire emits to composable methods (`fetch-detailed`, `ask-follow-up`, `retry`, `close`)
    - When a different ℹ️ icon is clicked while popover is open, close current and open new
    - Add click-outside handler to close popover
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 2.1, 6.3, 6.4_

- [x] 8. Checkpoint — Verify full integration
  - Ensure all tests pass, ask the user if questions arise.

- [ ]* 9. Write unit tests for end-to-end hint flow
  - Test: click ℹ️ icon → loading spinner → quick hint displayed → click "Complete explanation" → detailed appended → type follow-up → answer appended
  - Test: error state displays retry button, clicking retry re-triggers API call
  - Test: 429 response shows "Too many requests" message
  - Test: cached hint appears instantly on second click (no network call)
  - Test: closing and reopening modal clears cache
  - _Requirements: 2.1, 2.2, 2.3, 2.6, 3.2, 3.4, 4.1, 4.4, 5.1, 5.4, 9.1, 9.2, 9.4_

- [x] 10. Final checkpoint — Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate the 5 correctness properties from the design document
- Backend uses PHP/Laravel with PHPUnit data providers for property tests; frontend uses TypeScript/Vue 3 with fast-check for property tests
- The AI call follows the same `Http::withToken()` pattern used by the existing `AIUIGenerator`
