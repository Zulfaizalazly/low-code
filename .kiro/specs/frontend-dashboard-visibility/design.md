# Design Document: Frontend Dashboard Visibility Enhancements

## Overview

This spec enhances the existing Branch Operations Dashboard (built by the `branch-operations-dashboard` spec) with additional visibility features that were not covered in the initial implementation. The existing infrastructure — 4 Livewire components (`BranchDashboard`, `StaffActivity`, `AvailableFeatures`, `BranchSupport`), 4 Eloquent models (`FeatureAccessLog`, `FeatureHealthCheck`, `ChangeDeployment`, `SupportTicket`), branch layout, config, routes, and middleware — is fully operational.

This design focuses exclusively on the **gaps** between the original requirements and what is currently implemented:

1. **Version Diff Display** (Req 5.2) — Show previous vs current version info in the deployment tracker
2. **Notification System** (Req 7.2, 7.4) — Flash/toast notifications for feature unavailability and new deployments
3. **Average Completion Time** (Req 8.2) — Calculate from `automation_execution_logs` table
4. **Usage Drop Detection** (Req 8.3) — Compare current vs previous week usage, alert on significant drops
5. **Staff Efficiency Metrics** (Req 8.4) — Per-staff, per-feature completion time and success rate
6. **Weekly Performance Summary** (Req 8.5) — Summary card showing weekly branch performance
7. **Feature Documentation Link** (Req 6.5) — Link to feature description/docs from Available Features page
8. **Performance Degradation Alerts** (Req 10.4) — Alert when avgPerHour drops below threshold

All changes are incremental enhancements to existing components — no new Livewire components, models, or routes are created.

## Architecture

```mermaid
graph TB
    subgraph "Existing Components (Enhanced)"
        BDC[BranchDashboard.php<br/>+ weekly summary<br/>+ usage drop alerts<br/>+ perf degradation alerts<br/>+ notifications]
        SAC[StaffActivity.php<br/>+ avg completion time<br/>+ staff efficiency metrics]
        AFC[AvailableFeatures.php<br/>+ documentation links]
    end

    subgraph "Existing Data Sources"
        FAL[FeatureAccessLog]
        AEL[automation_execution_logs]
        CD[ChangeDeployment]
        FHC[FeatureHealthCheck]
        FV[FeatureVersion]
    end

    subgraph "New Config Keys"
        CFG["config/branch.php<br/>+ usage_drop_threshold_percent<br/>+ perf_degradation_threshold_per_hour"]
    end

    BDC --> FAL
    BDC --> AEL
    BDC --> CD
    BDC --> FHC
    BDC --> CFG

    SAC --> FAL
    SAC --> AEL
    SAC --> CFG

    AFC --> FV
</graph>
```

The approach is purely additive: new computed properties and view sections are added to existing Livewire `render()` methods, and new Blade sections are appended to existing view templates. Livewire's existing polling handles real-time refresh for all new data.

## Components and Interfaces

### 1. BranchDashboard Enhancements

**File:** `app/Livewire/Branch/BranchDashboard.php`

New data passed to the view from `render()`:

| Variable | Type | Source | Description |
|----------|------|--------|-------------|
| `$weeklyPerformance` | `array` | Computed | `['total_accesses', 'total_executions', 'completion_rate', 'avg_completion_seconds', 'active_staff_count']` |
| `$usageDropAlert` | `?array` | Computed | `['feature_name', 'current_week', 'previous_week', 'drop_percent']` or null |
| `$perfDegradationAlert` | `?string` | Computed | Alert message when avgPerHour < threshold, or null |
| `$notifications` | `array` | Computed | Array of `['type' => 'unavailable'|'deployment', 'message' => string, 'time' => Carbon]` |

New logic in `render()`:
- **Weekly Performance**: Query `automation_execution_logs` for current week — count total, count completed, calculate avg `TIMESTAMPDIFF(SECOND, started_at, completed_at)` for completed records. Count distinct active staff from `FeatureAccessLog::thisWeek()`.
- **Usage Drop Detection**: For each feature, compare `FeatureAccessLog::thisWeek()->count()` vs last week's count. If any feature drops by more than `config('branch.dashboard.usage_drop_threshold_percent', 30)`, populate `$usageDropAlert`.
- **Performance Degradation**: If `$avgPerHour` < `config('branch.dashboard.perf_degradation_threshold_per_hour', 2)` and current hour > 2 (avoid false positives early morning), set `$perfDegradationAlert`.
- **Notifications**: Collect recent `FeatureHealthCheck::hasIssues()` created in last polling interval as "unavailable" notifications. Collect `ChangeDeployment::visibleToBranches()` deployed in last hour as "deployment" notifications.

**Version Diff in Deployment Tracker**: Enhance the `$recentDeployments` query to eager-load `featureVersion` and add a `previous_version_no` attribute by querying the prior `FeatureVersion` for the same feature.

### 2. StaffActivity Enhancements

**File:** `app/Livewire/Branch/StaffActivity.php`

New data passed to the view:

| Variable | Type | Source | Description |
|----------|------|--------|-------------|
| `$avgCompletionTime` | `float` | `automation_execution_logs` | Average seconds for completed executions in selected period |
| `$staffEfficiency` | `Collection` | Computed | Per-staff metrics: `['user_id', 'name', 'avg_completion_seconds', 'success_rate', 'total_executions']` |

New logic in `render()`:
- **Average Completion Time**: `DB::table('automation_execution_logs')->where('status','completed')->avg(DB::raw('TIMESTAMPDIFF(SECOND, started_at, completed_at)'))` scoped to period.
- **Staff Efficiency**: Join `automation_execution_logs` with `flow_definitions` → `feature_versions` → `features`, then join `feature_access_logs` to correlate user_id. Group by user_id to get per-staff avg completion time and success rate (completed / total). This is displayed as an additional section below the staff table.

### 3. AvailableFeatures Enhancements

**File:** `app/Livewire/Branch/AvailableFeatures.php`

Enhancement: Add a `$feature->description` display (already present) and a "View Guide" link that points to the feature's description or a documentation anchor. Since features already have a `description` field and the `FeatureVersion` has a `change_summary`, we expose a simple expandable section or tooltip showing the feature's full description as documentation.

No new backend logic needed — the `description` field is already loaded. The Blade view will add a "View Details" toggle that expands to show the full description text as a lightweight documentation panel.

### 4. Configuration Additions

**File:** `config/branch.php`

New keys under `dashboard`:

```php
'usage_drop_threshold_percent' => 30,       // Alert when feature usage drops by this %
'perf_degradation_threshold_per_hour' => 2, // Alert when avg/hr falls below this
```

### 5. View Enhancements

**dashboard.blade.php** additions:
- Notification toast bar at top (Livewire-driven, auto-dismiss)
- Version diff display in deployment tracker (show `v{prev} → v{current}`)
- Weekly performance summary card section
- Usage drop alert banner
- Performance degradation alert banner

**staff-activity.blade.php** additions:
- Average completion time stat card
- Staff efficiency metrics table section

**available-features.blade.php** additions:
- "View Details" expandable section per feature card showing full description as documentation

## Data Models

No new database tables or migrations are required. All data is sourced from existing tables:

### Existing Tables Used

| Table | New Usage |
|-------|-----------|
| `automation_execution_logs` | Avg completion time, staff efficiency, weekly performance |
| `feature_access_logs` | Usage drop detection (week-over-week comparison) |
| `feature_health_checks` | Unavailability notifications |
| `change_deployments` | Deployment notifications, version diff |
| `feature_versions` | Version number for diff display |

### Key Queries

**Average Completion Time:**
```sql
SELECT AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at))
FROM automation_execution_logs
WHERE status = 'completed'
  AND started_at BETWEEN :week_start AND :week_end
```

**Usage Drop Detection:**
```sql
-- Current week count per feature
SELECT feature_id, COUNT(*) as current_count
FROM feature_access_logs
WHERE branch_id = :branch_id
  AND accessed_at BETWEEN :this_week_start AND :this_week_end
GROUP BY feature_id

-- Previous week count per feature
SELECT feature_id, COUNT(*) as previous_count
FROM feature_access_logs
WHERE branch_id = :branch_id
  AND accessed_at BETWEEN :last_week_start AND :last_week_end
GROUP BY feature_id
```

**Staff Efficiency (per-staff completion metrics):**
```sql
SELECT
    fal.user_id,
    COUNT(ael.id) as total_executions,
    SUM(CASE WHEN ael.status = 'completed' THEN 1 ELSE 0 END) as completed,
    AVG(CASE WHEN ael.status = 'completed'
        THEN TIMESTAMPDIFF(SECOND, ael.started_at, ael.completed_at)
        ELSE NULL END) as avg_seconds
FROM feature_access_logs fal
JOIN automation_execution_logs ael ON ael.feature_version_id = fal.feature_version_id
WHERE fal.branch_id = :branch_id
  AND fal.accessed_at BETWEEN :period_start AND :period_end
GROUP BY fal.user_id
```

**Version Diff (previous version for deployment):**
```sql
SELECT version_no FROM feature_versions
WHERE feature_id = :feature_id
  AND version_no < :current_version_no
ORDER BY version_no DESC
LIMIT 1
```


## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Previous version lookup correctness

*For any* feature with a sequence of version numbers and a deployment referencing a specific version, the computed previous version number SHALL be the highest version_no that is strictly less than the deployment's current version_no for the same feature. If no previous version exists, the result SHALL be null.

**Validates: Requirements 5.2**

### Property 2: Notification generation from health checks and deployments

*For any* set of FeatureHealthCheck records and ChangeDeployment records, the generated notification list SHALL contain exactly one "unavailable" notification for each feature with an active (unresolved) health issue, and exactly one "deployment" notification for each deployment visible to branches that was deployed within the notification window. No notifications SHALL be generated for resolved health checks or deployments outside the window.

**Validates: Requirements 7.2, 7.4**

### Property 3: Weekly performance aggregation correctness

