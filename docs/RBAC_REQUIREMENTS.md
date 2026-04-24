# RBAC Requirements - Ar-Rahnu System

## 1. Overview

Sistem Ar-Rahnu memerlukan Role-Based Access Control (RBAC) untuk mengawal akses kepada feature management, workflow execution, dan operasi kritikal. Implementation menggunakan Spatie Laravel Permission package.

## 2. Roles & Responsibilities

### 2.1 Super Admin
**Tanggungjawab:** Full system access, termasuk user management dan system configuration

**Akses:**
- ✅ Semua permissions
- ✅ User management (create, edit, delete, assign roles)
- ✅ System configuration
- ✅ Audit logs

**Use Case:**
- IT Administrator
- System Owner

---

### 2.2 System Admin
**Tanggungjawab:** Menguruskan features, flows, dan versions tanpa user management

**Akses:**
- ✅ Features: view, create, edit, delete, publish
- ✅ Flows: view, create, edit, delete, simulate
- ✅ Pages: view, create, edit, delete
- ✅ Versions: view, create, submit, review, approve, reject, publish, rollback
- ✅ Scopes: view, create, edit, delete
- ✅ Audit & monitoring
- ✅ AI features (generate-ui, refine-ui)
- ✅ Runtime execution & logs
- ❌ User management

**Use Case:**
- Technical Lead
- Senior Developer dengan full operational access

---

### 2.3 Feature Developer
**Tanggungjawab:** Membangunkan features dan workflows, submit untuk review

**Akses:**
- ✅ Features: view, create, edit
- ✅ Flows: view, create, edit, simulate
- ✅ Pages: view, create, edit
- ✅ Versions: view, create, submit
- ✅ Scopes: view only
- ✅ Monitor: view
- ✅ AI features (generate-ui, refine-ui)
- ✅ Runtime: execute
- ❌ Publish features
- ❌ Approve/reject versions
- ❌ Delete features

**Use Case:**
- Software Developer
- Business Analyst yang build workflows

---

### 2.4 Reviewer
**Tanggungjawab:** Review dan approve/reject versions sebelum publish

**Akses:**
- ✅ Features: view only
- ✅ Flows: view, simulate
- ✅ Pages: view only
- ✅ Versions: view, review, approve, reject
- ✅ Audit: view
- ✅ Monitor: view
- ✅ Runtime: execute
- ❌ Create/edit features
- ❌ Publish versions

**Use Case:**
- QA Lead
- Business Owner
- Compliance Officer

---

### 2.5 Publisher
**Tanggungjawab:** Publish approved versions ke production dan rollback jika perlu

**Akses:**
- ✅ Features: view only
- ✅ Flows: view only
- ✅ Pages: view only
- ✅ Versions: view, publish, rollback
- ✅ Audit: view
- ✅ Monitor: view
- ✅ Runtime: execute
- ❌ Create/edit features
- ❌ Approve/reject versions

**Use Case:**
- Release Manager
- DevOps Engineer
- Production Support

---

### 2.6 Business User
**Tanggungjawab:** Execute features untuk daily operations

**Akses:**
- ✅ Runtime: execute only
- ❌ Semua studio features
- ❌ View audit logs

**Use Case:**
- End users
- Customer service
- Operations staff

---

### 2.7 Auditor
**Tanggungjawab:** Read-only access untuk audit dan compliance

**Akses:**
- ✅ Features: view only
- ✅ Flows: view only
- ✅ Pages: view only
- ✅ Versions: view only
- ✅ Scopes: view only
- ✅ Audit: view
- ✅ Monitor: view
- ✅ Runtime logs: view
- ❌ Semua write operations

**Use Case:**
- Internal Auditor
- Compliance Team
- External Auditor

---

### 2.8 Branch Staff
**Tanggungjawab:** Execute runtime features di branch

**Akses:**
- ✅ Runtime: execute only
- ❌ Semua studio features

**Use Case:**
- Teller
- Front desk staff
- Branch operations

---

### 2.9 Branch Manager
**Tanggungjawab:** View dan edit flows/pages, plus runtime execution

**Akses:**
- ✅ Flows: view, edit
- ✅ Pages: view, edit
- ✅ Runtime: execute
- ❌ Create new features
- ❌ Delete features
- ❌ Publish versions

