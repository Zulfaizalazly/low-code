# Publish Workflow - Requirements Document

**Feature Name:** Publish Workflow (Approval + Impact Analysis + Simulation)  
**Priority:** CRITICAL (P0)  
**Estimated Effort:** 4-6 weeks  
**Dependencies:** Visual Builders (in progress), Registry models (✅ Complete)  
**Status:** Draft

---

## 1. Executive Summary

### Problem Statement
Currently, features can be "published" but there is **NO GOVERNANCE WORKFLOW**:
- ✅ `PublishGateValidator` exists (14 validation checks)
- ✅ `VersionPublisher` exists (basic publish logic)
- ❌ **NO APPROVAL WORKFLOW** - no state machine, no UI
- ❌ **NO IMPACT ANALYSIS ENGINE** - `ImpactAnalyzer` is empty
- ❌ **NO SIMULATION UI** - `FlowSimulator` is skeleton only
- ❌ **NO RELEASE CENTER** - no UI to manage releases

This means features can go live without proper review, risk assessment, or testing.

### Solution Overview
Build complete publish workflow with 4 components:
1. **Approval Workflow** - Submit → Review → Approve → Publish state machine
2. **Impact Analysis** - Analyze affected branches, roles, documents before publish
3. **Simulation Engine** - Test flows with sample data before publish
4. **Release Center UI** - Manage drafts, reviews, publishes, rollbacks

### Success Criteria
- ✅ Features cannot publish without approval
- ✅ Impact analysis shows affected branches/roles/documents
- ✅ Simulation runs flows with sample data
- ✅ Release Center shows all publish history
- ✅ Rollback mechanism works correctly

---

## 2. User Stories

### Epic 1: Approval Workflow

#### US-AW-001: Submit Feature for Review
**As a** HQ Designer  
**I want to** submit my feature for review  
**So that** it can be approved before going live

**Acceptance Criteria:**
- "Submit for Review" button in Feature Workspace
- Button only enabled if all 14 validation checks pass
- Clicking button changes status to "in_review"
- Notification sent to reviewers
- Designer cannot edit feature while in review

#### US-AW-002: Review Feature
**As a** HQ Reviewer  
**I want to** review submitted features  
**So that** I can approve or reject them

**Acceptance Criteria:**
- "Pending Reviews" section in Control Tower
- Click feature to open review screen
- Review screen shows:
  - Feature details
  - Flow diagram (read-only)
  - Page preview (read-only)
  - Validation results (all 14 checks)
  - Impact analysis report
  - Simulation results
- "Approve" and "Reject" buttons
- Reject requires reason/comments

#### US-AW-003: Approve Feature
**As a** HQ Reviewer  
**I want to** approve a feature  
**So that** it can be published

**Acceptance Criteria:**
- Click "Approve" button
- Confirmation dialog
- Status changes to "approved"
- Notification sent to designer
- Feature ready for publish

#### US-AW-004: Reject Feature
**As a** HQ Reviewer  
**I want to** reject a feature  
**So that** designer can fix issues

**Acceptance Criteria:**
- Click "Reject" button
- Modal asks for rejection reason
- Status changes back to "draft"
- Notification sent to designer with reason
- Designer can edit and re-submit

#### US-AW-005: Publish Approved Feature
**As a** HQ Admin  
**I want to** publish approved features  
**So that** they go live for branch users

**Acceptance Criteria:**
- "Publish" button only visible for approved features
- Confirmation dialog with impact summary
- Publish creates immutable snapshot
- Status changes to "published"
- Feature appears in branch user sidebar
- Notification sent to affected branches

### Epic 2: Impact Analysis

#### US-IA-001: Analyze Affected Branches
**As a** HQ Reviewer  
**I want to** see which branches will be affected  
**So that** I can assess deployment risk

**Acceptance Criteria:**
- Impact analysis runs automatically on submit
- Shows list of affected branches:
  - All branches (if platform-level)
  - Specific branches (if branch-level)
  - Branches with overrides
- Shows count: "Affects 15 branches"

#### US-IA-002: Analyze Affected Roles
**As a** HQ Reviewer  
**I want to** see which roles will see the feature  
**So that** I can verify permissions

