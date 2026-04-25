# Requirements Document

## Introduction

The Branch Operations Dashboard provides Branch Managers with operational visibility into branch activity, staff feature usage, feature availability, IT deployment tracking, and support ticket management. The system combines a data layer (4 Eloquent models with migrations, scopes, relationships, and seeders) with a UI layer (4 Livewire components with Blade views) to deliver a complete branch monitoring solution. The existing codebase has model stubs, migrations, middleware, and Blade views already scaffolded — this spec covers completing the implementation to production-ready state. UI labels reference Malay terms where appropriate (e.g., "Tiket Sokongan" for support tickets, "Aktiviti Staf" for staff activity).

## Glossary

- **Dashboard_System**: The main Livewire component (`BranchDashboard.php`) that displays branch operational overview including stats cards, health status, deployment tracker, and feature availability table
- **Branch_Manager**: A user with the `branch_manager` role who monitors branch operations and staff activity but does not edit technical configurations
- **Branch_Staff**: Staff members (e.g., tellers) assigned to a branch who execute features for daily operations
- **Feature_Access_Logger**: The `FeatureAccessLog` Eloquent model and `LogFeatureAccess` middleware that record every feature access by branch staff
- **Health_Monitor**: The `FeatureHealthCheck` Eloquent model that tracks feature availability status (available, degraded, unavailable) and error conditions
- **Deployment_Tracker**: The `ChangeDeployment` Eloquent model that records IT deployments visible to branches, including version info and change summaries
- **Support_System**: The `SupportTicket` Eloquent model and `BranchSupport` Livewire component that handle IT support ticket creation, tracking, and resolution
- **Staff_Monitor**: The `StaffActivity` Livewire component that displays staff usage patterns, activity timelines, and inactive staff alerts
- **Feature_Registry**: The `AvailableFeatures` Livewire component that lists all published features with availability status, health indicators, and usage metrics
- **Branch_Dashboard_Seeder**: Database seeder that generates realistic test data for all four branch models to enable development and demo workflows

## Requirements

### Requirement 1: Feature Access Log Data Model

**User Story:** As a Branch Manager, I want feature access events recorded with user, feature, branch, and timestamp data, so that I can monitor staff activity patterns.

#### Acceptance Criteria

1. THE Feature_Access_Logger SHALL store user_id, feature_id, feature_version_id, branch_id, access_type, session_duration_seconds, accessed_at, and completed_at for each access event
2. THE Feature_Access_Logger SHALL provide a `forBranch($branchId)` scope that filters records by branch_id
3. THE Feature_Access_Logger SHALL provide a `recent($minutes)` scope that filters records accessed within the specified number of minutes from the current time
4. THE Feature_Access_Logger SHALL provide a `today()` scope that filters records where accessed_at falls on the current date
5. THE Feature_Access_Logger SHALL provide a `thisWeek()` scope that filters records where accessed_at falls between the start and end of the current week
6. THE Feature_Access_Logger SHALL define a `belongsTo` relationship to the User model and a `belongsTo` relationship to the Feature model
7. THE Feature_Access_Logger SHALL cast accessed_at and completed_at as datetime values
8. WHEN a user accesses a feature route `/f/{featureKey}`, THE Feature_Access_Logger SHALL create a new record via the LogFeatureAccess middleware with the authenticated user_id, resolved feature_id, published feature_version_id, user branch_id, access_type "view", and current timestamp as accessed_at

### Requirement 2: Feature Health Check Data Model

**User Story:** As a Branch Manager, I want feature availability status tracked and stored, so that I can see which features are operational, degraded, or unavailable.

#### Acceptance Criteria

