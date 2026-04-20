# Scope Overrides Engine - Requirements Document

**Feature Name:** Scope Overrides Engine (Branch/Product Customization)  
**Priority:** HIGH (P1)  
**Estimated Effort:** 3-4 weeks  
**Dependencies:** Registry models (✅ Complete), Runtime engine (✅ Complete)  
**Status:** Draft

---

## 1. Executive Summary

### Problem Statement
Currently, **ALL BRANCHES USE THE SAME CONFIGURATION**:
- ✅ `ScopeOverride` model exists
- ❌ **NO RESOLUTION ENGINE** - no logic to resolve precedence
- ❌ **NO UI** to manage overrides
- ❌ **NO RUNTIME INTEGRATION** - runtime doesn't read overrides

This means:
- Branch Johor cannot require nominee while Branch KL makes it optional
- Product A cannot have different LTV rules than Product B
- Entity-specific customization is impossible

### Solution Overview
Build scope overrides engine with 4 components:
1. **Override Resolution Engine** - Resolve precedence (platform < entity < branch < product)
2. **Override Management UI** - Create/edit/delete overrides
3. **Runtime Integration** - Runtime reads and applies overrides
4. **Override Validation** - Ensure overrides don't break system

### Success Criteria
- ✅ Branch A and Branch B can have different configurations
- ✅ Product A and Product B can have different rules
- ✅ Overrides follow clear precedence
- ✅ Runtime applies overrides correctly
- ✅ Overrides are auditable

---

## 2. User Stories

### Epic 1: Override Management

#### US-OM-001: Create Branch Override
**As a** HQ Admin  
**I want to** create branch-specific overrides  
**So that** different branches can have different rules

**Acceptance Criteria:**
- "Overrides" section in Feature Workspace
- "Add Override" button
- Select scope type: Branch, Product, Entity, Region
- Select scope ID (e.g., Branch Johor)
- Select what to override:
  - Field visibility
  - Field requirement
  - Validation rules
  - Flow nodes
  - Approval tiers
- Set override value
- Save override

#### US-OM-002: View Overrides
**As a** HQ Admin  
**I want to** see all overrides for a feature  
**So that** I can understand customizations

**Acceptance Criteria:**
- Overrides list shows:
  - Scope type and ID
  - What is overridden
  - Override value
  - Effective date range
  - Created by
  - Status (active/inactive)
- Can filter by scope type
- Can search by scope ID

#### US-OM-003: Edit Override
**As a** HQ Admin  
**I want to** edit existing overrides  
**So that** I can update customizations

**Acceptance Criteria:**
- Click override to edit
- Can change override value
- Can change effective date range
- Can activate/deactivate
- Save changes
- Version history tracked

#### US-OM-004: Delete Override
**As a** HQ Admin  
**I want to** delete overrides  
**So that** I can remove customizations

**Acceptance Criteria:**
- Click delete button
- Confirmation dialog
- Override soft-deleted (not hard-deleted)
- Audit trail preserved

#### US-OM-005: Test Override
**As a** HQ Admin  
**I want to** test overrides before activating  
**So that** I can verify they work

**Acceptance Criteria:**
- "Test Override" button
- Select test branch/product
- Shows preview of how feature will look
- Shows which overrides apply
- Shows final configuration after resolution

### Epic 2: Override Resolution

#### US-OR-001: Resolve Precedence
**As a** System  
**I want to** resolve override precedence correctly  
**So that** the right configuration is used

**Acceptance Criteria:**
- Precedence order (lowest to highest):
  1. Platform default
  2. Entity override
  3. Region override
  4. Branch override
  5. Product override
- Higher precedence wins
- Multiple overrides merge correctly
- Resolution is deterministic

#### US-OR-002: Handle Conflicts
**As a** System  
**I want to** handle conflicting overrides  
**So that** system doesn't break

**Acceptance Criteria:**
- If two overrides at same level conflict, use most recent
- If override makes feature invalid, reject override
- If override breaks dependencies, show warning
- Log all conflicts

#### US-OR-003: Cache Resolution
**As a** System  
**I want to** cache resolved configurations  
**So that** performance is good

**Acceptance Criteria:**
- Resolved config cached per branch/product
- Cache invalidates when override changes
- Cache TTL: 1 hour
- Cache hit rate > 90%

### Epic 3: Runtime Integration

#### US-RI-001: Load Overrides at Runtime
**As a** Runtime Engine  
**I want to** load overrides when rendering features  
**So that** branch-specific config is used

