# Visual Builders - Gap Analysis
**Date:** 20 April 2026  
**Status:** Analysis Complete  
**Compared Against:** `.kiro/specs/visual-builders/requirements.md`

---

## Executive Summary

The Visual Builders feature is **~70% complete**. The core UI components and basic persistence are implemented, but several critical features from the requirements are missing:

### ✅ What's Complete (70%)
- Flow Builder UI with Vue Flow
- Page Builder UI with drag-drop
- Node/Field palettes
- Basic node/field configuration
- Livewire proxy components
- Save/load functionality
- Registry models and migrations
- AI UI generation integration

### ❌ What's Missing (30%)
- **API endpoints** for validation and operations
- **Validation engine** (flow/page validation)
- **Preview functionality** for flows
- **Responsive preview modes** (tablet/mobile)
- **Auto-save** functionality
- **Undo/Redo** support
- **Keyboard shortcuts**
- **Error handling** and user feedback
- **Testing** (unit + E2E tests)

---

## Detailed Gap Analysis

### 1. Flow Builder Gaps

#### ✅ Implemented (FR-1.1 to FR-1.6)
- [x] Canvas rendering with Vue Flow
- [x] Zoom and pan controls
- [x] Grid background and minimap
- [x] 9 node types (trigger, command, decision, approval, notification, document, gl_action, formula, end)
- [x] Drag-and-drop from palette
- [x] Node connections (edges)
- [x] Node configuration panel (NodeInspector)
- [x] Node deletion
- [x] Save to registry tables
- [x] Load existing flows

#### ❌ Missing Features

##### 1.1 Validation (FR-1.5) - **CRITICAL**
**Status:** Partially implemented (client-side only)  
**What exists:**
- Basic validation in `FlowCanvas.vue` (lines 200-240)
- Checks for trigger, end node, disconnected nodes
- Visual warnings in UI

**What's missing:**
```php
// ❌ Missing: Server-side validation endpoint
POST /api/studio/flows/{flowId}/validate

// ❌ Missing: Validation service class
app/Studio/Validation/FlowValidator.php

// ❌ Missing: Validation rules:
- Circular dependency detection
- Command class existence check
- Formula reference validation
- Template key validation
- Expression syntax validation
```

**Impact:** Users can save invalid flows that will fail at runtime.

##### 1.2 Preview Functionality - **HIGH PRIORITY**
**Status:** Not implemented  
**Requirements:** US-FB-009 (not in original list, but implied)

**What's missing:**
```vue
// ❌ Missing: Flow preview modal
resources/js/builders/flow/FlowPreviewModal.vue

// ❌ Missing: Flow simulation endpoint
POST /api/studio/flows/{flowId}/simulate
```

**Impact:** Users cannot test flows before publishing.

##### 1.3 Auto-save (FR-1.6) - **MEDIUM PRIORITY**
**Status:** Not implemented  
**What exists:** Manual save only

**What's missing:**
```javascript
// ❌ Missing: Auto-save timer in FlowCanvas.vue
setInterval(() => {
  if (isDirty.value) {
    saveFlow()
  }
}, 30000) // Every 30 seconds
```

##### 1.4 Keyboard Shortcuts - **LOW PRIORITY**
**Status:** Not implemented  
**Requirements:** NFR-2 (Usability)

**What's missing:**
```javascript
// ❌ Missing: Keyboard event handlers
- Delete key (partially works via Vue Flow)
- Ctrl+Z (Undo)
- Ctrl+Y (Redo)
- Ctrl+S (Save)
- Ctrl+D (Duplicate node)
```

##### 1.5 Edge Configuration - **MEDIUM PRIORITY**
**Status:** Basic implementation only  
**What exists:** Edges created with default config

**What's missing:**
```vue
// ❌ Missing: Edge inspector panel
// ❌ Missing: Condition editor for decision branches
// ❌ Missing: Edge labels/conditions UI
```

---

### 2. Page Builder Gaps

#### ✅ Implemented (FR-2.1 to FR-2.7)
- [x] Canvas rendering
- [x] Component library (15+ field types)
- [x] Multi-step forms
- [x] Drag-and-drop fields
- [x] Field configuration panel
- [x] Data binding UI
- [x] Field reordering
- [x] Save to registry tables
- [x] Load existing pages
- [x] Preview modal (PagePreviewModal.vue)

#### ❌ Missing Features

##### 2.1 Responsive Preview Modes (FR-2.1) - **MEDIUM PRIORITY**
**Status:** Partially implemented  
**What exists:**
- Preview mode toggles in UI (desktop/tablet/mobile)
- Canvas width changes