**Acceptance Criteria:**
- Shows list of roles with access:
  - branch_staff
  - branch_manager
  - regional_manager
  - etc.
- Shows permission level (create, read, approve)
- Highlights new permissions

#### US-IA-003: Analyze Affected Documents
**As a** HQ Reviewer  
**I want to** see which documents will be generated  
**So that** I can verify templates exist

**Acceptance Criteria:**
- Shows list of document templates used
- Shows if templates exist or missing
- Shows sample document preview

#### US-IA-004: Analyze Affected Reports
**As a** HQ Reviewer  
**I want to** see which reports might be affected  
**So that** I can plan report updates

**Acceptance Criteria:**
- Shows list of reports that query affected entities
- Shows if report queries need updates
- Shows risk level (low, medium, high)

#### US-IA-005: Risk Level Assessment
**As a** HQ Reviewer  
**I want to** see overall risk level  
**So that** I can decide approval

**Acceptance Criteria:**
- Risk level calculated based on:
  - Number of affected branches (more = higher risk)
  - Number of affected users (more = higher risk)
  - Breaking changes (yes = high risk)
  - New permissions (yes = medium risk)
- Risk shown as: Low, Medium, High, Critical
- Risk color-coded (green, yellow, orange, red)

### Epic 3: Simulation Engine

#### US-SE-001: Run Flow Simulation
**As a** HQ Designer  
**I want to** simulate my flow with test data  
**So that** I can verify it works before publish

**Acceptance Criteria:**
- "Simulate" button in Feature Workspace
- Modal asks for test data:
  - Customer IC number
  - Gold weight
  - Gold purity
  - Loan amount
  - etc.
- Click "Run Simulation"
- Shows node-by-node execution
- Shows output at each node
- Shows final result

#### US-SE-002: View Simulation Results
**As a** HQ Designer  
**I want to** see detailed simulation results  
**So that** I can debug issues

**Acceptance Criteria:**
- Results show:
  - Each node executed
  - Input data for each node
  - Output data for each node
  - Execution time for each node
  - Success/failure status
- Failed nodes highlighted in red
- Error messages shown

#### US-SE-003: Preview Generated Documents
**As a** HQ Designer  
**I want to** preview documents in simulation  
**So that** I can verify templates

**Acceptance Criteria:**
- Document nodes show "Preview" button
- Click to see rendered document
- Document uses test data
- Can download PDF preview

#### US-SE-004: Preview Notifications
**As a** HQ Designer  
**I want to** preview notifications in simulation  
**So that** I can verify message content

**Acceptance Criteria:**
- Notification nodes show "Preview" button
- Click to see message content
- Shows recipient, subject, body
- Variables interpolated with test data

#### US-SE-005: Simulation History
**As a** HQ Designer  
**I want to** see past simulation runs  
**So that** I can compare results

**Acceptance Criteria:**
- "Simulation History" tab
- Shows list of past runs with timestamp
- Click to view results
- Can re-run with same data

### Epic 4: Release Center

#### US-RC-001: View Draft Releases
**As a** HQ Admin  
**I want to** see all draft releases  
**So that** I can track what's in progress

**Acceptance Criteria:**
- "Release Center" in HQ Studio sidebar
- "Drafts" tab shows:
  - Feature name
  - Designer
  - Last updated
  - Completion %
  - Status badge
- Click to open feature

#### US-RC-002: View Pending Reviews
**As a** HQ Reviewer  
**I want to** see all pending reviews  
**So that** I can prioritize my work

**Acceptance Criteria:**
- "Pending Reviews" tab shows:
  - Feature name
  - Submitted by
  - Submitted date
  - Days pending
  - Risk level
- Sorted by priority (high risk first)
- Click to open review screen

#### US-RC-003: View Published Releases
**As a** HQ Admin  
**I want to** see all published releases  
**So that** I can track what's live

**Acceptance Criteria:**
- "Published" tab shows:
  - Feature name
  - Version number
  - Published by
  - Published date
  - Affected branches count
- Click to view details

#### US-RC-004: View Rollback History
**As a** HQ Admin  
**I want to** see rollback history  
**So that** I can track issues