**Acceptance Criteria:**
- When loading feature for branch user:
  - Get user's branch ID
  - Get user's product context (if applicable)
  - Load platform default config
  - Load applicable overrides
  - Resolve precedence
  - Apply overrides to config
  - Render with final config

#### US-RI-002: Apply Field Overrides
**As a** Runtime Engine  
**I want to** apply field-level overrides  
**So that** fields behave differently per branch

**Acceptance Criteria:**
- Override field visibility (show/hide)
- Override field requirement (required/optional)
- Override field validation rules
- Override field default values
- Override field help text

#### US-RI-003: Apply Flow Overrides
**As a** Runtime Engine  
**I want to** apply flow-level overrides  
**So that** flows behave differently per branch

**Acceptance Criteria:**
- Override node configuration
- Override edge conditions
- Override approval tiers
- Override notification templates
- Skip/add nodes based on override

#### US-RI-004: Apply Rule Overrides
**As a** Runtime Engine  
**I want to** apply rule-level overrides  
**So that** business rules differ per branch

**Acceptance Criteria:**
- Override LTV percentage
- Override approval thresholds
- Override fee calculations
- Override eligibility criteria

---

## 3. Functional Requirements

### FR-1: Override Data Model

#### FR-1.1: Scope Types
- **MUST** support 5 scope types:
  1. `platform` - System-wide default (no override)
  2. `entity` - Entity-level (e.g., Company A vs Company B)
  3. `region` - Region-level (e.g., Northern Region vs Southern Region)
  4. `branch` - Branch-level (e.g., Branch Johor vs Branch KL)
  5. `product` - Product-level (e.g., Gold Plus vs Gold Standard)

#### FR-1.2: Override Targets
- **MUST** support overriding:
  - **Field properties:** visibility, requirement, validation, default value
  - **Flow nodes:** config, skip/add nodes
  - **Rules:** conditions, results
  - **Formulas:** expressions, parameters
  - **Permissions:** role access
  - **Menu items:** visibility, order

