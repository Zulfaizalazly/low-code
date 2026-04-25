to# Implementation Plan: Branch Operations Dashboard

## Overview

Most of the branch operations dashboard is already implemented — all 4 Eloquent models, migration, middleware, config, Livewire components, routes, and Blade views are in place. The remaining work is creating the `BranchDashboardSeeder` for realistic test data and writing property-based tests to validate model scopes, helpers, and accessors against the 6 correctness properties defined in the design.

## Tasks

- [x] 1. Create BranchDashboardSeeder
  - [x] 1.1 Create `database/seeders/BranchDashboardSeeder.php`
    - Resolve existing users (branch staff), published features, and branches from the database
    - Generate 5–15 `FeatureAccessLog` records per branch staff member, distributed across the current week and assigned to published features
    - Generate 2–4 `FeatureHealthCheck` records: at least one "available" (resolved), one "degraded" (unresolved)
    - Generate 3–5 `ChangeDeployment` records for published features with `is_visible_to_branches = true`, including at least one deployed within the last 24 hours
    - Generate 3–5 `SupportTicket` records with varied categories, priorities, and statuses, including at least one open and one resolved with `response_note`
    - All records must reference valid existing `user_id`, `feature_id`, and `branch_id` values
    - Skip generation gracefully if no published features or branch staff exist
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

  - [x] 1.2 Register seeder in `database/seeders/DatabaseSeeder.php`
    - Add `BranchDashboardSeeder` call after existing seeders so it runs with `php artisan db:seed`
    - _Requirements: 6.5_

- [x] 2. Checkpoint — Verify seeder runs
  - Ensure `php artisan db:seed --class=BranchDashboardSeeder` completes without errors, ask the user if questions arise.

- [ ] 3. Property-based tests for model scopes and helpers
  - [ ] 3.1 Create test file `tests/Feature/Branch/BranchModelPropertyTest.php` with test scaffolding
    - Set up `RefreshDatabase` trait and helper methods to generate randomized model instances using Faker across 100+ iterations per property
    - _Requirements: 1.1–1.7, 2.1–2.7, 3.1–3.6, 4.1–4.8_

  - [ ]* 3.2 Write property test: Model attribute round-trip preservation
    - **Property 1: Model attribute round-trip preservation**
    - For each of the 4 models, create with random valid attributes, reload from DB, and assert all fields are preserved (datetimes as Carbon, JSON as array)
    - **Validates: Requirements 1.1, 1.7, 2.1, 2.6, 3.1, 3.5, 4.1, 4.6**

  - [ ]* 3.3 Write property test: Equality-filter scopes return exact matches
    - **Property 2: Equality-filter scopes return exact matches**
    - For `forBranch()`, `forFeature()`, `forUser()` scopes, generate records with varied IDs and assert the scope returns exactly matching records — no false positives or negatives
    - **Validates: Requirements 1.2, 2.3, 4.3, 4.4**

  - [ ]* 3.4 Write property test: Time-window scopes return records within boundaries
    - **Property 3: Time-window scopes return records within boundaries**
    - Generate `FeatureAccessLog` records with varied `accessed_at` timestamps and assert `recent()`, `today()`, `thisWeek()` return exactly the correct subset; same for `ChangeDeployment::recent()`
    - **Validates: Requirements 1.3, 1.4, 1.5, 3.3**

  - [ ]* 3.5 Write property test: Status-based scopes filter by compound criteria
    - **Property 4: Status-based scopes filter by compound criteria**
    - Generate records with varied status/resolved_at combinations and assert `hasIssues()`, `active()`, `open()`, `visibleToBranches()` return exactly the correct subset
    - **Validates: Requirements 2.2, 2.4, 3.2, 4.2**

  - [ ]* 3.6 Write property test: Boolean helper methods match attribute predicates
    - **Property 5: Boolean helper methods match attribute predicates**
    - For `isResolved()`, `isOpen()`, `isNew()`, generate models with varied attribute values and assert the helper return value matches the expected predicate
    - **Validates: Requirements 2.7, 3.6, 4.8**

  - [ ]* 3.7 Write property test: Color accessors map all valid enum values
    - **Property 6: Color accessors map all valid enum values to non-empty strings**
    - For all valid priority and status enum values on SupportTicket, assert `priority_color` and `status_color` return non-empty strings, and distinct enum values map to distinct colors
    - **Validates: Requirements 4.7**

- [x] 4. Final checkpoint — Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- All 4 Eloquent models, migration, middleware, config, Livewire components, routes, and Blade views are already fully implemented
- The seeder is the only production code that needs to be written
- Property tests use PHPUnit data providers with Faker to approximate PBT with 100+ iterations per property
- Each property test references specific correctness properties from the design document