1. THE Health_Monitor SHALL store feature_id, status (one of: available, degraded, unavailable), error_message, checked_at, resolved_at, resolution_note, and checked_by for each health check record
2. THE Health_Monitor SHALL provide a `hasIssues()` scope that filters records where status is "degraded" or "unavailable" and resolved_at is null
3. THE Health_Monitor SHALL provide a `forFeature($featureId)` scope that filters records by feature_id
4. THE Health_Monitor SHALL provide an `active()` scope that filters records where resolved_at is null
5. THE Health_Monitor SHALL define a `belongsTo` relationship to the Feature model
6. THE Health_Monitor SHALL cast checked_at and resolved_at as datetime values
7. THE Health_Monitor SHALL provide an `isResolved()` helper method that returns true when resolved_at is not null

### Requirement 3: Change Deployment Data Model

**User Story:** As a Branch Manager, I want IT deployment records tracked, so that I can see what system changes have been made and whether new features are available.

#### Acceptance Criteria

1. THE Deployment_Tracker SHALL store feature_id, feature_version_id, deployed_by, deployed_at, change_summary, is_visible_to_branches, and notified_at for each deployment record
2. THE Deployment_Tracker SHALL provide a `visibleToBranches()` scope that filters records where is_visible_to_branches is true
3. THE Deployment_Tracker SHALL provide a `recent($days)` scope that filters records deployed within the specified number of days from the current time
4. THE Deployment_Tracker SHALL define `belongsTo` relationships to the Feature model, FeatureVersion model, and User model (as deployed_by)
5. THE Deployment_Tracker SHALL cast deployed_at and notified_at as datetime values and is_visible_to_branches as boolean
6. THE Deployment_Tracker SHALL provide an `isNew()` helper method that returns true when deployed_at is within the configured `branch.dashboard.new_feature_badge_hours` threshold (default 24 hours)

### Requirement 4: Support Ticket Data Model

**User Story:** As a Branch Manager, I want support tickets stored with user, branch, category, priority, and status data, so that I can track IT support requests and their resolution.

#### Acceptance Criteria

1. THE Support_System SHALL store user_id, branch_id, title, description, category (one of: bug, feature_request, issue), priority (one of: low, medium, high, critical), status (one of: open, in_progress, resolved, closed), context_json, it_responder_id, response_note, responded_at, and resolved_at for each ticket
2. THE Support_System SHALL provide an `open()` scope that filters records where status is "open" or "in_progress"
3. THE Support_System SHALL provide a `forBranch($branchId)` scope that filters records by branch_id
4. THE Support_System SHALL provide a `forUser($userId)` scope that filters records by user_id
5. THE Support_System SHALL define a `belongsTo` relationship to the User model and a `belongsTo` relationship to the User model as responder (via it_responder_id)
6. THE Support_System SHALL cast context_json as array, responded_at as datetime, and resolved_at as datetime
7. THE Support_System SHALL provide `priority_color` and `status_color` computed attributes that return Tailwind color names based on the current priority and status values
8. THE Support_System SHALL provide an `isOpen()` helper method that returns true when status is "open" or "in_progress"

### Requirement 5: Database Migration for Branch Dashboard Tables

**User Story:** As a developer, I want database tables properly defined with indexes and foreign keys, so that the branch dashboard models have a reliable storage layer.

#### Acceptance Criteria

1. THE Dashboard_System SHALL create a `feature_access_logs` table with indexes on (user_id, accessed_at), (feature_id, accessed_at), (branch_id, accessed_at), and (access_type)
2. THE Dashboard_System SHALL create a `feature_health_checks` table with indexes on (feature_id, status) and (checked_at)
3. THE Dashboard_System SHALL create a `support_tickets` table with indexes on (user_id, status), (branch_id, status), and (status, priority)
4. THE Dashboard_System SHALL create a `change_deployments` table with indexes on (feature_id, deployed_at), (deployed_at), and (is_visible_to_branches)
5. THE Dashboard_System SHALL define a foreign key constraint on feature_access_logs.user_id referencing users.id with cascade on delete
6. THE Dashboard_System SHALL define a foreign key constraint on support_tickets.user_id referencing users.id with cascade on delete

### Requirement 6: Branch Dashboard Seeder