**What's missing:**
```vue
// ❌ Missing: Actual responsive rendering
// Current implementation only changes canvas width
// Need to apply responsive CSS classes and test field layouts
```

##### 2.2 Validation (FR-2.6) - **CRITICAL**
**Status:** Not implemented  
**What exists:** None

**What's missing:**
```php
// ❌ Missing: Server-side validation endpoint
POST /api/studio/pages/{pageId}/validate

// ❌ Missing: Validation service class
app/Studio/Validation/PageValidator.php

// ❌ Missing: Validation rules:
- Duplicate field key detection
- Binding validation (entity/field exists)
- Required field marking
- Step completeness check
```

**Impact:** Users can save invalid pages that will fail at runtime.

##### 2.3 Auto-save (FR-2.7) - **MEDIUM PRIORITY**
**Status:** Not implemented  
**What exists:** Manual save only

**What's missing:**
```javascript
// ❌ Missing: Auto-save timer in PageBuilder.vue
setInterval(() => {
  if (isDirty.value) {
    savePage()
  }
}, 30000)
```

##### 2.4 Field Validation Rules UI - **HIGH PRIORITY**
**Status:** Not implemented  
**Requirements:** FR-2.4 (Field Configuration)

**What's missing:**
```vue
// ❌ Missing: Validation rules editor in field inspector
// Should support:
- Min/max length
- Regex patterns
- Custom validation rules
- Conditional validation
```

##### 2.5 Computed/Lookup Bindings (FR-2.5) - **MEDIUM PRIORITY**
**Status:** Partially implemented  
**What exists:** Direct bindings only

**What's missing:**
```vue
// ❌ Missing: Binding mode selector
// ❌ Missing: Formula binding UI
// ❌ Missing: Lookup binding UI (reference data)
```

---

### 3. API Endpoints - **CRITICAL GAP**

**Status:** Not implemented  
**Requirements:** Section 8 (API Requirements)

#### Missing Endpoints

```php
// ❌ Flow Builder APIs
GET    /api/studio/flows/{flowId}              // Load flow (currently via Livewire)
POST   /api/studio/flows/{flowId}/save         // Save flow (currently via Livewire)
POST   /api/studio/flows/{flowId}/validate     // Validate flow ⚠️ CRITICAL
POST   /api/studio/flows/{flowId}/simulate     // Simulate flow execution

// ❌ Page Builder APIs
GET    /api/studio/pages/{pageId}              // Load page (currently via Livewire)
POST   /api/studio/pages/{pageId}/save         // Save page (currently via Livewire)
POST   /api/studio/pages/{pageId}/validate     // Validate page ⚠️ CRITICAL
POST   /api/studio/pages/{pageId}/preview      // Generate preview data

// ❌ Utility APIs
GET    /api/studio/entities                    // List available entities
GET    /api/studio/entities/{entity}/fields    // List entity fields
GET    /api/studio/commands                    // List domain commands (exists in Livewire)
GET    /api/studio/formulas                    // List available formulas
GET    /api/studio/templates                   // List document templates
```

**Current State:**
- All operations go through Livewire components
- No REST API layer
- No validation endpoints

**Impact:**
- Cannot validate before save
- No external integrations possible
- Harder to test
- No API documentation

**Recommendation:** Create API controller layer:
```php
app/Http/Controllers/Studio/FlowBuilderController.php
app/Http/Controllers/Studio/PageBuilderController.php
app/Http/Controllers/Studio/ValidationController.php
```

---

### 4. Validation Services - **CRITICAL GAP**

**Status:** Not implemented  
**Requirements:** Section 7 (Validation Rules)

#### Missing Validation Classes

```php
// ❌ Flow Validation Service
namespace App\Studio\Validation;

class FlowValidator
{
    public function validate(FlowDefinition $flow): ValidationResult
    {
        // VR-1.1: Has trigger
        // VR-1.2: Has end node
        // VR-1.3: All nodes connected
        // VR-1.4: No orphans
        // VR-1.5: Config complete
        // VR-1.6: Valid commands
        // VR-1.7: Valid formulas
        // VR-1.8: Valid templates
    }
}

// ❌ Page Validation Service
namespace App\Studio\Validation;

class PageValidator
{
    public function validate(PageDefinition $page): ValidationResult
    {
        // VR-2.1: Has steps
        // VR-2.2: Has fields
        // VR-2.3: Unique keys
        // VR-2.4: Has bindings
        // VR-2.5: Valid bindings
        // VR-2.6: Required marked
        // VR-2.7: Valid components
    }
}

// ❌ Validation Result DTO
namespace App\Studio\Validation;

class ValidationResult
{
    public bool $valid;
    public array $errors;
    public array $warnings;
}
```

