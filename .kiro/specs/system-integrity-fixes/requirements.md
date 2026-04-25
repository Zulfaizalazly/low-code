# Requirements Document

## Introduction

Hasil daripada audit menyeluruh 3 bahagian (Architecture & Data Flow, UI/Frontend Layer, Data Integrity & Edge Cases), 16 isu telah dikenal pasti merentasi sistem Ar-Rahnu low-code platform. Isu-isu ini merangkumi wiring gaps antara Studio → Branch → Worker, UI routing errors, broken notifications, missing runtime validation, dual audit model conflicts, dan missing deployment tracking. Spec ini merangkumi semua fixes yang diperlukan untuk memastikan sistem betul-betul reflect dan berinteraksi antara ketiga-tiga layer (Studio/Admin, Branch Manager, Worker/Staff) dengan integrity penuh.

## Glossary

- **StaffPortal**: Livewire component (`Runtime/StaffPortal.php`) yang memaparkan senarai features kepada Worker/Staff untuk execution
- **FormEngine**: Livewire component (`Runtime/FormEngine.php`) yang render dynamic forms dan submit ke FlowOrchestrator
- **BranchDashboard**: Livewire component (`Branch/BranchDashboard.php`) yang memaparkan operational overview kepada Branch Manager
- **ApprovalService**: Service class (`Studio/Publishing/ApprovalService.php`) yang menguruskan submit/approve/reject workflow
- **VersionPublisher**: Service class (`Studio/Publishing/VersionPublisher.php`) yang menguruskan publish dan rollback
- **ScopeResolver**: Service class (`Studio/Scoping/ScopeResolver.php`) yang resolve scope overrides berdasarkan user/branch/entity context
- **AuditLog**: Kernel-level audit class (`Kernel/Audit/AuditLog.php`) untuk Studio operations
- **AuditTrail**: Model-level audit class (`Models/AuditTrail.php`) untuk Branch operations
- **ChangeDeployment**: Model (`Models/Branch/ChangeDeployment.php`) yang track IT deployments visible to branches
- **PageLoader**: Service class (`Runtime/UI/PageLoader.php`) yang load published page definitions dengan scope overrides

## Requirements

### Requirement 1: Branch-Scoped Feature Filtering di StaffPortal

**User Story:** Sebagai Worker/Staff, saya hanya mahu nampak features yang available untuk branch saya, supaya saya tak nampak features yang bukan untuk branch saya.

#### Acceptance Criteria

1. THE StaffPortal SHALL filter published features berdasarkan `branch_id` user yang authenticated
2. THE StaffPortal SHALL guna `ScopeResolver` untuk check feature availability per branch
3. THE StaffPortal SHALL paparkan domain badge dan availability status untuk setiap feature (sama seperti Branch AvailableFeatures view)
4. THE StaffPortal SHALL paparkan health indicator — jika feature degraded/unavailable, Worker perlu nampak warning sebelum launch
5. WHEN tiada features available untuk branch tersebut, THE StaffPortal SHALL paparkan mesej "No features available for your branch" dengan IT contact info

### Requirement 2: Branch Access Validation di FormEngine

**User Story:** Sebagai sistem, saya perlu validate bahawa Worker yang access feature memang ada hak untuk branch tersebut, supaya Worker dari Branch A tak boleh execute feature yang hanya untuk Branch B.

#### Acceptance Criteria

1. THE FormEngine SHALL validate bahawa authenticated user's `branch_id` ada access kepada feature yang diminta semasa `mount()`
2. IF user tak ada access kepada feature untuk branch mereka, THEN THE FormEngine SHALL redirect ke StaffPortal dengan error message "Feature not available for your branch"
3. THE FormEngine SHALL guna `ScopeResolver` untuk resolve branch-specific overrides pada page definition
4. THE validation SHALL berlaku sebelum page loading dan form initialization

### Requirement 3: FormEngine Success State Routing Fix

**User Story:** Sebagai Worker/Staff, selepas submit form, saya mahu diarahkan ke portal yang sesuai dengan role saya, bukan ke Studio dashboard.

#### Acceptance Criteria

1. THE FormEngine success state SHALL paparkan "Return to Portal" link yang navigate ke `runtime.portal` route (bukan `studio.dashboard`)
2. THE FormEngine success state SHALL paparkan "Start New" link yang navigate ke feature yang sama untuk new submission
3. THE FormEngine success state SHALL TIDAK paparkan link ke `studio.monitor` kerana Worker tak ada access ke Studio
4. IF user adalah `branch_manager` dalam Staff View mode, THEN THE FormEngine success state BOLEH paparkan "Return to Ops" link ke `branch.dashboard`