**Use Case:**
- Branch Manager
- Operations Manager

---

## 3. Permissions Matrix

| Permission | Super Admin | System Admin | Developer | Reviewer | Publisher | Business User | Auditor | Branch Staff | Branch Manager |
|------------|-------------|--------------|-----------|----------|-----------|---------------|---------|--------------|----------------|
| **Features** |
| features.view | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| features.create | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| features.edit | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| features.delete | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| features.publish | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Flows** |
| flows.view | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| flows.create | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| flows.edit | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| flows.delete | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| flows.simulate | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Pages** |
| pages.view | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| pages.create | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| pages.edit | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| pages.delete | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Versions** |
| versions.view | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| versions.create | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| versions.submit | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| versions.review | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| versions.approve | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| versions.reject | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| versions.publish | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| versions.rollback | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **Scopes** |
| scopes.view | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ |
| scopes.create | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| scopes.edit | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| scopes.delete | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Audit & Monitoring** |
| audit.view | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| monitor.view | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| **User Management** |
| users.view | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| users.create | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| users.edit | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| users.delete | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| users.assign-roles | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **AI Features** |
| ai.generate-ui | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| ai.refine-ui | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Runtime** |
| runtime.execute | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ |
| runtime.view-logs | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ |

---

## 4. Workflow Approval Chain

### 4.1 Feature Development Lifecycle

```
Developer → Reviewer → Publisher → Production
   ↓           ↓          ↓
 Submit    Approve    Publish
           Reject     Rollback
```

**Separation of Duties:**
- Developer **TIDAK BOLEH** approve sendiri
- Reviewer **TIDAK BOLEH** publish sendiri
- Publisher **TIDAK BOLEH** approve sendiri

### 4.2 Critical Operations

**Publish to Production:**
- Requires: `versions.publish` permission
- Pre-requisite: Version must be approved
- Audit: Logged dengan user ID dan timestamp

**Rollback:**
- Requires: `versions.rollback` permission
- Audit: Logged dengan reason dan user ID

**User Role Assignment:**
- Requires: `users.assign-roles` permission
- Restricted to: Super Admin only

---

## 5. Route Protection

### 5.1 Studio Routes
```php
Route::prefix('studio')->middleware(['auth'])->group(function () {
    // Dashboard - accessible to all authenticated users
    Route::get('/', Dashboard::class);
    
    // Flow Canvas - requires flows.edit
    Route::get('/flow-canvas/{flowId}', FlowCanvasProxy::class)
        ->middleware('permission:flows.edit');
    
    // Page Builder - requires pages.edit
    Route::get('/page-builder/{featureVersionId}/{pageId}', PageBuilderProxy::class)
        ->middleware('permission:pages.edit');
    
    // Monitor - requires monitor.view
    Route::get('/monitor', RuntimeMonitor::class)
        ->middleware('permission:monitor.view');
});
```

### 5.2 API Routes
```php
// Version Management
Route::post('versions/{id}/submit', [ApprovalController::class, 'submit'])
    ->middleware('permission:versions.submit');

Route::post('versions/{id}/approve', [ApprovalController::class, 'approve'])
    ->middleware('permission:versions.approve');

Route::post('versions/{id}/publish', [ApprovalController::class, 'publish'])
    ->middleware('permission:versions.publish');

Route::post('versions/{id}/rollback', [ApprovalController::class, 'rollback'])
    ->middleware('permission:versions.rollback');
```

### 5.3 Runtime Routes
```php
Route::get('/f/{featureKey}/{pageKey?}', FormEngine::class)
    ->middleware(['web', 'auth', 'permission:runtime.execute']);
```

---

## 6. Implementation Requirements

### 6.1 Database Tables

**Required Tables:**
- `roles` - Role definitions
- `permissions` - Permission definitions
- `model_has_roles` - User-role assignments
- `model_has_permissions` - Direct user permissions
- `role_has_permissions` - Role-permission mappings

**Migration:**
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### 6.2 Seeding

**Command:**
```bash
php artisan db:seed --class=RolePermissionSeeder
```