**Impact:**
- Invalid flows/pages can be saved
- Runtime errors will occur
- Poor user experience

---

### 5. Testing - **CRITICAL GAP**

**Status:** Minimal testing  
**Requirements:** NFR-5 (Maintainability)

#### What Exists
```php
// ✅ Basic persistence test
tests/Feature/Studio/VisualBuilderPersistenceTest.php
- Tests save flow state
- Tests save page state
```

#### What's Missing

```php
// ❌ Unit Tests
tests/Unit/Studio/Validation/FlowValidatorTest.php
tests/Unit/Studio/Validation/PageValidatorTest.php
tests/Unit/Studio/Registry/FlowDefinitionTest.php
tests/Unit/Studio/Registry/PageDefinitionTest.php

// ❌ Feature Tests
tests/Feature/Studio/FlowBuilderTest.php
tests/Feature/Studio/PageBuilderTest.php
tests/Feature/Studio/FlowValidationTest.php
tests/Feature/Studio/PageValidationTest.php

// ❌ E2E Tests (Dusk)
tests/Browser/Studio/FlowBuilderTest.php
tests/Browser/Studio/PageBuilderTest.php
- Test drag-and-drop
- Test node configuration
- Test save/load
- Test validation
```

**Impact:**
- No confidence in code quality
- Regressions likely
- Hard to refactor

---

### 6. User Experience Gaps

#### 6.1 Error Handling - **HIGH PRIORITY**
**Status:** Basic implementation  
**What exists:**
- AI generation error handling in FlowCanvas.vue
- Basic Livewire error messages

**What's missing:**
```javascript
// ❌ Missing: Comprehensive error handling
- Network error recovery
- Validation error display
- Save conflict resolution
- Graceful degradation
```

#### 6.2 Loading States - **MEDIUM PRIORITY**
**Status:** Minimal implementation  
**What exists:**
- "Initializing Visual Canvas..." message
- AI generation loading state

**What's missing:**
```vue
// ❌ Missing: Loading indicators for:
- Save operations
- Load operations
- Validation operations
- Node configuration updates
```

#### 6.3 User Feedback - **MEDIUM PRIORITY**
**Status:** Basic implementation  
**What exists:**
- Dirty indicator
- Save status message

**What's missing:**
```vue
// ❌ Missing: Toast notifications
// ❌ Missing: Success confirmations
// ❌ Missing: Progress indicators
// ❌ Missing: Undo/Redo feedback
```

#### 6.4 Onboarding - **LOW PRIORITY**
**Status:** Not implemented  
**Requirements:** NFR-2 (Usability)

**What's missing:**
```vue
// ❌ Missing: Tutorial/walkthrough
// ❌ Missing: Tooltips on all actions
// ❌ Missing: Help documentation links
// ❌ Missing: Example flows/pages
```

---

### 7. Performance Gaps

#### 7.1 Large Flow Handling - **MEDIUM PRIORITY**
**Status:** Unknown (not tested)  
**Requirements:** NFR-1 (Performance)

**What's missing:**
```javascript
// ❌ Missing: Performance testing with 50+ nodes
// ❌ Missing: Virtualization for large node lists
// ❌ Missing: Lazy loading of node configurations
// ❌ Missing: Debouncing for auto-save
```

#### 7.2 Optimization - **LOW PRIORITY**
**Status:** Not optimized  
**What's missing:**
```javascript
// ❌ Missing: Memoization of computed properties
// ❌ Missing: Lazy loading of components
// ❌ Missing: Code splitting
```

---

### 8. Documentation Gaps

**Status:** Minimal documentation  
**Requirements:** NFR-5 (Maintainability)

#### What's Missing

```markdown
// ❌ Missing: User documentation
docs/visual-builders/flow-builder-guide.md
docs/visual-builders/page-builder-guide.md
docs/visual-builders/node-types.md
docs/visual-builders/field-types.md

// ❌ Missing: Developer documentation
docs/visual-builders/architecture.md
docs/visual-builders/api-reference.md
docs/visual-builders/extending-builders.md

// ❌ Missing: Code comments
- Component documentation
- Function documentation
- Complex logic explanation
```

---

## Priority Matrix

### P0 (Critical - Must Have)
1. **Validation Services** (FlowValidator, PageValidator)
2. **Validation API Endpoints** (POST /validate)
3. **Server-side validation** before save
4. **Error handling** and user feedback
5. **Basic testing** (unit + feature tests)

### P1 (High - Should Have)
1. **Flow preview/simulation**
2. **Field validation rules UI**
3. **Auto-save** functionality
4. **Loading states** for all operations
5. **E2E tests** for critical flows

