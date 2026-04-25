# Requirements Document

## Introduction

Organization Management Admin UI provides a centralized administration interface for the Ar-Rahnu system, enabling HQ Admin (Head Ar-Rahnu / Super Admin) to manage the full organizational hierarchy — entities, regions, branches (Cawangan), departments (Jabatan), and staff assignments. The system builds on fully implemented Eloquent models (Entity, Region, Branch, Department, StaffAssignment), the Spatie RBAC layer (9 roles, 30+ permissions), and the enhanced User model. Currently, no `/admin/*` routes exist; this feature introduces the complete admin route group, layout, navigation, and Livewire-based CRUD interfaces with organizational scoping.

## Glossary

- **Admin_Panel**: The top-level admin interface served under the `/admin` route prefix, accessible to users with the `super-admin` role
- **Admin_Layout**: The Blade layout providing sidebar navigation, header, and content area for all admin pages
- **Entity_Manager**: The Livewire component responsible for viewing and editing Entity (legal entity) details and settings
- **Branch_Manager_UI**: The Livewire component for listing, creating, editing, and configuring Branch records (Cawangan)
- **Department_Manager**: The Livewire component for listing, creating, editing, and displaying hierarchical Department records (Jabatan)
- **Staff_Manager**: The Livewire component for listing, creating, editing, assigning, and transferring staff (StaffAssignment + User)
- **Admin_Dashboard**: The Livewire component providing an organizational overview with key metrics for the HQ Admin
- **User_Role_Manager**: The existing Livewire component (`App\Livewire\Admin\UserRoleManager`) for managing user roles and direct permissions
- **Branch_Settings**: The JSON `settings` column on the Branch model used for branch-specific configurations (feature access, scope overrides)
- **Staff_Transfer**: The workflow for moving a staff member from one branch/department to another, ending the current StaffAssignment and creating a new one
- **Entity_Scope**: A Laravel global scope that filters queries by the authenticated user's `entity_id`
- **Branch_Scope**: A Laravel global scope that filters queries by the authenticated user's accessible branches
- **Organizational_Hierarchy**: The structure Entity → Region → Branch and Entity → Department, with StaffAssignment linking Users to Branches or Departments

## Requirements

### Requirement 1: Admin Route Group and Middleware

**User Story:** As a Super Admin, I want a dedicated admin route group protected by role-based middleware, so that only authorized HQ administrators can access organizational management pages.

#### Acceptance Criteria

1. THE Admin_Panel SHALL be served under the `/admin` route prefix with `web` and `auth` middleware applied
2. THE Admin_Panel SHALL restrict access to users with the `super-admin` role using the existing `role` middleware
3. WHEN an unauthenticated user navigates to any `/admin/*` route, THE Admin_Panel SHALL redirect the user to the login page
4. WHEN an authenticated user without the `super-admin` role navigates to any `/admin/*` route, THE Admin_Panel SHALL return a 403 Forbidden response
5. THE Admin_Panel SHALL register named routes with the `admin.` prefix for all admin pages

### Requirement 2: Admin Layout and Navigation

**User Story:** As a Super Admin, I want a consistent admin layout with sidebar navigation, so that I can navigate between all organizational management sections efficiently.

#### Acceptance Criteria

1. THE Admin_Layout SHALL provide a sidebar with navigation links to Dashboard, Branches, Departments, Staff, Entity Settings, and User Roles pages
2. THE Admin_Layout SHALL highlight the currently active navigation item based on the current route
3. THE Admin_Layout SHALL display the authenticated user's name and role in the sidebar footer
4. THE Admin_Layout SHALL follow the same visual design pattern as the existing `layouts/branch.blade.php` (Tailwind CSS, Inter font, Apple-style sidebar)
5. THE Admin_Layout SHALL include a link to the Studio dashboard for quick access

### Requirement 3: Admin Dashboard Overview

**User Story:** As a Super Admin (Head Ar-Rahnu), I want an organizational overview dashboard, so that I can see the status of all branches, staff distribution, and key operational metrics at a glance.

#### Acceptance Criteria

1. THE Admin_Dashboard SHALL display the total count of active branches, total staff count, total departments count, and total regions count as summary cards
2. THE Admin_Dashboard SHALL display a breakdown of branches by type (HQ, branch, mini_branch) with counts
3. THE Admin_Dashboard SHALL display a breakdown of staff by employment type (permanent, contract, temporary) with counts
4. THE Admin_Dashboard SHALL display a list of branches with their active staff count, manager name, and status
5. THE Admin_Dashboard SHALL display recently created or modified staff assignments within the last 30 days
6. WHEN no branches exist, THE Admin_Dashboard SHALL display a prompt to create the first branch