**Default Admin:**
```php
$admin = User::create([
    'name' => 'System Admin',
    'email' => 'admin@arrahnu.com',
    'password' => Hash::make('password'),
]);
$admin->assignRole('super-admin');
```

### 6.3 Middleware

**Permission Check:**
```php
// Single permission
->middleware('permission:flows.edit')

// Multiple permissions (OR)
->middleware('permission:flows.edit|pages.edit')

// Multiple permissions (AND)
->middleware(['permission:flows.edit', 'permission:pages.edit'])
```

**Role Check:**
```php
->middleware('role:super-admin|system-admin')
```

### 6.4 Blade Directives

**Check Permission:**
```blade
@can('flows.edit')
    <button>Edit Flow</button>
@endcan

@cannot('flows.delete')
    <p>You cannot delete this flow</p>
@endcannot
```

**Check Role:**
```blade
@role('super-admin')
    <a href="/admin">Admin Panel</a>
@endrole
```

### 6.5 Controller Authorization

**Using Policy:**
```php
public function update(Request $request, Flow $flow)
{
    $this->authorize('update', $flow);
    // Update logic
}
```

**Using Permission:**
```php
public function store(Request $request)
{
    if (!auth()->user()->can('flows.create')) {
        abort(403, 'Unauthorized');
    }
    // Store logic
}
```

---

## 7. Security Requirements

### 7.1 Password Policy
- Minimum 8 characters
- Must contain uppercase, lowercase, number
- Password expiry: 90 days (for admin roles)
- Password history: Cannot reuse last 5 passwords

### 7.2 Session Management
- Session timeout: 30 minutes idle
- Force logout on role change
- Single session per user (optional)

### 7.3 Audit Logging
**Must Log:**
- User login/logout
- Role assignments
- Permission changes
- Feature publish/rollback
- Failed authorization attempts

**Log Format:**
```json
{
    "user_id": 123,
    "action": "version.publish",
    "resource_type": "FeatureVersion",
    "resource_id": 456,
    "ip_address": "192.168.1.1",
    "user_agent": "Mozilla/5.0...",
    "timestamp": "2026-04-24 10:30:00"
}
```

### 7.4 Rate Limiting
- API calls: 60 per minute per user
- Login attempts: 5 per 15 minutes
- Password reset: 3 per hour

---

## 8. Testing Requirements

### 8.1 Unit Tests
```php
// Test role permissions
public function test_developer_cannot_publish()
{
    $user = User::factory()->create();
    $user->assignRole('feature-developer');
    
    $this->assertFalse($user->can('versions.publish'));
}

// Test route protection
public function test_unauthorized_user_cannot_access_flow_canvas()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->get('/studio/flow-canvas/1');
    
    $response->assertStatus(403);
}
```

### 8.2 Integration Tests
- Test complete approval workflow
- Test role escalation prevention
- Test audit log creation

---

## 9. Migration Plan

### 9.1 Existing Users
```php
// Assign default role to existing users
User::whereDoesntHave('roles')->each(function ($user) {
    $user->assignRole('business-user');
});
```

### 9.2 Rollback Plan
- Backup `users` table
- Backup `roles` and `permissions` tables
- Document current role assignments
- Test rollback in staging

---

## 10. Compliance & Audit

### 10.1 BNM Requirements
- Segregation of duties (maker-checker)
- Audit trail for all critical operations
- Role-based access control
- Regular access reviews

### 10.2 Access Review
- Quarterly review of user roles
- Annual review of permission matrix
- Immediate revocation on termination

### 10.3 Reporting
- Monthly: Active users by role
- Quarterly: Permission usage report
- Annually: Access certification

---

## 11. Future Enhancements

### 11.1 Dynamic Permissions
- Feature-level permissions
- Branch-level permissions
- Time-based permissions

### 11.2 Advanced Features
- Multi-factor authentication for admin roles
- IP whitelisting for sensitive operations
- Approval workflows with multiple reviewers
- Emergency access (break-glass) procedures

---

## Status: ✅ IMPLEMENTED

RBAC system telah diimplementasikan menggunakan Spatie Laravel Permission package dengan 9 roles dan 30+ permissions.

**Next Steps:**
1. Review requirements ini
2. Validate permission matrix
3. Add missing permissions (jika ada)
4. Implement audit logging
5. Add automated tests