### P2 (Medium - Nice to Have)
1. **Undo/Redo** support
2. **Keyboard shortcuts**
3. **Edge configuration UI**
4. **Responsive preview modes** (actual rendering)
5. **Computed/Lookup bindings**
6. **Performance optimization**

### P3 (Low - Future)
1. **Onboarding tutorial**
2. **Help documentation**
3. **Code splitting**
4. **Offline mode**
5. **Collaborative editing**

---

## Recommended Implementation Order

### Phase 1: Critical Fixes (1-2 weeks)
**Goal:** Make builders production-ready

1. **Week 1: Validation**
   - [ ] Create `FlowValidator` service
   - [ ] Create `PageValidator` service
   - [ ] Add validation API endpoints
   - [ ] Integrate validation into save flow
   - [ ] Add validation error display in UI

2. **Week 2: Testing & Error Handling**
   - [ ] Write unit tests for validators
   - [ ] Write feature tests for save/load
   - [ ] Add comprehensive error handling
   - [ ] Add loading states
   - [ ] Add user feedback (toasts)

### Phase 2: User Experience (1 week)
**Goal:** Improve usability

3. **Week 3: UX Improvements**
   - [ ] Add auto-save (30s interval)
   - [ ] Add flow preview modal
   - [ ] Add field validation rules UI
   - [ ] Add keyboard shortcuts
   - [ ] Add tooltips

### Phase 3: Polish (1 week)
**Goal:** Production polish

4. **Week 4: Polish & Documentation**
   - [ ] Write E2E tests
   - [ ] Performance testing
   - [ ] User documentation
   - [ ] Developer documentation
   - [ ] Bug fixes

**Total Estimated Time:** 4 weeks to complete all critical gaps

---

## Risk Assessment

### High Risk
1. **No validation** - Users can save broken flows/pages
   - **Mitigation:** Implement validation ASAP (Phase 1)

2. **No testing** - Regressions likely
   - **Mitigation:** Add tests in Phase 1

3. **No error handling** - Poor user experience
   - **Mitigation:** Add error handling in Phase 1

### Medium Risk
1. **No auto-save** - Data loss possible
   - **Mitigation:** Add auto-save in Phase 2

2. **No preview** - Users can't test flows
   - **Mitigation:** Add preview in Phase 2

### Low Risk
1. **No undo/redo** - Inconvenient but not critical
   - **Mitigation:** Add in Phase 3 or later

2. **No onboarding** - Learning curve steep
   - **Mitigation:** Add documentation in Phase 3

---

## Acceptance Criteria Status

### AC-1: Flow Builder Complete
- [x] User can create new flow from scratch
- [x] User can add all 9 node types
- [x] User can connect nodes with edges
- [x] User can configure each node type
- [ ] User can validate flow ⚠️ **MISSING**
- [x] User can save flow
- [x] User can load existing flow
- [x] User can delete nodes/edges
- [ ] Saved flow executes correctly in runtime ⚠️ **UNTESTED**
- [ ] All tests passing ⚠️ **MISSING**

**Status:** 7/10 complete (70%)

### AC-2: Page Builder Complete
- [x] User can create new page from scratch
- [x] User can add steps
- [x] User can add all 15+ field types
- [x] User can configure each field
- [x] User can set data bindings
- [ ] User can validate page ⚠️ **MISSING**
- [x] User can save page
- [x] User can load existing page
- [x] User can delete fields/steps
- [ ] Saved page renders correctly for branch users ⚠️ **UNTESTED**
- [ ] All tests passing ⚠️ **MISSING**

**Status:** 7/10 complete (70%)

### AC-3: Integration Complete
- [x] Flow Builder integrates with FlowCanvasProxy
- [x] Page Builder integrates with PageBuilderProxy
- [x] Both builders save to correct registry tables
- [x] Both builders load from registry tables
- [x] Both builders respect feature versioning
- [ ] Both builders work with existing runtime engine ⚠️ **UNTESTED**

**Status:** 5/6 complete (83%)

**Overall Completion:** ~73%

---

## Conclusion

The Visual Builders are **functionally complete** for basic use cases, but **not production-ready** due to:

1. **No validation** - Critical gap
2. **No testing** - Critical gap
3. **Limited error handling** - High priority gap
4. **No auto-save** - Medium priority gap
5. **No preview** - Medium priority gap

**Recommendation:** Complete Phase 1 (Critical Fixes) before releasing to production. Phases 2-3 can be done post-launch.

**Estimated Time to Production-Ready:** 2 weeks (Phase 1 only)  
**Estimated Time to Full Completion:** 4 weeks (All phases)

---

**Next Steps:**
1. Review this gap analysis with team
2. Prioritize missing features
3. Create implementation tasks for Phase 1
4. Begin validation service implementation
