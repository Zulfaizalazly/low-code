# Implementation Tasks

## Task 1: Secure Login Routes for Production
- **Requirement:** Req 14
- **Priority:** 🔴 Critical (Security)
- **Files:** `routes/web.php`
- **Description:** Wrap all development login shortcut routes (`/login-hq`, `/login-admin`, `/login-manager`, `/login-teller`, `/login-admin-panel`) with `app()->environment(['local', 'testing'])` guard supaya tak accessible dalam production.
- [x] Wrap 5 login shortcut routes dalam environment check
- [x] Verify routes return 404 when `APP_ENV=production`

## Task 2: Fix ApprovalService Reviewer Notification
- **Requirement:** Req 6
- **Priority:** 🔴 Critical (Broken Feature)
- **Files:** `app/Studio/Publishing/ApprovalService.php`
- **Description:** Fix `notifyReviewers()` untuk guna Spatie `role()` scope instead of `whereIn('role', ...)`. Fix `notifySubmitter()` untuk resolve submitter dari `ApprovalWorkflow.submitted_by` instead of `FeatureVersion.published_by`.
- [x] Update `notifyReviewers()` — guna `User::role(['reviewer', 'super-admin', 'system-admin'])->get()`
- [x] Update `notifySubmitter()` — resolve submitter dari `ApprovalWorkflow` record

## Task 3: VersionPublisher Auto-Create ChangeDeployment
- **Requirement:** Req 5
- **Priority:** 🔴 Critical (Missing Wiring)
- **Files:** `app/Studio/Publishing/VersionPublisher.php`
- **Description:** Add `ChangeDeployment::create()` inside `publish()` dan `rollback()` DB transactions supaya Branch Dashboard "Recent IT Deployments" auto-populate.
- [x] Add ChangeDeployment creation in `publish()` method inside DB transaction
- [x] Add ChangeDeployment creation in `rollback()` method inside DB transaction

## Task 4: FormEngine Success State Routing Fix
- **Requirement:** Req 3
- **Priority:** 🔴 Critical (Wrong Navigation)
- **Files:** `resources/views/livewire/runtime/form-engine.blade.php`
- **Description:** Fix success state links — replace `studio.dashboard` dengan `runtime.portal`, replace `studio.monitor` dengan "Start New" link. Add conditional "Return to Ops" untuk branch_manager dalam Staff View.
- [x] Replace "Return to Dashboard" link → `runtime.portal`
- [x] Replace "View in Monitor" link → "Start New" link ke same feature
- [x] Add conditional "Return to Ops" link untuk branch_manager in staff view

## Task 5: Standardize Audit Trail System
- **Requirement:** Req 7
- **Priority:** 🟡 High
- **Files:** `app/Kernel/Audit/AuditLog.php`, `app/Http/Controllers/Branch/ViewToggleController.php`, `app/Http/Middleware/LogFeatureAccess.php`
- **Description:** Extend `AuditLog::record()` untuk support `branch_id`, `description`, `payload`. Migrate `AuditTrail` usages ke `AuditLog`.
- [x] Extend `AuditLog::record()` signature dengan optional `branchId`, `description`, `payload` params
- [x] Update `AuditLog` model `$casts` untuk include `payload`
- [x] Migrate `ViewToggleController` dari `AuditTrail::create()` ke `AuditLog::record()`
- [x] Migrate `LogFeatureAccess` middleware dari `AuditTrail::create()` ke `AuditLog::record()`

## Task 6: StaffPortal Branch Filtering & Health Display
- **Requirement:** Req 1, 15
- **Priority:** 🟡 High
- **Files:** `app/Livewire/Runtime/StaffPortal.php`, `resources/views/livewire/runtime/staff-portal.blade.php`
- **Description:** Add health status display (availability badge, health error, disabled launch for unavailable). Add IT contact info for empty state.
- [x] Update `StaffPortal::render()` — add health check per feature
- [x] Update Blade view — add availability badge per feature card
- [x] Update Blade view — disable launch link for unavailable features
- [x] Update Blade view — show health error message
- [x] Update Blade view — show IT contact info in empty state