### Requirement 4: Branch Management — List and Filters

**User Story:** As a Super Admin, I want to view all branches with filtering and search capabilities, so that I can quickly find and manage specific branches.

#### Acceptance Criteria

1. THE Branch_Manager_UI SHALL display a paginated table of all branches showing code, name, type, region, city, state, manager name, active staff count, and status
2. THE Branch_Manager_UI SHALL provide a text search field that filters branches by code or name
3. THE Branch_Manager_UI SHALL provide dropdown filters for region, state, branch type, and active status
4. WHEN filters are applied, THE Branch_Manager_UI SHALL update the branch list without a full page reload using Livewire reactivity
5. THE Branch_Manager_UI SHALL display branch type as a color-coded badge (HQ, branch, mini_branch)
6. THE Branch_Manager_UI SHALL display active/inactive status as a visual indicator

### Requirement 5: Branch Management — Create and Edit

**User Story:** As a Super Admin, I want to create new branches and edit existing branch details, so that I can maintain accurate organizational structure.

#### Acceptance Criteria

1. WHEN the Super Admin clicks "Create Branch", THE Branch_Manager_UI SHALL display a modal form with fields for code, name, type, region, address, city, state, postcode, phone, email, manager, opening hours, and active status
2. THE Branch_Manager_UI SHALL validate that branch code is unique within the entity before saving
3. THE Branch_Manager_UI SHALL validate that only one branch of type `hq` exists per entity
4. THE Branch_Manager_UI SHALL validate that the selected manager has the `branch-manager` role
5. WHEN the Super Admin submits a valid branch form, THE Branch_Manager_UI SHALL create the branch record and display a success notification
6. WHEN the Super Admin clicks "Edit" on a branch, THE Branch_Manager_UI SHALL populate the form with existing branch data for modification
7. IF the branch code already exists for another branch in the same entity, THEN THE Branch_Manager_UI SHALL display a validation error message

### Requirement 6: Branch Details with Tabs

**User Story:** As a Super Admin, I want to view detailed branch information organized in tabs, so that I can see overview, staff, and settings for each branch.

#### Acceptance Criteria

1. THE Branch_Manager_UI SHALL display a branch detail view with tabs for Overview, Staff, and Settings
2. THE Overview tab SHALL display branch basic information, contact details, operating hours, and manager details
3. THE Staff tab SHALL display a table of all active staff assigned to the branch with name, position, employment type, and start date
4. THE Staff tab SHALL provide buttons to assign new staff or transfer existing staff
5. THE Settings tab SHALL display and allow editing of the branch `settings` JSON field as key-value configuration pairs
6. WHEN the Super Admin updates branch settings, THE Branch_Manager_UI SHALL save the settings and display a success notification

### Requirement 7: Department Management

**User Story:** As a Super Admin, I want to manage HQ departments with hierarchical display, so that I can maintain the organizational structure of headquarters.

#### Acceptance Criteria

1. THE Department_Manager SHALL display a list of all departments with code, name, head name, parent department, active staff count, and status
2. THE Department_Manager SHALL display departments in a hierarchical tree structure showing parent-child relationships
3. WHEN the Super Admin clicks "Create Department", THE Department_Manager SHALL display a form with fields for code, name, description, head, parent department, and active status
4. THE Department_Manager SHALL validate that department code is unique within the entity before saving
5. WHEN the Super Admin clicks "Edit" on a department, THE Department_Manager SHALL populate the form with existing department data for modification
6. THE Department_Manager SHALL prevent deletion of departments that have active staff assignments
7. IF a department has child departments, THEN THE Department_Manager SHALL display the children indented under the parent in the hierarchy view

### Requirement 8: Staff Management — List and Filters

**User Story:** As a Super Admin, I want to view all staff with filtering capabilities, so that I can manage staff assignments across the organization.

#### Acceptance Criteria

1. THE Staff_Manager SHALL display a paginated table of all users with employee number, name, email, current branch or department, position, role(s), and active status
2. THE Staff_Manager SHALL provide a text search field that filters staff by name, email, or employee number
3. THE Staff_Manager SHALL provide dropdown filters for branch, department, role, active status, and employment type
4. WHEN filters are applied, THE Staff_Manager SHALL update the staff list without a full page reload using Livewire reactivity
5. THE Staff_Manager SHALL display the user's primary assignment location (branch name or department name)
6. THE Staff_Manager SHALL display user roles as badges

### Requirement 9: Staff Management — Create and Edit

**User Story:** As a Super Admin, I want to create new staff members and edit their details, so that I can onboard and maintain staff records.

#### Acceptance Criteria

