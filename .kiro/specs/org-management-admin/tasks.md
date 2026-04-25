# Implementation Plan: Organization Management Admin

## Overview

Build the complete admin panel at `/admin/*` for the Ar-Rahnu system using Livewire 3 full-page components with an Apple-style sidebar layout. All Eloquent models and migrations already exist — this plan focuses exclusively on routes, layout, Livewire components, Blade views, organizational scopes, and tests. Each task builds incrementally, wiring components together as they are created.

## Tasks

- [x] 1. Set up admin route group, middleware, and layout
  - [x] 1.1 Register the admin route group in `routes/web.php`
    - Add `Route::prefix('admin')->middleware(['web', 'auth', 'role:super-admin'])->group(...)` with named routes using the `admin.` prefix
    - Register routes: `admin.dashboard`, `admin.branches`, `admin.branches.show`, `admin.departments`, `admin.staff`, `admin.entity`, `admin.users.roles`
    - All routes render Livewire full-page components
    - _Requirements: 1.1, 1.2, 1.5, 13.1, 13.2_

  - [x] 1.2 Create the admin layout at `resources/views/layouts/admin.blade.php`
    - Follow the same Apple-style sidebar pattern as `layouts/branch.blade.php` (260px sidebar, Inter font, Tailwind CSS)
    - Sidebar sections: Dashboard, Organization (Branches, Departments), People (Staff, User Roles), Settings (Entity Settings)
    - Active route highlighting via `request()->routeIs()`
    - User profile display in sidebar footer with name and role
    - Quick-access link to Studio dashboard (`route('studio.dashboard')`)
    - Main content area with `{{ $slot }}` for Livewire components
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

  - [ ]* 1.3 Write feature tests for admin route protection
    - Test unauthenticated user redirected to login for `/admin/*` routes
    - Test authenticated user without `super-admin` role gets 403 for `/admin/*` routes
    - Test authenticated super-admin can access `/admin/*` routes
    - Test all named routes exist with `admin.` prefix
    - Create `tests/Feature/Admin/AdminRouteTest.php`
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

- [x] 2. Implement Admin Dashboard component
  - [x] 2.1 Create `app/Livewire/Admin/Dashboard.php` and its Blade view
    - Full-page Livewire component using `->layout('layouts.admin')`
    - Query and display: active branch count, total staff count, department count, region count as summary cards
    - Display branch type breakdown (HQ, branch, mini_branch) with counts
    - Display staff employment type breakdown (permanent, contract, temporary) with counts
    - Display branches list with active staff count, manager name, and status
    - Display recent staff assignments (created/updated in last 30 days)
    - Show "Create your first branch" prompt when no branches exist
    - Create view at `resources/views/livewire/admin/dashboard.blade.php`
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6_

  - [ ]* 2.2 Write feature tests for Admin Dashboard
    - Test dashboard renders with correct metrics
    - Test empty state shows create branch prompt
    - Create `tests/Feature/Admin/DashboardTest.php`
    - _Requirements: 3.1, 3.6_

- [x] 3. Checkpoint — Verify admin routes, layout, and dashboard
  - Ensure all tests pass, ask the user if questions arise.

- [x] 4. Implement Branch Management — List with filters
  - [x] 4.1 Create `app/Livewire/Admin/BranchManager.php` and its Blade view
    - Full-page Livewire component with `->layout('layouts.admin')`
    - Paginated table showing: code, name, type (color-coded badge), region, city, state, manager name, active staff count, status indicator
    - Text search filtering by code or name using `wire:model.live.debounce.300ms`
    - Dropdown filters for region, state, branch type, and active status — all update without full page reload
    - Eager load `region`, `manager`, `activeStaffAssignments` relationships
    - Create view at `resources/views/livewire/admin/branch-manager.blade.php`
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6_

  - [ ]* 4.2 Write property test for branch filter correctness
    - **Property 2: Branch filter correctness**
    - Generate random branches with varied attributes using factories, apply random filter combinations, verify all returned branches match every applied filter
    - Create `tests/Feature/Admin/BranchFilterPropertyTest.php`
    - **Validates: Requirements 4.2, 4.3**