#### FR-1.3: Override Storage
- **MUST** use existing `scoped_overrides` table:
```sql
CREATE TABLE scoped_overrides (
  id BIGINT PRIMARY KEY,
  feature_version_id BIGINT FK,
  scope_type ENUM('entity','region','branch','product'),
  scope_id VARCHAR,
  target_table VARCHAR,
  target_key VARCHAR,
  override_value JSON,
  effective_from DATE,
  effective_to DATE NULLABLE,
  is_active BOOLEAN,
  created_by BIGINT FK,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

### FR-2: Resolution Engine

#### FR-2.1: Precedence Algorithm
- **MUST** implement precedence resolution:
```
1. Start with platform default config
2. Apply entity overrides (if any)
3. Apply region overrides (if any)
4. Apply branch overrides (if any)
5. Apply product overrides (if any)
6. Return final config
```

#### FR-2.2: Merge Strategy
- **MUST** support merge strategies:
  - **Replace:** Override completely replaces default
  - **Merge:** Override merges with default (for objects)
  - **Append:** Override appends to default (for arrays)
  - **Remove:** Override removes from default

#### FR-2.3: Conflict Resolution
- **MUST** handle conflicts:
  - Same-level conflicts: use most recent
  - Invalid overrides: reject with error
  - Breaking overrides: warn but allow (with confirmation)

#### FR-2.4: Caching
- **MUST** cache resolved configs:
  - Cache key: `feature:{feature_id}:scope:{scope_type}:{scope_id}`
  - Cache TTL: 1 hour
  - Invalidate on override change
  - Invalidate on feature publish

### FR-3: Override Management UI

#### FR-3.1: Override List
- **MUST** show all overrides for feature
- **MUST** support filtering by scope type
- **MUST** support searching by scope ID
- **MUST** show override status (active/inactive)
- **MUST** show effective date range

#### FR-3.2: Override Form
- **MUST** provide form to create/edit override:
  - Scope type dropdown
  - Scope ID selector (dynamic based on type)
  - Target selector (what to override)
  - Override value editor (JSON or form)
  - Effective date range
  - Active/inactive toggle

#### FR-3.3: Override Preview
- **MUST** show preview of override effect:
  - Before: platform default
  - After: with override applied
  - Diff view showing changes

#### FR-3.4: Override Validation
- **MUST** validate override before save:
  - Scope ID exists
  - Target exists
  - Override value is valid JSON
  - Override doesn't break feature
  - Override doesn't conflict with others (warn if does)

### FR-4: Runtime Integration

#### FR-4.1: Context Resolution
- **MUST** resolve user context at runtime:
  - Get user's entity ID
  - Get user's branch ID
  - Get user's region (from branch)
  - Get product context (from current operation)

#### FR-4.2: Config Loading
- **MUST** load config with overrides:
```php
$config = OverrideResolver::resolve(
    featureVersionId: $featureVersionId,
    entityId: $user->entity_id,
    branchId: $user->branch_id,
    productCode: $context['product_code'] ?? null
);
```

#### FR-4.3: Dynamic Rendering
- **MUST** render UI with resolved config:
  - Hide fields if override says hidden
  - Make fields required if override says required
  - Use override validation rules
  - Use override default values

#### FR-4.4: Flow Execution
- **MUST** execute flows with resolved config:
  - Skip nodes if override says skip
  - Use override node config
  - Use override edge conditions
  - Use override approval tiers

---

## 4. Non-Functional Requirements

### NFR-1: Performance
- **MUST** resolve overrides in < 50ms
- **MUST** cache resolved configs
- **MUST** not impact page load significantly
- **SHOULD** preload overrides for common branches

### NFR-2: Scalability
- **MUST** support 100+ branches
- **MUST** support 50+ products
- **MUST** support 1000+ overrides per feature
- **SHOULD** support 10,000+ total overrides

### NFR-3: Auditability
- **MUST** log all override changes
- **MUST** track who created/modified overrides
- **MUST** track when overrides were applied
- **MUST** support override history

---

## 5. API Requirements

### API-1: Override Management

#### GET /api/studio/features/{featureId}/overrides
**Purpose:** List all overrides  
**Response:**
```json
{
  "overrides": [
    {
      "id": 1,
      "scope_type": "branch",
      "scope_id": "branch_johor",
      "target_table": "form_fields",
      "target_key": "nominee_required",
      "override_value": {"is_required": true},
      "is_active": true
    }
  ]
}
```

#### POST /api/studio/features/{featureId}/overrides
**Purpose:** Create override  
**Request:**
```json
{
  "scope_type": "branch",
  "scope_id": "branch_johor",
  "target_table": "form_fields",
  "target_key": "nominee_required",
  "override_value": {"is_required": true}
}
```

#### PUT /api/studio/overrides/{overrideId}
**Purpose:** Update override

#### DELETE /api/studio/overrides/{overrideId}
**Purpose:** Delete override

### API-2: Override Resolution

#### POST /api/runtime/resolve-config
**Purpose:** Resolve config with overrides  
**Request:**
```json
{
  "feature_version_id": 1,
  "entity_id": "entity_1",
  "branch_id": "branch_johor",
  "product_code": "GOLD_PLUS"
}
```
**Response:**
```json
{
  "resolved_config": {
    "fields": [...],
    "flows": [...],
    "rules": [...]
  },
  "applied_overrides": [1, 5, 12]
}
```

---

## 6. Examples

### Example 1: Nominee Field Override

**Platform Default:**
```json
{
  "field_key": "nominee",
  "is_required": false,
  "is_visible": true
}
```

**Branch Johor Override:**
```json
{
  "scope_type": "branch",
  "scope_id": "branch_johor",
  "target_table": "form_fields",
  "target_key": "nominee",
  "override_value": {
    "is_required": true
  }
}
```

**Resolved Config for Branch Johor:**
```json
{
  "field_key": "nominee",
  "is_required": true,  // overridden
  "is_visible": true
}
```

### Example 2: LTV Rule Override

**Platform Default:**
```json
{
  "rule_key": "ltv_calculation",
  "max_ltv_percentage": 70
}
```

**Product Gold Plus Override:**
```json
{
  "scope_type": "product",
  "scope_id": "GOLD_PLUS",
  "target_table": "rule_sets",
  "target_key": "ltv_calculation",
  "override_value": {
    "max_ltv_percentage": 75
  }
}
```

**Resolved Config for Gold Plus:**
```json
{
  "rule_key": "ltv_calculation",
  "max_ltv_percentage": 75  // overridden
}
```

---

## 7. Success Metrics

- ✅ 90% of branches use at least one override
- ✅ Override resolution time < 50ms
- ✅ Cache hit rate > 90%
- ✅ 0 override-related runtime errors
- ✅ Override management UI used weekly

---

## 8. Timeline Estimate

- Week 1: Resolution engine + caching
- Week 2: Override management UI
- Week 3: Runtime integration
- Week 4: Testing + documentation

**Total: 3-4 weeks**

---

**Document Status:** Draft  
**Last Updated:** 20 April 2026  
**Author:** Kiro AI
