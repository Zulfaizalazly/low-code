# RBAC Implementation Guide

## Overview

RBAC (Role-Based Access Control) telah diimplementasikan menggunakan **Spatie Laravel Permission** package dengan 9 roles dan 30+ permissions.

---

## Installation & Setup

### 1. Install Package (Already Done)

```bash
composer require spatie/laravel-permission
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Seed Roles & Permissions

```bash
php artisan db:seed --class=RolePermissionSeeder
```

---

## Available Roles

| Role | Description | Key Permissions |
|------|-------------|-----------------|
| `super-admin` | Full system access | All permissions |
| `system-admin` | System management (no user mgmt) | All except user management |
| `feature-developer` | Build features | Create/edit features, flows, pages |
| `reviewer` | Review & approve versions | Approve/reject versions |
| `publisher` | Publish to production | Publish/rollback versions |
| `business-user` | Execute features only | Runtime execution |
| `auditor` | Read-only access | View all, no modifications |
| `branch_staff` | Branch operations | Runtime execution |
| `branch_manager` | Branch management | Edit flows/pages, runtime |

---

## Available Permissions

### Features
- `features.view`
- `features.create`
- `features.edit`
- `features.delete`
- `features.publish`

### Flows
- `flows.view`
- `flows.create`
- `flows.edit`
- `flows.delete`
- `flows.simulate`

### Pages
- `pages.view`
- `pages.create`
- `pages.edit`
- `pages.delete`

### Versions
- `versions.view`
- `versions.create`
- `versions.submit`
- `versions.review`
- `versions.approve`
- `versions.reject`
- `versions.publish`
- `versions.rollback`

### Scopes
- `scopes.view`
- `scopes.create`
- `scopes.edit`
- `scopes.delete`

### Audit & Monitoring
- `audit.view`
- `monitor.view`

### Users
- `users.view`
- `users.create`
- `users.edit`
- `users.delete`
- `users.assign-roles`

### AI
- `ai.generate-ui`
- `ai.refine-ui`

### Runtime
- `runtime.execute`
- `runtime.view-logs`

---

## Usage Examples

### 1. Assign Role to User

```php
use App\Models\User;

$user = User::find(1);
$user->assignRole('feature-developer');

// Multiple roles
$user->assignRole(['feature-developer', 'reviewer']);
```

### 2. Check Permission in Controller

```php
public function store(Request $request)
{
    // Method 1: Using authorize
    $this->authorize('create', Feature::class);
    
    // Method 2: Using hasPermissionTo
    if (!auth()->user()->hasPermissionTo('features.create')) {
        abort(403);
    }
    
    // Method 3: Using helper
    if (!can_permission('features.create')) {
        abort(403);
    }
    
    // Your logic here
}
```

### 3. Protect Routes with Middleware

```php
// Single permission
Route::get('/features', [FeatureController::class, 'index'])
    ->middleware('permission:features.view');

// Multiple permissions (OR)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('permission:features.view|flows.view');

// Role check
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware('role:super-admin|system-admin');
```

### 4. Blade Directives

```blade
{{-- Check permission --}}
@can('features.create')
    <button>Create Feature</button>
@endcan

@cannot('features.delete')
    <p>You cannot delete features</p>
@endcannot

{{-- Check role --}}
@role('super-admin')
    <a href="/admin">Admin Panel</a>
@endrole

{{-- Custom component --}}
<x-can-permission permission="features.edit">
    <button>Edit</button>
</x-can-permission>

<x-has-role role="super-admin">
    <div>Admin Content</div>
</x-has-role>
```

### 5. Using Helper Functions

```php
// Check permission
if (can_permission('features.create')) {
    // User has permission
}

// Check role
if (has_role('super-admin')) {
    // User is super admin
}

// Check multiple permissions
if (has_any_permission(['features.create', 'flows.create'])) {
    // User has at least one permission
}

// Check if super admin
if (is_super_admin()) {
    // User is super admin
}

// Check if HQ staff
if (is_hq_staff()) {
    // User is HQ staff
}
```

### 6. Using PermissionService

```php
use App\Services\PermissionService;

$service = app(PermissionService::class);

// Assign role
$service->assignRole($user, 'feature-developer');

// Remove role
$service->removeRole($user, 'feature-developer');

// Sync roles (replace all)
$service->syncRoles($user, ['reviewer', 'publisher']);

// Give direct permission
$service->givePermission($user, 'features.view');

// Get user permissions
$permissions = $service->getUserPermissions($user);

// Get permissions by category
$grouped = $service->getPermissionsByCategory();
```

### 7. Using Policy Classes

```php
// In controller
public function update(Request $request, Feature $feature)
{
    $this->authorize('update', $feature);
    
    // Update logic
}

// In blade
@can('update', $feature)
    <button>Edit</button>
@endcan
```

---

## Artisan Commands

### List All Roles

```bash
php artisan permission:manage list-roles
```

### List All Permissions

```bash
php artisan permission:manage list-permissions
```

### Assign Role to User

```bash
php artisan permission:manage assign-role --user=admin@arrahnu.com --role=super-admin
```

### Show User Permissions

```bash
php artisan permission:manage show-user --user=admin@arrahnu.com
```

### Create New Role

```bash
php artisan permission:manage create-role --role=custom-role --permissions=features.view --permissions=flows.view
```

---

## Testing

### Run RBAC Tests

```bash
# All RBAC tests
php artisan test tests/Feature/RBAC

