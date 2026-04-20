# Security Layer - Requirements Document

**Feature Name:** Security Layer (Permissions + CSRF + Authentication)  
**Priority:** CRITICAL (P0)  
**Estimated Effort:** 2-3 weeks  
**Dependencies:** None (foundational)  
**Status:** Draft

---

## 1. Executive Summary

### Problem Statement
The system is currently **VULNERABLE**:
- ❌ **NO PERMISSION CHECKS** on routes
- ❌ **NO CSRF PROTECTION** on forms
- ❌ **NO POLICY CLASSES** for authorization
- ❌ **NO ROLE SEEDING** - roles/permissions not setup
- ❌ **NO INPUT SANITIZATION** - XSS vulnerable

This is a **CRITICAL SECURITY RISK** that must be fixed before any production deployment.

### Solution Overview
Implement comprehensive security layer with 5 components:
1. **Route Protection** - Middleware on all routes
2. **CSRF Protection** - Token validation on all forms
3. **Policy-Based Authorization** - Laravel policies for all models
4. **Role & Permission System** - Proper RBAC setup
5. **Input Sanitization** - XSS prevention

### Success Criteria
- ✅ All routes require authentication
- ✅ All routes check permissions
- ✅ All forms have CSRF protection
- ✅ All models have policies
- ✅ All inputs sanitized
- ✅ Security audit passes

---

## 2. User Stories

### Epic 1: Authentication & Authorization

#### US-AA-001: Enforce Authentication
**As a** System Administrator  
**I want** all routes to require authentication  
**So that** unauthorized users cannot access the system

**Acceptance Criteria:**
- All routes except login require authentication
- Unauthenticated users redirected to login
- Session timeout after 2 hours of inactivity
- "Remember me" option extends session to 30 days

#### US-AA-002: Role-Based Access Control
**As a** System Administrator  
**I want** users to have roles with specific permissions  
**So that** users only access what they're allowed to

**Acceptance Criteria:**
- 6 roles defined:
  - Platform Owner (full access)
  - HQ Designer (design features)
  - HQ Reviewer (review features)
  - Branch Manager (approve operations)
  - Branch Staff (use features)
  - Auditor (read-only access)
- Each role has specific permissions
- Users can have multiple roles
- Permissions checked on every action

#### US-AA-003: Policy-Based Authorization
**As a** Developer  
**I want** Laravel policies for all models  
**So that** authorization is consistent

**Acceptance Criteria:**
- Policy for each model:
  - FeaturePolicy
  - FlowDefinitionPolicy
  - PageDefinitionPolicy
  - CustomerPolicy
  - FacilityPolicy
  - etc.
- Policies check user role and ownership
- Policies used in controllers and views

### Epic 2: CSRF Protection

#### US-CSRF-001: CSRF Tokens on Forms
**As a** Security Engineer  
**I want** all forms to have CSRF tokens  
**So that** CSRF attacks are prevented

**Acceptance Criteria:**
- All Livewire forms have @csrf directive
- All Blade forms have @csrf directive
- All AJAX requests include CSRF token
- Invalid tokens rejected with 419 error

#### US-CSRF-002: CSRF Token Refresh
**As a** User  
**I want** CSRF tokens to refresh automatically  
**So that** I don't get errors on long sessions

**Acceptance Criteria:**
- Token refreshes every 30 minutes
- Token refresh doesn't interrupt user work
- Expired tokens show friendly error message

### Epic 3: Input Sanitization

#### US-IS-001: XSS Prevention
**As a** Security Engineer  
**I want** all user inputs sanitized  
**So that** XSS attacks are prevented

**Acceptance Criteria:**
- All text inputs escaped in Blade views
- All rich text sanitized (strip dangerous tags)
- All JSON inputs validated
- All file uploads validated (type, size, content)

#### US-IS-002: SQL Injection Prevention
**As a** Security Engineer  
**I want** all database queries parameterized  
**So that** SQL injection is prevented

**Acceptance Criteria:**
- All queries use Eloquent or Query Builder
- No raw SQL with user input
- All where clauses use bindings
- All dynamic queries validated

---

## 3. Functional Requirements

### FR-1: Route Protection

#### FR-1.1: Authentication Middleware
- **MUST** apply `auth` middleware to all routes except:
  - `/login`
  - `/logout`
  - `/password/reset`