**Acceptance Criteria:**
- "Rollbacks" tab shows:
  - Feature name
  - Rolled back from version
  - Rolled back to version
  - Rolled back by
  - Rolled back date
  - Reason
- Click to view details

#### US-RC-005: Rollback Feature
**As a** HQ Admin  
**I want to** rollback a published feature  
**So that** I can revert problematic changes

**Acceptance Criteria:**
- "Rollback" button on published feature
- Modal asks for reason
- Confirmation dialog with impact warning
- Rollback reverts to previous version
- Status changes to "rolled_back"
- Runtime immediately uses previous version
- Notification sent to affected branches

---

## 3. Functional Requirements

### FR-1: Approval Workflow State Machine

#### FR-1.1: Feature States
- **MUST** support 6 states:
  1. `draft` - Initial state, editable
  2. `in_review` - Submitted, read-only
  3. `approved` - Approved, ready to publish
  4. `published` - Live, immutable
  5. `archived` - Deprecated, hidden
  6. `rolled_back` - Reverted, read-only

#### FR-1.2: State Transitions
- **MUST** enforce valid transitions:
  - `draft` → `in_review` (submit)
  - `in_review` → `approved` (approve)
  - `in_review` → `draft` (reject)
  - `approved` → `published` (publish)
  - `published` → `rolled_back` (rollback)
  - `rolled_back` → `draft` (re-edit)
  - Any → `archived` (archive)

#### FR-1.3: Permissions
- **MUST** enforce role-based permissions:
  - Designer: can edit `draft`, submit for review
  - Reviewer: can approve/reject `in_review`
  - Admin: can publish `approved`, rollback `published`
  - Auditor: can view all, edit none

### FR-2: Impact Analysis Engine

#### FR-2.1: Branch Analysis
- **MUST** identify affected branches based on:
  - Feature scope level (platform, entity, branch)
  - Scope overrides
  - Menu visibility rules
- **MUST** calculate branch count
- **MUST** list branch names

#### FR-2.2: Role Analysis
- **MUST** identify roles with access from `feature_permissions`
- **MUST** show permission level (create, read, approve, etc.)
- **MUST** highlight new permissions vs existing

#### FR-2.3: Document Analysis
- **MUST** identify document templates from flow nodes
- **MUST** verify templates exist in `document_templates`
- **MUST** flag missing templates as high risk

#### FR-2.4: Report Analysis
- **MUST** identify reports querying affected entities
- **MUST** flag reports that may need updates
- **SHOULD** suggest report changes (nice-to-have)

#### FR-2.5: Risk Calculation
- **MUST** calculate risk level:
  - **Low:** < 5 branches, no breaking changes, existing permissions
  - **Medium:** 5-20 branches, minor changes, new permissions
  - **High:** 20-50 branches, breaking changes, new entities
  - **Critical:** > 50 branches, major breaking changes, system-wide impact

### FR-3: Simulation Engine

#### FR-3.1: Flow Execution
- **MUST** execute flow in simulation mode
- **MUST** use test data provided by user
- **MUST** execute all node types
- **MUST** log each node execution
- **MUST** handle errors gracefully

#### FR-3.2: Simulation Mode
- **MUST** set `simulation: true` flag in execution context
- **MUST** skip actual side-effects:
  - Don't send real SMS/emails
  - Don't create real database records
  - Don't post real GL entries
  - Don't create real documents (generate preview only)
- **MUST** return simulated outputs

#### FR-3.3: Result Logging
- **MUST** log to separate `simulation_logs` table
- **MUST** store:
  - Simulation ID
  - Feature version ID
  - Test data used
  - Node-by-node results
  - Final outcome
  - Timestamp
  - User who ran simulation

### FR-4: Release Center UI

#### FR-4.1: Dashboard
- **MUST** show summary cards:
  - Draft features count
  - Pending reviews count
  - Published features count
  - Failed simulations count

#### FR-4.2: Feature Lists
- **MUST** show filterable lists:
  - Filter by status
  - Filter by designer
  - Filter by date range
  - Search by name
- **MUST** support sorting:
  - By date (newest first)
  - By name (A-Z)
  - By risk level (high first)

#### FR-4.3: Feature Details
- **MUST** show complete feature information:
  - Metadata (name, description, domain)
  - Flow diagram (read-only)
  - Page preview (read-only)
  - Validation results
  - Impact analysis
  - Simulation history
  - Publish history
  - Rollback history