**User Story:** As a developer, I want realistic test data seeded for all branch dashboard models, so that I can develop and demo the dashboard with meaningful data.

#### Acceptance Criteria

1. THE Branch_Dashboard_Seeder SHALL generate feature access log records spanning the current week for each branch staff member, distributed across published features
2. THE Branch_Dashboard_Seeder SHALL generate feature health check records including at least one record with "degraded" status and one with "available" status
3. THE Branch_Dashboard_Seeder SHALL generate change deployment records for published features with is_visible_to_branches set to true, including at least one deployment within the last 24 hours to trigger the "New" badge
4. THE Branch_Dashboard_Seeder SHALL generate support ticket records with varied categories, priorities, and statuses including at least one open ticket and one resolved ticket with a response_note
5. WHEN the seeder is executed, THE Branch_Dashboard_Seeder SHALL associate all generated records with valid existing user_id, feature_id, and branch_id values from the database

### Requirement 7: Branch Operations Dashboard Component

**User Story:** As a Branch Manager, I want a dashboard overview showing active features count, staff currently active, usage statistics, health status, recent deployments, and feature availability, so that I can monitor branch operations at a glance.

#### Acceptance Criteria

1. THE Dashboard_System SHALL display the total count of published features as "Active Features"
2. THE Dashboard_System SHALL display the count of distinct users who accessed features within the configured active window (default 15 minutes) as "Staff Active"
3. THE Dashboard_System SHALL display the total feature access count for the current day as "Usage Today" and the total for the current week
4. THE Dashboard_System SHALL display a health status indicator: "All Systems Operational" (emerald) when zero active issues exist, "Minor Issues Detected" (amber) when 1-2 active issues exist, and "Service Disruption" (rose) when more than 2 active issues exist
5. THE Dashboard_System SHALL display the 5 most recent deployments visible to branches within the last 7 days, showing feature name, change summary, deployed_at timestamp, and a "New" badge for deployments within the configured threshold
6. THE Dashboard_System SHALL display a feature availability table listing all published features with their availability status (available/degraded/unavailable), last used timestamp, and error message when applicable
7. THE Dashboard_System SHALL display the count of open support tickets for the current user and a link to the IT Support page
8. THE Dashboard_System SHALL auto-refresh data using Livewire polling at the configured interval (default 30 seconds)

### Requirement 8: Staff Activity Monitoring Component

**User Story:** As a Branch Manager, I want to monitor staff feature usage with activity status, access counts, and inactive staff alerts, so that I can ensure productivity and identify training needs.

#### Acceptance Criteria

1. THE Staff_Monitor SHALL display a list of branch staff members (excluding the current manager) with their name, activity status (Active/Idle/Inactive), last accessed feature name, and last access timestamp
2. THE Staff_Monitor SHALL classify a staff member as "Active" when their last feature access is within 15 minutes, "Inactive" when their last access exceeds the configured inactive threshold (default 4 hours), and "Idle" otherwise
3. THE Staff_Monitor SHALL display today's access count and this week's access count for each staff member
4. THE Staff_Monitor SHALL display a relative usage bar for each staff member proportional to the highest usage in the branch
5. THE Staff_Monitor SHALL display summary statistics: total staff count, active now count, total accesses for the selected period, and workflow completion rate percentage
6. WHEN one or more staff members are classified as inactive, THE Staff_Monitor SHALL display an alert banner stating the count of inactive staff and the inactivity threshold
7. THE Staff_Monitor SHALL provide a period toggle (Today/This Week) that filters the total accesses and completion rate statistics
8. THE Staff_Monitor SHALL auto-refresh data using Livewire polling every 120 seconds

### Requirement 9: Available Features Display Component

**User Story:** As a Branch Manager, I want to see all published features with their availability status, health indicators, usage metrics, and deployment info, so that I can monitor what is available for branch staff.

#### Acceptance Criteria