- **MUST** redirect unauthenticated users to login
- **MUST** preserve intended URL for redirect after login

#### FR-1.2: Permission Middleware
- **MUST** create custom middleware: `CheckPermission`
- **MUST** check user has required permission for route
- **MUST** return 403 Forbidden if permission denied
- **MUST** log unauthorized access attempts

#### FR-1.3: Route Groups
- **MUST** group routes by permission level:
  - `studio.*` - requires `access_studio` permission
  - `features.*` - requires `manage_features` permission
  - `runtime.*` - requires `use_features` permission
  - `admin.*` - requires `admin_access` permission

### FR-2: CSRF Protection

#### FR-2.1: Token Generation
- **MUST** generate CSRF token on login
- **MUST** store token in session
- **MUST** include token in all forms
- **MUST** include token in AJAX headers

#### FR-2.2: Token Validation
- **MUST** validate token on all POST/PUT/DELETE requests
- **MUST** reject requests with invalid tokens
- **MUST** return 419 error for token mismatch
- **MUST** log CSRF failures

#### FR-2.3: Token Refresh
- **MUST** refresh token every 30 minutes
- **MUST** update token in session and forms
- **SHOULD** use JavaScript to refresh without page reload

### FR-3: Policy-Based Authorization

#### FR-3.1: Policy Classes
- **MUST** create policy for each model:
  - `FeaturePolicy` - viewAny, view, create, update, delete, publish
  - `FlowDefinitionPolicy` - view, update, delete
  - `PageDefinitionPolicy` - view, update, delete
  - `CustomerPolicy` - viewAny, view, create, update, delete
  - `FacilityPolicy` - viewAny, view, create, update, delete, approve
  - `ApprovalTaskPolicy` - view, decide
  - `PaymentTransactionPolicy` - view, create
  - `JournalEntryPolicy` - view, create, post

#### FR-3.2: Policy Methods
- **MUST** implement standard methods:
  - `viewAny($user)` - can list records
  - `view($user, $model)` - can view single record
  - `create($user)` - can create new record
  - `update($user, $model)` - can update record
  - `delete($user, $model)` - can delete record
- **MUST** check user role
- **MUST** check ownership (for scoped models)
- **MUST** check branch/entity scope

#### FR-3.3: Policy Registration
- **MUST** register policies in `AuthServiceProvider`
- **MUST** use policies in controllers: `$this->authorize('update', $feature)`
- **MUST** use policies in Blade: `@can('update', $feature)`
- **MUST** use policies in Livewire: `$this->authorize('update', $feature)`

### FR-4: Role & Permission System

#### FR-4.1: Roles
- **MUST** define 6 roles:
  1. **platform_owner** - Full system access
  2. **hq_designer** - Design features, submit for review
  3. **hq_reviewer** - Review and approve features
  4. **branch_manager** - Approve operations, view reports
  5. **branch_staff** - Use features, create records
  6. **auditor** - Read-only access to all data

#### FR-4.2: Permissions
- **MUST** define permissions:
  - `access_studio` - Access HQ Studio
  - `manage_features` - Create/edit features
  - `review_features` - Approve/reject features
  - `publish_features` - Publish features
  - `use_features` - Use published features
  - `approve_facilities` - Approve facility applications
  - `manage_payments` - Process payments
  - `post_gl_entries` - Post journal entries
  - `view_audit_logs` - View audit trails
  - `admin_access` - System administration

#### FR-4.3: Role-Permission Mapping
- **MUST** map roles to permissions:
  - **platform_owner:** all permissions
  - **hq_designer:** access_studio, manage_features
  - **hq_reviewer:** access_studio, review_features
  - **branch_manager:** use_features, approve_facilities, view_audit_logs
  - **branch_staff:** use_features
  - **auditor:** view_audit_logs

#### FR-4.4: Permission Seeder
- **MUST** create seeder: `RolePermissionSeeder`
- **MUST** seed all roles
- **MUST** seed all permissions
- **MUST** seed role-permission mappings
- **MUST** create demo users for each role

### FR-5: Input Sanitization

#### FR-5.1: XSS Prevention
- **MUST** escape all output in Blade: `{{ $variable }}`
- **MUST** sanitize rich text inputs (strip `<script>`, `<iframe>`, etc.)
- **MUST** validate JSON inputs against schema
- **MUST** sanitize file uploads (check MIME type, scan for malware)