## Task 7: FormEngine Branch Validation
- **Requirement:** Req 2
- **Priority:** 🟡 High
- **Files:** `app/Livewire/Runtime/FormEngine.php`
- **Description:** Add redirect ke StaffPortal dengan error message jika `PageLoader` return null (feature not available for branch).
- [x] Update `mount()` — add null check with redirect and flash message

## Task 8: FormEngine Field Validation
- **Requirement:** Req 4
- **Priority:** 🟡 High
- **Files:** `app/Livewire/Runtime/FormEngine.php`, `resources/views/livewire/runtime/form-engine.blade.php`
- **Description:** Add field validation dalam `next()` method berdasarkan `is_required` dan `data_type`. Add error display dalam Blade view.
- [x] Add validation rules generation dari field definitions in `next()`
- [x] Add `@error` directives dalam form-engine Blade view untuk setiap field

## Task 9: FormEngine Submission Logging
- **Requirement:** Req 16
- **Priority:** 🟡 High
- **Files:** `app/Livewire/Runtime/FormEngine.php`
- **Description:** Add `ui_submission_logs` insert dalam `submit()` method selepas BindingResolver dan sebelum FlowOrchestrator. Wrap dalam try/catch.
- [x] Add `DB::table('ui_submission_logs')->insert()` in `submit()`
- [x] Wrap in try/catch with Log::warning

## Task 10: BranchDashboard Health Check Scoping
- **Requirement:** Req 8
- **Priority:** 🟡 High
- **Files:** `app/Livewire/Branch/BranchDashboard.php`
- **Description:** Scope health check count kepada published features sahaja. Fix notifications section untuk filter by published features.
- [x] Update `$activeIssues` query — add `whereIn('feature_id', $publishedFeatureIds)`
- [x] Update notifications `$healthIssues` query — filter by published features

## Task 11: ScopeResolver Cache Compatibility
- **Requirement:** Req 12
- **Priority:** 🟡 High
- **Files:** `app/Studio/Scoping/ScopeResolver.php`
- **Description:** Replace `Cache::tags()` dengan cache key registry pattern supaya compatible dengan semua cache drivers.
- [x] Update `resolve()` — register cache keys in registry
- [x] Update `clearCache()` — use `Cache::forget()` instead of `Cache::tags()`
- [x] Update `clearFeatureCache()` — iterate registry keys and forget

## Task 12: Branch Layout Query Optimization
- **Requirement:** Req 11
- **Priority:** 🟠 Medium
- **Files:** `resources/views/layouts/branch.blade.php`
- **Description:** Rename variable to avoid conflicts. Inline query acceptable for simple count.
- [x] Rename variable to `$sidebarOpenTickets` to avoid conflicts

## Task 13: Runtime Sidebar Icon Rendering
- **Requirement:** Req 9
- **Priority:** 🟠 Medium
- **Files:** `resources/views/livewire/runtime/sidebar.blade.php`
- **Description:** Replace empty icon comment dengan actual SVG rendering. Add default icon fallback.
- [x] Add default SVG icon for all menu items
- [x] Remove empty icon comment

## Task 14: Admin Sidebar User Roles Fix
- **Requirement:** Req 10
- **Priority:** 🟠 Medium
- **Files:** `resources/views/layouts/admin.blade.php`
- **Description:** Add clarifying subtitle ke "User Roles" link explaining navigation path.
- [x] Add subtitle text "via Staff page" ke User Roles nav item

## Task 15: PublishGateValidator Naming Fix
- **Requirement:** Req 13
- **Priority:** 🟠 Medium
- **Files:** `app/Studio/Publishing/PublishGateValidator.php`
- **Description:** Rename `versionIsDraft()` ke `versionIsApproved()`. Fix logic to check for approved status. Update check key dan message.
- [x] Rename method `versionIsDraft()` → `versionIsApproved()`
- [x] Fix logic — check `approved` instead of `draft`
- [x] Update key `'version_is_draft'` → `'version_is_approved'`
- [x] Update message to "Version must be in approved status to publish"
- [x] Update reference in `validate()` method