- [x] 5. Implement Branch Management — Create and Edit (modal CRUD)
  - [x] 5.1 Add create/edit modal functionality to `BranchManager`
    - Modal form with fields: code, name, type, region, address, city, state, postcode, phone, email, manager, opening hours, is_active
    - Validate branch code unique within entity (excluding current on edit)
    - Validate only one HQ per entity
    - Validate manager has `branch-manager` role
    - Success notification on save via `session()->flash()`
    - Edit populates form with existing data
    - Toggle status method for activate/deactivate
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7_

  - [ ]* 5.2 Write property test for branch code uniqueness
    - **Property 3: Branch code uniqueness invariant**
    - Generate random branch codes, attempt duplicate creation within same entity, verify rejection
    - Create `tests/Feature/Admin/BranchUniquenessPropertyTest.php`
    - **Validates: Requirements 5.2, 5.7**

  - [ ]* 5.3 Write property test for one HQ per entity
    - **Property 4: One HQ per entity invariant**
    - After any sequence of branch create/update operations, verify at most one branch has `type = 'hq'` per entity
    - Add to `tests/Feature/Admin/BranchUniquenessPropertyTest.php`
    - **Validates: Requirements 5.3**

  - [ ]* 5.4 Write property test for manager role validation
    - **Property 5: Manager role validation**
    - Generate users with and without `branch-manager` role, attempt to assign as manager, verify only valid managers accepted
    - Add to `tests/Feature/Admin/BranchUniquenessPropertyTest.php`
    - **Validates: Requirements 5.4**

- [x] 6. Implement Branch Detail with Tabs
  - [x] 6.1 Create `app/Livewire/Admin/BranchDetail.php` and its Blade view
    - Full-page Livewire component with route model binding (`mount(Branch $branch)`)
    - Three tabs: Overview, Staff, Settings
    - Overview tab: branch info, contact details, operating hours, manager details
    - Staff tab: table of active staff with name, position, employment type, start date; buttons for assign/transfer (wire to StaffManager)
    - Settings tab: editable key-value pairs from `settings` JSON column with save functionality
    - Success notification on settings save
    - Create view at `resources/views/livewire/admin/branch-detail.blade.php`
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_

  - [ ]* 6.2 Write property test for settings JSON round-trip
    - **Property 6: Settings JSON round-trip**
    - Generate random key-value maps (string keys, scalar/array values), save to Branch `settings`, reload, verify equivalence
    - Create `tests/Feature/Admin/SettingsRoundTripPropertyTest.php`
    - **Validates: Requirements 6.5, 12.3**

- [x] 7. Checkpoint — Verify branch management components
  - Ensure all tests pass, ask the user if questions arise.

- [x] 8. Implement Department Management
  - [x] 8.1 Create `app/Livewire/Admin/DepartmentManager.php` and its Blade view
    - Full-page Livewire component with `->layout('layouts.admin')`
    - Display departments in hierarchical tree: root departments first, children indented with CSS margin
    - Show code, name, head name, parent department, active staff count, status for each department
    - Eager load `children.children` for up to 3 levels, `head`, `activeStaffAssignments`
    - Modal form for create/edit with fields: code, name, description, head, parent department, is_active
    - Validate department code unique within entity
    - Prevent deletion of departments with active staff assignments
    - Create view at `resources/views/livewire/admin/department-manager.blade.php`
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7_

  - [ ]* 8.2 Write property test for department code uniqueness
    - **Property 7: Department code uniqueness invariant**
    - Generate random department codes, attempt duplicate creation within same entity, verify rejection
    - Create `tests/Feature/Admin/DepartmentPropertyTest.php`
    - **Validates: Requirements 7.4**

  - [ ]* 8.3 Write property test for department deletion guard
    - **Property 8: Department deletion guard**
    - Generate departments with and without active staff, attempt deletion, verify only empty departments can be deleted
    - Add to `tests/Feature/Admin/DepartmentPropertyTest.php`
    - **Validates: Requirements 7.6**