#### FR-5.2: SQL Injection Prevention
- **MUST** use Eloquent ORM for all queries
- **MUST** use Query Builder with bindings for complex queries
- **MUST** never concatenate user input into SQL
- **MUST** validate all dynamic table/column names

#### FR-5.3: Command Injection Prevention
- **MUST** never pass user input to `exec()`, `shell_exec()`, etc.
- **MUST** validate all file paths
- **MUST** use Laravel's `Storage` facade for file operations

---

## 4. Non-Functional Requirements

### NFR-1: Performance
- **MUST** check permissions in < 10ms
- **MUST** not impact page load time significantly
- **SHOULD** cache permission checks

### NFR-2: Auditability
- **MUST** log all authentication attempts
- **MUST** log all authorization failures
- **MUST** log all CSRF failures
- **MUST** log all suspicious activity

### NFR-3: Compliance
- **MUST** comply with OWASP Top 10
- **MUST** pass security audit
- **SHOULD** comply with ISO 27001

---

## 5. Implementation Tasks

### Phase 1: Authentication & Middleware (Week 1)
1. Apply `auth` middleware to all routes
2. Create `CheckPermission` middleware
3. Apply permission middleware to route groups
4. Test authentication flow
5. Test permission checks

### Phase 2: CSRF Protection (Week 1)
1. Add @csrf to all Livewire forms
2. Add @csrf to all Blade forms
3. Add CSRF token to AJAX headers
4. Test CSRF validation
5. Implement token refresh

### Phase 3: Policies (Week 2)
1. Create policy classes for all models
2. Implement policy methods
3. Register policies in AuthServiceProvider
4. Use policies in controllers
5. Use policies in views
6. Test policy enforcement

### Phase 4: Roles & Permissions (Week 2)
1. Define roles and permissions
2. Create role-permission mapping
3. Create RolePermissionSeeder
4. Seed demo users
5. Test role-based access

### Phase 5: Input Sanitization (Week 3)
1. Audit all user inputs
2. Add validation rules
3. Sanitize rich text inputs
4. Validate file uploads
5. Test XSS prevention
6. Test SQL injection prevention

### Phase 6: Security Audit (Week 3)
1. Run automated security scan
2. Manual penetration testing
3. Fix identified vulnerabilities
4. Re-test
5. Document security measures

---

## 6. Testing Requirements

### Test Cases

#### TC-1: Authentication
- [ ] Unauthenticated user cannot access protected routes
- [ ] Authenticated user can access allowed routes
- [ ] Session expires after 2 hours
- [ ] Remember me extends session to 30 days

#### TC-2: Authorization
- [ ] User with permission can access route
- [ ] User without permission gets 403 error
- [ ] Policy allows authorized actions
- [ ] Policy denies unauthorized actions

#### TC-3: CSRF Protection
- [ ] Form submission without token fails
- [ ] Form submission with valid token succeeds
- [ ] Form submission with expired token fails
- [ ] AJAX request without token fails

#### TC-4: Input Sanitization
- [ ] XSS payload in text input is escaped
- [ ] SQL injection in query is prevented
- [ ] Malicious file upload is rejected
- [ ] Command injection is prevented

---

## 7. Security Checklist

- [ ] All routes require authentication
- [ ] All routes check permissions
- [ ] All forms have CSRF tokens
- [ ] All models have policies
- [ ] All inputs are validated
- [ ] All outputs are escaped
- [ ] All queries use bindings
- [ ] All file uploads are validated
- [ ] All errors are logged
- [ ] All sensitive data is encrypted
- [ ] All passwords are hashed
- [ ] All sessions are secure
- [ ] All cookies are httpOnly
- [ ] All headers are secure (CSP, X-Frame-Options, etc.)

---

## 8. Success Metrics

- ✅ 0 authentication bypasses
- ✅ 0 authorization bypasses
- ✅ 0 CSRF vulnerabilities
- ✅ 0 XSS vulnerabilities
- ✅ 0 SQL injection vulnerabilities
- ✅ Security audit score > 95%

---

## 9. Timeline Estimate

- Week 1: Authentication + CSRF
- Week 2: Policies + Roles
- Week 3: Sanitization + Audit

**Total: 2-3 weeks**

---

**Document Status:** Draft  
**Last Updated:** 20 April 2026  
**Author:** Kiro AI