---

## 4. Non-Functional Requirements

### NFR-1: Performance
- **MUST** run impact analysis in < 5 seconds
- **MUST** run simulation in < 10 seconds
- **MUST** load Release Center in < 2 seconds
- **SHOULD** support real-time updates (WebSocket)

### NFR-2: Reliability
- **MUST** handle concurrent approvals gracefully
- **MUST** prevent double-publish
- **MUST** ensure rollback atomicity
- **MUST** log all state transitions

### NFR-3: Auditability
- **MUST** log all approval actions
- **MUST** log all publish actions
- **MUST** log all rollback actions
- **MUST** store approval comments
- **MUST** store rollback reasons

---

## 5. API Requirements

### API-1: Approval Workflow

#### POST /api/studio/features/{featureId}/submit
**Purpose:** Submit feature for review  
**Response:**
```json
{
  "success": true,
  "status": "in_review",
  "reviewers_notified": ["reviewer@example.com"]
}
```

#### POST /api/studio/features/{featureId}/approve
**Purpose:** Approve feature  
**Request:**
```json
{
  "comments": "Looks good, approved"
}
```

#### POST /api/studio/features/{featureId}/reject
**Purpose:** Reject feature  
**Request:**
```json
{
  "reason": "Missing nominee validation"
}
```

#### POST /api/studio/features/{featureId}/publish
**Purpose:** Publish approved feature  
**Response:**
```json
{
  "success": true,
  "version": 2,
  "published_at": "2026-04-20T10:00:00Z"
}
```

### API-2: Impact Analysis

#### GET /api/studio/features/{featureId}/impact-analysis
**Purpose:** Get impact analysis report  
**Response:**
```json
{
  "affected_branches": ["Branch A", "Branch B"],
  "affected_roles": ["branch_staff", "branch_manager"],
  "affected_documents": ["pledge_agreement"],
  "affected_reports": ["daily_pledge_report"],
  "risk_level": "medium"
}
```

### API-3: Simulation

#### POST /api/studio/features/{featureId}/simulate
**Purpose:** Run flow simulation  
**Request:**
```json
{
  "test_data": {
    "customer_ic": "900101011234",
    "gold_weight": 50.5,
    "gold_purity": 916,
    "loan_amount": 5000
  }
}
```
**Response:**
```json
{
  "simulation_id": 123,
  "status": "success",
  "nodes_executed": 8,
  "execution_time_ms": 1250,
  "results": [...]
}
```

---

## 6. Database Schema Changes

### New Tables

#### `approval_workflows`
```sql
CREATE TABLE approval_workflows (
  id BIGINT PRIMARY KEY,
  feature_version_id BIGINT FK,
  submitted_by BIGINT FK,
  submitted_at TIMESTAMP,
  reviewed_by BIGINT FK,
  reviewed_at TIMESTAMP,
  decision ENUM('approved', 'rejected'),
  comments TEXT,
  created_at TIMESTAMP
);
```

#### `simulation_logs`
```sql
CREATE TABLE simulation_logs (
  id BIGINT PRIMARY KEY,
  feature_version_id BIGINT FK,
  test_data JSON,
  results JSON,
  status ENUM('success', 'failed'),
  executed_by BIGINT FK,
  executed_at TIMESTAMP,
  created_at TIMESTAMP
);
```

#### `impact_analysis_reports` (already exists, needs population)
```sql
-- Table exists, just needs engine to populate it
```

---

## 7. Success Metrics

- ✅ 100% of features go through approval before publish
- ✅ 90% of features pass simulation before submit
- ✅ 80% reduction in post-publish issues
- ✅ Impact analysis accuracy > 95%
- ✅ Average approval time < 24 hours

---

## 8. Timeline Estimate

- Week 1-2: Approval workflow state machine + UI
- Week 3: Impact analysis engine
- Week 4: Simulation engine + UI
- Week 5: Release Center UI
- Week 6: Testing + bug fixes

**Total: 4-6 weeks**

---

**Document Status:** Draft  
**Last Updated:** 20 April 2026  
**Author:** Kiro AI