- [x] 9. Implement Staff Management — List with filters
  - [x] 9.1 Create `app/Livewire/Admin/StaffManager.php` and its Blade view
    - Full-page Livewire component with `->layout('layouts.admin')`
    - Paginated table showing: employee number, name, email, primary assignment location (branch or department name), position, role(s) as badges, active status
    - Text search filtering by name, email, or employee number
    - Dropdown filters for branch, department, role, active status, employment type — all update without full page reload
    - Eager load `primaryAssignment.branch`, `primaryAssignment.department`, `roles`
    - "Manage Roles" link for each staff member navigating to `admin.users.roles` route
    - Create view at `resources/views/livewire/admin/staff-manager.blade.php`
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 13.3_

  - [ ]* 9.2 Write property test for staff filter correctness
    - **Property 9: Staff filter correctness**
    - Generate random users with varied attributes, apply random filter combinations, verify all returned users match every applied filter
    - Create `tests/Feature/Admin/StaffFilterPropertyTest.php`
    - **Validates: Requirements 8.2, 8.3**

- [x] 10. Implement Staff Management — Create, Edit, and Password Reset
  - [x] 10.1 Add create/edit staff modal and password reset to `StaffManager`
    - Modal form with fields: name, email, employee number, phone, password, entity, is_active
    - Validate employee number unique
    - Validate email unique
    - Success notification on save
    - Edit populates form with existing user data
    - Password reset method that generates and sets a new password
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6_

  - [ ]* 10.2 Write property test for user uniqueness constraints
    - **Property 10: User uniqueness constraints**
    - Generate random employee numbers and emails, attempt duplicate creation, verify rejection
    - Create `tests/Feature/Admin/UserUniquenessPropertyTest.php`
    - **Validates: Requirements 9.2, 9.3**

- [x] 11. Implement Staff Assignment
  - [x] 11.1 Add assignment modal and history to `StaffManager`
    - Assignment form with fields: branch or department, position, employment type, start date, is_primary flag
    - Validate branch XOR department (exactly one must be set)
    - Validate only one active primary assignment per user
    - Prompt to confirm replacing primary assignment if one already exists
    - Display assignment history showing all past and current assignments
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5, 10.6_

  - [ ]* 11.2 Write property test for assignment branch XOR department
    - **Property 11: Assignment branch XOR department**
    - Generate random assignments with various branch_id/department_id combinations, verify exactly one must be non-null
    - Create `tests/Feature/Admin/AssignmentInvariantPropertyTest.php`
    - **Validates: Requirements 10.2**

  - [ ]* 11.3 Write property test for one primary assignment per user
    - **Property 12: One primary assignment per user**
    - After any sequence of assignment operations, verify at most one active assignment has `is_primary = true` per user
    - Add to `tests/Feature/Admin/AssignmentInvariantPropertyTest.php`
    - **Validates: Requirements 10.3**

- [x] 12. Implement Staff Transfer Workflow
  - [x] 12.1 Add transfer modal and execution logic to `StaffManager`
    - Transfer form showing current assignment details with fields: new branch or department, new position, transfer reason, effective date
    - Validate transfer destination differs from current location
    - Confirmation dialog before executing transfer
    - On execute: end current assignment (`ended_at` = effective date), create new assignment (`started_at` = effective date), inherit `is_primary` flag
    - Wrap in database transaction for atomicity
    - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5, 11.6_

  - [ ]* 12.2 Write property test for transfer workflow correctness
    - **Property 13: Transfer workflow correctness**
    - Generate random staff with assignments, execute transfers to random valid destinations, verify: (a) old assignment ended, (b) new assignment created with correct dates, (c) primary flag inherited; verify same-location transfer rejected
    - Create `tests/Feature/Admin/TransferPropertyTest.php`
    - **Validates: Requirements 11.2, 11.3, 11.4, 11.5**

- [x] 13. Checkpoint — Verify staff management and transfer workflow
  - Ensure all tests pass, ask the user if questions arise.