### Requirement 4: FormEngine Field Validation

**User Story:** Sebagai Worker/Staff, saya mahu form fields di-validate sebelum proceed ke next step, supaya saya tak submit data yang incomplete atau invalid.

#### Acceptance Criteria

1. THE FormEngine SHALL validate required fields sebelum allow `next()` step transition
2. THE FormEngine SHALL validate field data types berdasarkan `data_type` dari form_fields table (string, integer, decimal, date, boolean)
3. THE FormEngine SHALL paparkan validation error messages di bawah field yang bermasalah
4. THE FormEngine SHALL prevent submission jika ada required fields yang kosong
5. THE FormEngine SHALL log submission ke `ui_submission_logs` table selepas successful submit

### Requirement 5: VersionPublisher Auto-Create ChangeDeployment

**User Story:** Sebagai Branch Manager, saya mahu nampak deployment baru secara automatik dalam dashboard apabila HQ publish atau rollback feature, supaya saya sentiasa aware tentang system changes.

#### Acceptance Criteria

1. WHEN VersionPublisher successfully publishes a version, IT SHALL auto-create `ChangeDeployment` record dengan `is_visible_to_branches = true`, `deployed_at = now()`, dan `change_summary` dari version's `change_summary` field
2. WHEN VersionPublisher successfully rolls back a version, IT SHALL auto-create `ChangeDeployment` record dengan `change_summary` = "Rollback to v{version_no}: {reason}" dan `is_visible_to_branches = true`
3. THE ChangeDeployment record SHALL include `feature_id`, `feature_version_id`, dan `deployed_by` (user ID yang perform action)
4. THE ChangeDeployment creation SHALL berlaku dalam DB transaction yang sama dengan publish/rollback operation

### Requirement 6: Fix ApprovalService Reviewer Notification

**User Story:** Sebagai Reviewer, saya mahu terima notification apabila Developer submit feature untuk review, supaya saya boleh review tepat pada masanya.

#### Acceptance Criteria

1. THE ApprovalService `notifyReviewers()` SHALL query users menggunakan Spatie `role()` scope (bukan `whereIn('role', ...)`)
2. THE ApprovalService SHALL notify users dengan roles: `reviewer`, `super-admin`, dan `system-admin`
3. THE ApprovalService `notifySubmitter()` SHALL resolve submitter dari `ApprovalWorkflow.submitted_by` (bukan `FeatureVersion.published_by`)
4. THE notification SHALL include feature name, version number, dan submitter name

### Requirement 7: Standardize Audit Trail System

**User Story:** Sebagai developer, saya mahu satu consistent audit trail system, supaya semua audit records boleh di-query dan di-display dengan format yang sama.

#### Acceptance Criteria

1. THE system SHALL standardize pada SATU audit approach — `AuditLog` class SHALL menjadi primary audit mechanism
2. THE `AuditLog::record()` method SHALL di-extend untuk support optional `branch_id`, `description`, dan `payload` fields
3. THE `AuditTrail` model usages (ViewToggleController, LogFeatureAccess) SHALL di-migrate untuk guna `AuditLog::record()` instead
4. THE `audit_trails` table migration SHALL di-update untuk include semua fields dari kedua-dua models: `auditable_type`, `auditable_id`, `action`, `old_values`, `new_values`, `user_id`, `branch_id`, `description`, `payload`, `ip_address`, `user_agent`, `performed_at`
5. THE Studio AuditLogs view SHALL boleh display kedua-dua Studio dan Branch audit events

### Requirement 8: BranchDashboard Health Check Scoping

**User Story:** Sebagai Branch Manager, saya mahu health status di dashboard reflect issues yang relevant kepada branch saya sahaja, bukan global issues.

#### Acceptance Criteria

1. THE BranchDashboard health status indicator SHALL scope `FeatureHealthCheck` issues kepada features yang published dan accessible oleh branch tersebut
2. THE health status count SHALL TIDAK include issues dari features yang tak available untuk branch tersebut
3. THE notifications section SHALL hanya paparkan health issues untuk features yang relevant kepada branch tersebut

### Requirement 9: Runtime Sidebar Icon Rendering

**User Story:** Sebagai Worker/Staff, saya mahu sidebar navigation ada icons yang proper, supaya navigation lebih intuitive.

#### Acceptance Criteria

