# Implementation Plan: Frontend Dashboard Visibility Enhancements

## Overview

Incremental enhancements to existing Branch Operations Dashboard Livewire components. All changes are additive — no new components, models, or routes. The implementation adds 2 config keys, enhances 3 Livewire component `render()` methods with new computed data, and updates 3 Blade templates with new UI sections.

## Tasks

- [x] 1. Add new configuration keys to `config/branch.php`
  - Add `usage_drop_threshold_percent` (default: 30) under `dashboard`
  - Add `perf_degradation_threshold_per_hour` (default: 2) under `dashboard`
  - _Requirements: 8.3, 10.4_

- [x] 2. Enhance BranchDashboard component with weekly performance, alerts, and notifications
  - [x] 2.1 Add weekly performance summary computation to `BranchDashboard::render()`
    - Query `automation_execution_logs` for current week: total executions, completed count, avg completion seconds
    - Count distinct active staff from `FeatureAccessLog::thisWeek()`
    - Compute `completion_rate` as `(completed / total) * 100`, defaulting to 100 when total is 0
    - Pass `$weeklyPerformance` array to view with keys: `total_accesses`, `total_executions`, `completion_rate`, `avg_completion_seconds`, `active_staff_count`
    - _Requirements: 8.2, 8.5_

  - [x] 2.2 Add usage drop detection to `BranchDashboard::render()`
    - For each feature, compare `FeatureAccessLog::thisWeek()->count()` vs previous week count
    - If `(previous - current) / previous * 100` exceeds `config('branch.dashboard.usage_drop_threshold_percent', 30)`, populate `$usageDropAlert`
    - Skip calculation when previous week count is 0 (avoid division by zero)
    - Pass `$usageDropAlert` (nullable array with `feature_name`, `current_week`, `previous_week`, `drop_percent`) to view
    - _Requirements: 8.3_

  - [x] 2.3 Add performance degradation alert to `BranchDashboard::render()`
    - If `$avgPerHour` < `config('branch.dashboard.perf_degradation_threshold_per_hour', 2)` and `now()->hour > 2`, set `$perfDegradationAlert` message
    - Pass `$perfDegradationAlert` (nullable string) to view
    - _Requirements: 10.4_

  - [x] 2.4 Add notification collection to `BranchDashboard::render()`
    - Collect unresolved `FeatureHealthCheck` records as "unavailable" notifications (deduplicated by feature_id)
    - Collect `ChangeDeployment::visibleToBranches()` deployed in last hour as "deployment" notifications
    - Pass `$notifications` array of `['type', 'message', 'time']` to view
    - _Requirements: 7.2, 7.4_

  - [x] 2.5 Add version diff to deployment tracker in `BranchDashboard::render()`
    - Enhance `$recentDeployments` query to eager-load `featureVersion`
    - For each deployment, query the previous `FeatureVersion` (highest `version_no` strictly less than current) for the same feature
    - Attach `previous_version_no` attribute to each deployment (null if first version)
    - _Requirements: 5.2_

  - [ ]* 2.6 Write property test: Previous version lookup correctness (Property 1)
    - **Property 1: Previous version lookup correctness**
    - Generate random version sequences for a feature, verify the computed previous version is always the highest version_no strictly less than the current
    - **Validates: Requirements 5.2**

  - [ ]* 2.7 Write property test: Notification generation from health checks and deployments (Property 2)
    - **Property 2: Notification generation from health checks and deployments**
    - Generate varied FeatureHealthCheck and ChangeDeployment records, verify notification list contains exactly one "unavailable" per unresolved issue and one "deployment" per recent visible deployment
    - **Validates: Requirements 7.2, 7.4**

  - [ ]* 2.8 Write property test: Weekly performance aggregation correctness (Property 3)
    - **Property 3: Weekly performance aggregation correctness**
    - Generate varied `automation_execution_logs` records, verify total_executions count, completion_rate percentage, and avg_completion_seconds arithmetic mean are correct; avg_completion_seconds is 0 when no completed records
    - **Validates: Requirements 8.2, 8.5**

  - [ ]* 2.9 Write property test: Usage drop detection (Property 4)
    - **Property 4: Usage drop detection**
    - Generate varied current/previous week access counts per feature, verify alert fires iff drop percentage exceeds threshold and previous count > 0
    - **Validates: Requirements 8.3**

  - [ ]* 2.10 Write property test: Performance degradation alert threshold (Property 6)
    - **Property 6: Performance degradation alert threshold**
    - Generate varied avgPerHour values and hour values, verify alert fires iff avgPerHour < threshold AND hour > 2
    - **Validates: Requirements 10.4**