1. WHEN the Super Admin clicks "Create Staff", THE Staff_Manager SHALL display a form with fields for name, email, employee number, phone, password, entity, and active status
2. THE Staff_Manager SHALL validate that employee number is unique before saving
3. THE Staff_Manager SHALL validate that email is unique before saving
4. WHEN the Super Admin submits a valid staff form, THE Staff_Manager SHALL create the user record and display a success notification
5. WHEN the Super Admin clicks "Edit" on a staff member, THE Staff_Manager SHALL populate the form with existing user data for modification
6. THE Staff_Manager SHALL allow the Super Admin to reset a staff member's password

### Requirement 10: Staff Assignment

**User Story:** As a Super Admin, I want to assign staff to branches or departments with specific positions, so that I can manage where each staff member works.

#### Acceptance Criteria

1. WHEN the Super Admin clicks "Assign" on a staff member, THE Staff_Manager SHALL display a form with fields for branch or department, position, employment type, start date, and primary assignment flag
2. THE Staff_Manager SHALL validate that a staff member cannot be assigned to both a branch and a department in the same assignment
3. THE Staff_Manager SHALL validate that only one assignment is marked as primary per user
4. WHEN the Super Admin submits a valid assignment, THE Staff_Manager SHALL create the StaffAssignment record and display a success notification
5. THE Staff_Manager SHALL display the staff member's assignment history showing all past and current assignments
6. IF the staff member already has an active primary assignment, THEN THE Staff_Manager SHALL prompt the Super Admin to confirm replacing the primary assignment

### Requirement 11: Staff Transfer Workflow

**User Story:** As a Super Admin, I want to transfer staff between branches or departments, so that I can manage organizational changes and staff movements.

#### Acceptance Criteria

1. WHEN the Super Admin initiates a transfer, THE Staff_Manager SHALL display a form with the current assignment details and fields for the new branch or department, new position, transfer reason, and effective date
2. THE Staff_Manager SHALL validate that the transfer destination differs from the current assignment location
3. WHEN the Super Admin submits a valid transfer, THE Staff_Manager SHALL end the current assignment by setting `ended_at` to the effective date
4. WHEN the Super Admin submits a valid transfer, THE Staff_Manager SHALL create a new StaffAssignment record with the new location, position, and effective date as `started_at`
5. THE Staff_Manager SHALL set the new assignment as primary if the ended assignment was primary
6. THE Staff_Manager SHALL display a confirmation dialog before executing the transfer

### Requirement 12: Entity Settings Management

**User Story:** As a Super Admin, I want to view and edit entity details and settings, so that I can maintain the legal entity configuration.

#### Acceptance Criteria

1. THE Entity_Manager SHALL display the entity's code, name, registration number, license number, address, phone, email, and active status
2. THE Entity_Manager SHALL allow editing of entity details (name, address, phone, email, registration number, license number)
3. THE Entity_Manager SHALL display and allow editing of the entity `settings` JSON field as key-value configuration pairs
4. WHEN the Super Admin saves entity changes, THE Entity_Manager SHALL validate required fields and display a success notification
5. IF no entity exists, THEN THE Entity_Manager SHALL display a form to create the first entity

### Requirement 13: User Role Management Route

**User Story:** As a Super Admin, I want to access the existing UserRoleManager component through a proper admin route, so that I can manage user roles and permissions from the admin panel.

#### Acceptance Criteria

1. THE Admin_Panel SHALL register a route at `/admin/users/{user}/roles` that renders the existing `UserRoleManager` Livewire component
2. THE Admin_Panel SHALL pass the User model instance to the `UserRoleManager` component via route model binding
3. THE Staff_Manager SHALL provide a "Manage Roles" link for each staff member that navigates to the user role management page
4. THE User_Role_Manager page SHALL use the Admin_Layout for consistent navigation

### Requirement 14: Organizational Scoping Middleware

**User Story:** As a Super Admin, I want organizational scoping applied to queries, so that data access is properly restricted based on the user's entity and branch access level.

#### Acceptance Criteria

1. THE Entity_Scope SHALL filter all entity-aware queries to the authenticated user's `entity_id` for non-super-admin users
2. THE Branch_Scope SHALL filter branch-aware queries based on the user's accessible branches as determined by `getAccessibleBranches()`
3. WHEN a super-admin user is authenticated, THE Entity_Scope SHALL not apply any filtering
4. WHEN a super-admin user is authenticated, THE Branch_Scope SHALL not apply any filtering
5. THE Entity_Scope and Branch_Scope SHALL be implemented as Laravel Global Scopes that can be applied to Eloquent models
6. IF an unauthenticated request reaches a scoped query, THEN THE Entity_Scope SHALL return an empty result set