1. THE Runtime Sidebar SHALL render icons berdasarkan `$item->icon` value dari MenuManager
2. IF `$item->icon` is null atau empty, THE Sidebar SHALL render default icon
3. THE icon rendering SHALL support SVG icon set yang consistent dengan design system sedia ada

### Requirement 10: Admin Sidebar User Roles Navigation Fix

**User Story:** Sebagai Super Admin, saya mahu "User Roles" link di Admin sidebar navigate ke page yang betul, supaya saya boleh manage user roles tanpa confusion.

#### Acceptance Criteria

1. THE Admin sidebar "User Roles" link SHALL navigate ke `admin.staff` route (sebagai interim, kerana role management diakses melalui staff detail)
2. THE Admin sidebar SHALL paparkan tooltip atau subtitle yang explain "Manage roles via Staff detail page"
3. ALTERNATIVELY, IF dedicated user roles listing page dibuat, THE link SHALL navigate ke page tersebut

### Requirement 11: Branch Sidebar Query Optimization

**User Story:** Sebagai developer, saya mahu layout files tidak mengandungi inline database queries, supaya code lebih maintainable dan performant.

#### Acceptance Criteria

1. THE Branch layout (`layouts/branch.blade.php`) SHALL TIDAK contain inline `SupportTicket::forUser()` query
2. THE open ticket count SHALL di-pass sebagai shared view data melalui View Composer atau Livewire layout property
3. THE query SHALL execute sekali sahaja per request, bukan setiap kali layout render

### Requirement 12: ScopeResolver Cache Compatibility

**User Story:** Sebagai developer, saya mahu ScopeResolver cache mechanism compatible dengan semua Laravel cache drivers, supaya system tak crash kalau guna file/database cache.

#### Acceptance Criteria

1. THE ScopeResolver `clearCache()` method SHALL TIDAK guna `Cache::tags()` kerana ia hanya supported oleh Redis/Memcached
2. THE ScopeResolver SHALL guna cache key prefix pattern untuk clear specific caches: `Cache::forget($key)` dengan predictable key format
3. THE `clearFeatureCache()` method SHALL iterate dan forget individual cache keys instead of using tags
4. THE `resolve()` method cache key format SHALL remain consistent untuk enable targeted cache clearing

### Requirement 13: PublishGateValidator Naming Fix

**User Story:** Sebagai developer, saya mahu publish gate check names accurately describe apa yang mereka validate, supaya debugging dan reporting lebih jelas.

#### Acceptance Criteria

1. THE `versionIsDraft` check SHALL di-rename kepada `versionIsApproved` atau nama yang accurately describe validation logic
2. THE check message SHALL clearly state "Version must be in approved status to publish"
3. THE rename SHALL reflect dalam `publish_validations` table records dan Release Center UI

### Requirement 14: Secure Login Routes for Production

**User Story:** Sebagai developer, saya mahu development-only login routes dilindungi supaya tak accessible dalam production environment.

#### Acceptance Criteria

1. THE login shortcut routes (`/login-hq`, `/login-admin`, `/login-manager`, `/login-teller`, `/login-admin-panel`) SHALL hanya available dalam `local` dan `testing` environments
2. THE routes SHALL di-wrap dengan `App::environment(['local', 'testing'])` guard
3. IN production environment, THE routes SHALL return 404

### Requirement 15: Staff Portal Feature Health Display

**User Story:** Sebagai Worker/Staff, saya mahu nampak health status setiap feature sebelum launch, supaya saya tahu kalau feature tu ada masalah.

#### Acceptance Criteria

1. THE StaffPortal SHALL paparkan availability badge (available/degraded/unavailable) untuk setiap feature card
2. IF feature status adalah `degraded`, THE StaffPortal SHALL paparkan amber warning indicator
3. IF feature status adalah `unavailable`, THE StaffPortal SHALL paparkan red indicator dan disable launch link
4. THE StaffPortal SHALL paparkan health error message jika ada

### Requirement 16: FormEngine Submission Logging

**User Story:** Sebagai sistem, saya mahu setiap form submission di-log ke `ui_submission_logs` table, supaya ada complete audit trail untuk semua user submissions.

#### Acceptance Criteria

1. THE FormEngine `submit()` method SHALL create `ui_submission_logs` record dengan `page_definition_id`, `page_version` (dari feature version), `form_data` (JSON), `submitted_by` (user ID), dan `submitted_at` (timestamp)
2. THE logging SHALL berlaku selepas successful BindingResolver resolution dan sebelum FlowOrchestrator execution
3. IF logging fails, THE FormEngine SHALL log warning dan continue dengan execution (non-blocking)