- [x] 3. Update `dashboard.blade.php` with new UI sections
  - [x] 3.1 Add notification toast bar at top of dashboard
    - Render `$notifications` as dismissible toast cards (color-coded by type: rose for unavailable, blue for deployment)
    - Auto-dismiss via Alpine.js or Livewire polling
    - _Requirements: 7.2, 7.4_

  - [x] 3.2 Add version diff display in deployment tracker
    - Show `v{previous} → v{current}` next to each deployment entry; show "New feature" when `previous_version_no` is null
    - _Requirements: 5.2_

  - [x] 3.3 Add weekly performance summary card section
    - New card row below existing stats showing: total executions, completion rate, avg completion time (formatted as seconds/minutes), active staff count
    - _Requirements: 8.2, 8.5_

  - [x] 3.4 Add usage drop alert banner
    - Conditional amber banner showing feature name, previous/current counts, and drop percentage when `$usageDropAlert` is not null
    - _Requirements: 8.3_

  - [x] 3.5 Add performance degradation alert banner
    - Conditional rose banner showing `$perfDegradationAlert` message when not null
    - _Requirements: 10.4_

- [x] 4. Checkpoint — Verify dashboard enhancements
  - Ensure all tests pass, ask the user if questions arise.

- [x] 5. Enhance StaffActivity component with avg completion time and efficiency metrics
  - [x] 5.1 Add average completion time computation to `StaffActivity::render()`
    - Query `automation_execution_logs` for `AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at))` where status = 'completed', scoped to selected period
    - Return 0 when no completed records exist
    - Pass `$avgCompletionTime` (float, seconds) to view
    - _Requirements: 8.2_

  - [x] 5.2 Add staff efficiency metrics to `StaffActivity::render()`
    - Join `feature_access_logs` with `automation_execution_logs` to correlate user_id with execution data
    - Group by user_id to compute per-staff: `total_executions`, `success_rate` (completed/total × 100), `avg_completion_seconds`
    - Pass `$staffEfficiency` collection to view
    - _Requirements: 8.4_

  - [ ]* 5.3 Write property test: Staff efficiency metrics correctness (Property 5)
    - **Property 5: Staff efficiency metrics correctness**
    - Generate varied per-staff execution logs, verify total_executions count, success_rate percentage, and avg_completion_seconds are correctly computed per user; staff with zero executions show 0 for all metrics
    - **Validates: Requirements 8.4**

- [x] 6. Update `staff-activity.blade.php` with new UI sections
  - [x] 6.1 Add average completion time stat card
    - New card in the summary stats grid showing avg completion time formatted as human-readable duration (e.g., "2m 34s")
    - _Requirements: 8.2_

  - [x] 6.2 Add staff efficiency metrics table section
    - New table section below the staff activity table showing per-staff: name, total executions, success rate (color-coded), avg completion time
    - _Requirements: 8.4_

- [x] 7. Enhance AvailableFeatures Blade view with expandable documentation section
  - Add a "View Details" toggle button per feature card in `available-features.blade.php`
  - When toggled, expand to show the feature's full `description` text as a lightweight documentation panel
  - Use Alpine.js `x-show` for expand/collapse behavior
  - No backend changes needed — `description` is already loaded
  - _Requirements: 6.5_

- [x] 8. Final checkpoint — Verify all enhancements
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- All changes are additive to existing files — no new components, models, or routes
- The design has 6 correctness properties; property tests are placed close to their related implementation tasks
- Each task references specific requirement clauses for traceability
- PHP/Laravel/Livewire/Blade is used throughout — matching the existing codebase