- [x] 14. Implement Entity Settings and UserRoleManager route integration
  - [x] 14.1 Create `app/Livewire/Admin/EntitySettings.php` and its Blade view
    - Full-page Livewire component with `->layout('layouts.admin')`
    - Display entity details: code, name, registration number, license number, address, phone, email, active status
    - Editable form for entity details (name, address, phone, email, registration number, license number)
    - Editable key-value settings from `settings` JSON column
    - Validate required fields, display success notification on save
    - Show create form if no entity exists
    - Create view at `resources/views/livewire/admin/entity-settings.blade.php`
    - _Requirements: 12.1, 12.2, 12.3, 12.4, 12.5_

  - [x] 14.2 Wire UserRoleManager to admin layout
    - Update the existing `UserRoleManager` component's `render()` method to use `->layout('layouts.admin')` when accessed via admin route
    - Ensure route model binding passes User instance correctly at `/admin/users/{user}/roles`
    - Verify the existing `user-role-manager.blade.php` view works within admin layout
    - _Requirements: 13.1, 13.2, 13.4_

  - [ ]* 14.3 Write feature tests for Entity Settings and UserRoleManager route
    - Test entity details display and edit
    - Test entity settings JSON save
    - Test create form shown when no entity exists
    - Test `/admin/users/{user}/roles` resolves User and renders UserRoleManager
    - Create `tests/Feature/Admin/EntitySettingsTest.php` and `tests/Feature/Admin/UserRoleManagerRouteTest.php`
    - _Requirements: 12.1, 12.4, 12.5, 13.1, 13.2_

- [x] 15. Implement Organizational Scoping (EntityScope and BranchScope)
  - [x] 15.1 Create `EntityScope` and `BranchScope` global scopes
    - Create `app/Models/Scopes/EntityScope.php` implementing `Illuminate\Database\Eloquent\Scope`
    - EntityScope: filter by `entity_id` for non-super-admin users; return empty result for unauthenticated requests
    - Create `app/Models/Scopes/BranchScope.php` implementing `Illuminate\Database\Eloquent\Scope`
    - BranchScope: filter by accessible branch IDs via `getAccessibleBranches()` for non-super-admin users; return empty result for unauthenticated requests
    - Super-admin users bypass both scopes (no filtering applied)
    - _Requirements: 14.1, 14.2, 14.3, 14.4, 14.5, 14.6_

  - [ ]* 15.2 Write property test for EntityScope filtering
    - **Property 14: EntityScope filtering**
    - Generate users with different roles and entity assignments, execute entity-scoped queries, verify all results have matching `entity_id`; verify super-admin sees all
    - Create `tests/Feature/Admin/ScopingPropertyTest.php`
    - **Validates: Requirements 14.1**

  - [ ]* 15.3 Write property test for BranchScope filtering
    - **Property 15: BranchScope filtering**
    - Generate users with different roles and branch access, execute branch-scoped queries, verify all results have `branch_id` within accessible set; verify super-admin sees all
    - Add to `tests/Feature/Admin/ScopingPropertyTest.php`
    - **Validates: Requirements 14.2**

- [x] 16. Checkpoint — Verify entity settings, scoping, and role management
  - Ensure all tests pass, ask the user if questions arise.

- [x] 17. Integration wiring and final verification
  - [x] 17.1 Wire all navigation links and cross-component interactions
    - Verify sidebar navigation links in admin layout point to correct routes
    - Verify "Manage Roles" link in StaffManager navigates to `admin.users.roles` with correct user ID
    - Verify BranchDetail staff tab assign/transfer buttons integrate with StaffManager
    - Verify Studio dashboard quick-access link works from admin layout
    - Verify all Livewire components use `->layout('layouts.admin')` consistently
    - _Requirements: 2.1, 2.5, 13.3_

  - [ ]* 17.2 Write integration tests for cross-component flows
    - Test transfer atomicity: if new assignment creation fails, old assignment is not ended (transaction rollback)
    - Test route model binding for `/admin/branches/{branch}` and `/admin/users/{user}/roles`
    - Test non-super-admin access denial across all admin routes (Property 1)
    - Create `tests/Feature/Admin/AdminIntegrationTest.php`
    - **Property 1: Non-super-admin access denial**
    - **Validates: Requirements 1.4, 11.3, 11.4, 13.2**

- [x] 18. Final checkpoint — Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- All models and migrations already exist — no database work needed
- The existing `UserRoleManager` component is reused at its new admin route
- Property tests use Laravel factories with Faker to generate random valid inputs (minimum 100 iterations each)
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Database transactions wrap multi-step operations (transfer workflow) for atomicity
