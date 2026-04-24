# RBAC Quick Reference Card

## Roles

| Role | Code | Use Case |
|------|------|----------|
| Super Admin | `super-admin` | IT Admin, System Owner |
| System Admin | `system-admin` | Technical Lead |
| Feature Developer | `feature-developer` | Developers, BA |
| Reviewer | `reviewer` | QA, Business Owner |
| Publisher | `publisher` | Release Manager |
| Business User | `business-user` | End Users |
| Auditor | `auditor` | Internal Auditor |
| Branch Staff | `branch_staff` | Teller, Front Desk |
| Branch Manager | `branch_manager` | Branch Manager |

---

## Common Permissions

```
features.view, features.create, features.edit, features.delete, features.publish
flows.view, flows.create, flows.edit, flows.delete, flows.simulate
pages.view, pages.create, pages.edit, pages.delete
versions.view, versions.create, versions.submit, versions.review
versions.approve, versions.reject, versions.publish, versions.rollback
scopes.view, scopes.create, scopes.edit, scopes.delete
audit.view, monitor.view
users.view, users.create, users.edit, users.delete, users.assign-roles
ai.generate-ui, ai.refine-ui
runtime.execute, runtime.view-logs
```

---

## Quick Commands

```bash
# Seed roles & permissions
php artisan db:seed --class=RolePermissionSeeder

# List roles
php artisan permission:manage list-roles

# List permissions
php artisan permission:manage list-permissions

# Assign role
php artisan permission:manage assign-role --user=email@example.com --role=super-admin

# Show user permissions
php artisan permission:manage show-user --user=email@example.com

# Run tests
php artisan test tests/Feature/RBAC

# Clear cache
php artisan permission:cache-reset
```

---

## Code Snippets

### Assign Role
```php
$user->assignRole('feature-developer');
```

### Check Permission
```php
if (auth()->user()->hasPermissionTo('features.create')) {
    // Allowed
}
```

### Protect Route
```php
Route::get('/features', [FeatureController::class, 'index'])
    ->middleware('permission:features.view');
```

### Blade Directive
```blade
@can('features.create')
    <button>Create</button>
@endcan
```

### Helper Function
```php
if (can_permission('features.create')) {
    // Allowed
}
```

### Policy
```php
$this->authorize('create', Feature::class);
```

---

## Permission Matrix

| Permission | Super Admin | System Admin | Developer | Reviewer | Publisher | Business User | Auditor |
|------------|-------------|--------------|-----------|----------|-----------|---------------|---------|
| features.create | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| versions.approve | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| versions.publish | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ | ❌ |
| runtime.execute | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| users.assign-roles | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

---

## Troubleshooting

**Permission not working?**
```bash
php artisan cache:clear
php artisan permission:cache-reset
```

**User has no permissions?**
```php
$user->roles; // Check roles
$user->getAllPermissions(); // Check permissions
```

**403 Error?**
```php
auth()->user()->hasPermissionTo('permission.name'); // Should be true
```