1. THE Feature_Registry SHALL display each published feature with its name, description, domain, version number, availability status (available/degraded/unavailable), and a visual status icon
2. THE Feature_Registry SHALL display the last used timestamp and weekly usage count for each feature scoped to the current branch
3. THE Feature_Registry SHALL display the last deployment timestamp for each feature and a "New" badge for features deployed within the configured threshold
4. IF a feature has a health check error, THEN THE Feature_Registry SHALL display the error message alongside the feature
5. WHEN a feature status is "degraded" or "unavailable", THE Feature_Registry SHALL display a "Report to IT" link that navigates to the support page
6. WHEN no published features exist, THE Feature_Registry SHALL display a message instructing the Branch Manager to contact the IT department, including IT support email and phone number
7. THE Feature_Registry SHALL provide a search input that filters features by name with 300ms debounce
8. THE Feature_Registry SHALL display summary counts: total features, available count, and issue count

### Requirement 10: Branch Support Ticket Component

**User Story:** As a Branch Manager, I want to create support tickets, view ticket history with status tracking, and see IT department contact information, so that I can communicate technical issues to the IT team.

#### Acceptance Criteria

1. THE Support_System SHALL display a list of the current user's support tickets filtered by status: Open, Resolved, or All
2. THE Support_System SHALL display each ticket with its title, description, category, priority (with color indicator), status (with color indicator), creation timestamp, and IT response when available
3. THE Support_System SHALL provide a "New Ticket" button that opens a modal form with fields: title (required, 5-255 chars), description (required, min 10 chars), category (bug/feature_request/issue), and priority (low/medium/high/critical)
4. WHEN the Branch Manager submits a valid ticket form, THE Support_System SHALL create a SupportTicket record with the authenticated user_id, user branch_id, and status "open", then close the modal and display a success flash message
5. IF the ticket form submission contains invalid data, THEN THE Support_System SHALL display validation error messages below the respective form fields
6. THE Support_System SHALL display IT department contact information (email, phone, support hours) from the `branch.it_support` configuration
7. THE Support_System SHALL display a ticket summary sidebar showing open ticket count, resolved ticket count, and resolution rate percentage
8. THE Support_System SHALL display open and resolved ticket counts in the filter tabs

### Requirement 11: LogFeatureAccess Middleware Integration

**User Story:** As a developer, I want the LogFeatureAccess middleware to correctly write to the FeatureAccessLog model, so that all feature access events are captured for the branch dashboard.

#### Acceptance Criteria

1. WHEN an authenticated user accesses a feature route, THE Feature_Access_Logger SHALL create a FeatureAccessLog record with user_id from the authenticated user, feature_id from the resolved Feature model, feature_version_id from the published version, branch_id from the user, access_type "view", and accessed_at as the current timestamp
2. IF the Feature model or feature route parameter cannot be resolved, THEN THE Feature_Access_Logger SHALL skip logging and return the response without error
3. IF the FeatureAccessLog creation fails, THEN THE Feature_Access_Logger SHALL log a warning message and return the response without interrupting the request
4. WHEN a Branch Manager accesses a feature in Staff View mode, THE Feature_Access_Logger SHALL additionally create an AuditTrail record with action "FEATURE_EXECUTION_AS_STAFF"

### Requirement 12: Branch Dashboard Configuration

**User Story:** As a developer, I want branch dashboard behavior controlled by configuration values, so that thresholds and intervals can be adjusted without code changes.

#### Acceptance Criteria

1. THE Dashboard_System SHALL read the active staff window from `branch.dashboard.active_staff_window_minutes` configuration (default: 15)
2. THE Dashboard_System SHALL read the polling interval from `branch.dashboard.poll_interval` configuration (default: "30s")
3. THE Staff_Monitor SHALL read the inactive staff threshold from `branch.dashboard.inactive_staff_threshold_hours` configuration (default: 4)
4. THE Deployment_Tracker SHALL read the new feature badge duration from `branch.dashboard.new_feature_badge_hours` configuration (default: 24)
5. THE Support_System SHALL read IT department contact details from `branch.it_support` configuration including email, phone, and hours fields