# Specific test
php artisan test tests/Feature/RBAC/RolePermissionTest.php
```

### Test Coverage

- ✅ Role permission assignments
- ✅ Permission checks
- ✅ Middleware protection
- ✅ Policy authorization
- ✅ Service methods
- ✅ Helper functions

---

## Security Best Practices

### 1. Separation of Duties

```php
// Developer cannot approve their own work
$developer->assignRole('feature-developer');
// ❌ Cannot approve versions

// Reviewer cannot publish
$reviewer->assignRole('reviewer');
// ❌ Cannot publish versions

// Publisher cannot approve
$publisher->assignRole('publisher');
// ❌ Cannot approve versions
```

### 2. Least Privilege Principle

```php
// Give minimum required permissions
$user->assignRole('business-user'); // Only runtime.execute

// Not this
$user->assignRole('super-admin'); // ❌ Too much access
```

### 3. Audit Trail

```php
// Log role changes
Log::info('Role assigned', [
    'user_id' => $user->id,
    'role' => $roleName,
    'assigned_by' => auth()->id(),
]);
```

### 4. Cache Management

```php
// Clear permission cache after changes
app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

// Or use service
$permissionService->clearCache();
```

---

## Common Patterns

### 1. Check Multiple Permissions

```php
// User must have ALL permissions
if (auth()->user()->hasAllPermissions(['features.create', 'flows.create'])) {
    // Proceed
}

// User must have ANY permission
if (auth()->user()->hasAnyPermission(['features.view', 'flows.view'])) {
    // Proceed
}
```

### 2. Conditional UI Elements

```blade
<div class="actions">
    @can('features.edit')
        <button>Edit</button>
    @endcan
    
    @can('features.delete')
        <button>Delete</button>
    @endcan
    
    @role('super-admin')
        <button>Advanced Settings</button>
    @endrole
</div>
```

### 3. Dynamic Permission Checks

```php
$permissions = ['features.view', 'flows.view', 'pages.view'];

foreach ($permissions as $permission) {
    if (auth()->user()->hasPermissionTo($permission)) {
        // Show menu item
    }
}
```

### 4. Role-Based Redirects

```php
public function redirectAfterLogin()
{
    $user = auth()->user();
    
    if ($user->hasRole('super-admin')) {
        return redirect('/admin/dashboard');
    }
    
    if ($user->hasRole('feature-developer')) {
        return redirect('/studio');
    }
    
    if ($user->hasRole('business-user')) {
        return redirect('/features');
    }
    
    return redirect('/');
}
```

---

## Troubleshooting

### Permission Not Working

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear

# Clear permission cache
php artisan permission:cache-reset
```

### User Has No Permissions

```php
// Check if role is assigned
$user->roles; // Should not be empty

// Check if role has permissions
$role = Role::findByName('feature-developer');
$role->permissions; // Should not be empty

// Reseed if needed
php artisan db:seed --class=RolePermissionSeeder
```

### 403 Forbidden Error

```php
// Check middleware
Route::get('/test', function() {
    return 'OK';
})->middleware('permission:features.view');

// Check if user has permission
auth()->user()->hasPermissionTo('features.view'); // Should be true

// Check if permission exists
Permission::findByName('features.view'); // Should not be null
```

---

## Migration Guide

### Adding New Permission

1. Add to RolePermissionSeeder:

```php
$permissions = [
    // ... existing permissions
    'reports.view',
    'reports.export',
];
```

2. Assign to roles:

```php
$systemAdmin->givePermissionTo(['reports.view', 'reports.export']);
```

3. Reseed:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

### Adding New Role

1. Add to RolePermissionSeeder:

```php
$customRole = Role::create(['name' => 'custom-role']);
$customRole->givePermissionTo([
    'features.view',
    'flows.view',
]);
```

2. Reseed:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

---

## API Integration

### Check Permission in API

```php
// In API controller
public function index(Request $request)
{
    if (!$request->user()->hasPermissionTo('features.view')) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    // Your logic
}
```

### Protect API Routes

```php
Route::middleware(['auth:sanctum', 'permission:features.view'])
    ->get('/api/features', [FeatureController::class, 'index']);
```

---

## Performance Considerations

### 1. Permission Caching

Permissions are cached for 24 hours by default. Configure in `config/permission.php`:

```php
'cache' => [
    'expiration_time' => \DateInterval::createFromDateString('24 hours'),
    'key' => 'spatie.permission.cache',
    'store' => 'default',
],
```

### 2. Eager Loading

```php
// Load roles and permissions
$users = User::with('roles.permissions')->get();

// Avoid N+1 queries
$users = User::with('roles')->get();
foreach ($users as $user) {
    $user->hasPermissionTo('features.view'); // Uses cache
}
```

---

## Status: ✅ IMPLEMENTED

RBAC system fully implemented with:
- ✅ 9 roles defined
- ✅ 30+ permissions defined
- ✅ Middleware protection
- ✅ Policy classes
- ✅ Service layer
- ✅ Helper functions
- ✅ Blade components
- ✅ Artisan commands
- ✅ Comprehensive tests
- ✅ Documentation

**Next Steps:**
1. Run tests: `php artisan test tests/Feature/RBAC`
2. Seed database: `php artisan db:seed --class=RolePermissionSeeder`
3. Test in browser with different roles
4. Integrate with Organization Management (Phase 2)