*For any* set of `automation_execution_logs` records within a week, the weekly performance summary SHALL correctly compute: total_executions as the count of all records, completion_rate as (completed count / total count) × 100, and avg_completion_seconds as the arithmetic mean of `TIMESTAMPDIFF(SECOND, started_at, completed_at)` for completed records only. When no completed records exist, avg_completion_seconds SHALL be 0.

**Validates: Requirements 8.2, 8.5**

### Property 4: Usage drop detection

*For any* feature with access logs in both the current and previous week, the system SHALL generate a usage drop alert if and only if `(previous_count - current_count) / previous_count × 100` exceeds the configured `usage_drop_threshold_percent`. When previous_count is zero, no drop alert SHALL be generated.

**Validates: Requirements 8.3**

### Property 5: Staff efficiency metrics correctness

*For any* set of staff members with associated execution logs, the per-staff efficiency metrics SHALL correctly compute: total_executions as the count of executions per user, success_rate as (completed / total) × 100 per user, and avg_completion_seconds as the arithmetic mean of completion durations for completed executions per user. Staff with zero executions SHALL show 0 for all metrics.

**Validates: Requirements 8.4**

### Property 6: Performance degradation alert threshold

*For any* branch with a computed avgPerHour value and a current hour > 2, the system SHALL generate a performance degradation alert if and only if avgPerHour is strictly less than the configured `perf_degradation_threshold_per_hour`. When the current hour is ≤ 2, no degradation alert SHALL be generated regardless of avgPerHour value.

**Validates: Requirements 10.4**

## Error Handling

### Computation Edge Cases
- **Division by zero in usage drop**: When previous week count is 0 for a feature, skip drop calculation for that feature (no alert generated — can't compute percentage drop from zero baseline)
- **Division by zero in completion rate**: When total executions is 0, return 100% completion rate (no failures if nothing ran)
- **Division by zero in avgPerHour**: Already handled in existing code with ternary guard (`now()->hour > 0`)
- **Empty execution logs**: When `automation_execution_logs` has no records for the period, avg completion time returns 0 and weekly summary shows zeroes
- **No previous version**: When a deployment is for version 1 (first version), previous_version_no is null and the view shows "New feature" instead of a diff

### Notification Edge Cases
- **Stale health checks**: Only unresolved (`resolved_at IS NULL`) health checks generate notifications — resolved issues are excluded
- **Duplicate notifications**: Deployments and health checks are deduplicated by feature_id — only the most recent per feature is shown
- **Polling overlap**: Notifications are based on record timestamps, not polling cycles, so duplicate display is avoided

### Configuration Fallbacks
- All new config keys have sensible defaults: `usage_drop_threshold_percent` defaults to 30, `perf_degradation_threshold_per_hour` defaults to 2
- Missing config keys fall back to defaults via `config('key', default)` pattern

## Testing Strategy

### Property-Based Tests (PHPUnit + Faker data providers)

PBT is appropriate for this feature because the core logic involves pure computations (averages, percentages, threshold comparisons, version lookups) with clear input/output behavior and universal properties across varied inputs.

- **Library**: PHPUnit with custom data providers using `Faker` to generate randomized inputs (100+ iterations per property)
- **Tag format**: `Feature: frontend-dashboard-visibility, Property {N}: {title}`

Property tests cover:
1. Previous version lookup across varied version sequences
2. Notification generation from varied health check and deployment data
3. Weekly performance aggregation across varied execution log data
4. Usage drop detection across varied access log distributions
5. Staff efficiency metric computation across varied per-staff execution data
6. Performance degradation alert threshold logic across varied avgPerHour values and hours

### Unit Tests (PHPUnit + Livewire testing)

- **Version diff display**: Render BranchDashboard with deployments that have previous versions, verify `v{prev} → v{current}` appears in output
- **Notification rendering**: Render BranchDashboard with active health issues and recent deployments, verify notification toasts appear
- **Feature documentation toggle**: Render AvailableFeatures with features that have descriptions, verify "View Details" section renders with description text
- **Weekly summary card**: Render BranchDashboard, verify weekly performance section shows all 5 metrics
- **Usage drop alert banner**: Render BranchDashboard with usage drop data, verify alert banner appears with correct feature name and percentages
- **Performance degradation banner**: Render BranchDashboard with low avgPerHour, verify degradation alert appears
- **Staff efficiency table**: Render StaffActivity, verify efficiency metrics section shows per-staff data
- **Avg completion time card**: Render StaffActivity, verify avg completion time stat card appears

### Integration Tests

- **End-to-end notification flow**: Create FeatureHealthCheck with "unavailable" status, render BranchDashboard, verify notification appears
- **End-to-end usage drop**: Seed access logs with significant drop, render BranchDashboard, verify alert appears
- **Config override**: Set custom threshold values, verify alerts respect overridden config

### Example-Based Tests

- **Feature documentation link** (Req 6.5): Render AvailableFeatures with a feature that has a description, verify the expandable documentation section is present
- **Empty states**: Verify no alerts/notifications when all features are healthy, no usage drops, and performance is above threshold
