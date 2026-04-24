# Organization Management Requirements - Ar-Rahnu System

## 1. Overview

Sistem Organization Management untuk menguruskan struktur organisasi Ar-Rahnu termasuk HQ, branches, departments, dan staff assignments. System ini integrate dengan RBAC untuk access control berdasarkan organizational hierarchy.

**Bukan Multi-tenancy** - Single database, shared schema, dengan organizational scoping.

---

## 2. Organizational Structure

### 2.1 Hierarchy

```
Entity (Legal Entity / Company)
    └── Headquarters (HQ)
        ├── Department 1 (e.g., IT, Finance, Operations)
        ├── Department 2
        └── ...
    └── Region 1 (e.g., Northern Region)
        ├── Branch 1
        ├── Branch 2
        └── ...
    └── Region 2 (e.g., Central Region)
        ├── Branch 3
        ├── Branch 4
        └── ...
```

### 2.2 Entity (Legal Entity)

**Purpose:** Top-level organizational unit (e.g., Bank Rakyat, MBSB Bank)

**Attributes:**
- `id` - Primary key
- `code` - Unique code (e.g., "BR", "MBSB")
- `name` - Full name (e.g., "Bank Rakyat")
- `registration_number` - SSM/ROC number
- `license_number` - BNM license number
- `address` - Registered address
- `phone` - Contact number
- `email` - Official email
- `is_active` - Status
- `settings` - JSON (entity-level configurations)
- `created_at`, `updated_at`

**Business Rules:**
- One system can support multiple entities (future-proof)
- Currently: Single entity implementation
- Entity code must be unique
- Cannot delete entity with active branches

---

### 2.3 Region

**Purpose:** Geographical grouping of branches

**Attributes:**
- `id` - Primary key
- `entity_id` - Foreign key to entities
- `code` - Unique code within entity (e.g., "NORTH", "CENTRAL")
- `name` - Region name (e.g., "Northern Region", "Wilayah Utara")
- `description` - Optional description
- `is_active` - Status
- `created_at`, `updated_at`

**Business Rules:**
- Region code must be unique within entity
- Cannot delete region with active branches
- Optional - branches can exist without region

---

### 2.4 Branch

**Purpose:** Physical branch location

**Attributes:**
- `id` - Primary key
- `entity_id` - Foreign key to entities
- `region_id` - Foreign key to regions (nullable)
- `code` - Unique branch code (e.g., "BR001", "MBSB-KL01")
- `name` - Branch name (e.g., "Cawangan Kuala Lumpur")
- `type` - Enum: 'hq', 'branch', 'mini_branch'
- `address` - Full address
- `city` - City
- `state` - State
- `postcode` - Postcode
- `phone` - Contact number
- `email` - Branch email
- `manager_id` - Foreign key to users (Branch Manager)
- `opening_hours` - JSON (operating hours)
- `is_active` - Status
- `opened_at` - Branch opening date
- `closed_at` - Branch closing date (nullable)
- `settings` - JSON (branch-specific configurations)
- `created_at`, `updated_at`

**Business Rules:**
- Branch code must be unique within entity
- Only one branch can have type = 'hq' per entity
- Cannot delete branch with active staff
- Cannot delete branch with active facilities
- Manager must have 'branch_manager' role

**Example Data:**
```php
[
    'code' => 'BR-HQ',
    'name' => 'Ibu Pejabat Bank Rakyat',
    'type' => 'hq',
    'city' => 'Kuala Lumpur',
    'state' => 'Wilayah Persekutuan',
    'is_active' => true,
]

[
    'code' => 'BR-001',
    'name' => 'Cawangan Bukit Bintang',
    'type' => 'branch',
    'region_id' => 1, // Central Region
    'city' => 'Kuala Lumpur',
    'state' => 'Wilayah Persekutuan',
    'is_active' => true,
]
```

---

### 2.5 Department

**Purpose:** Organizational units within HQ (e.g., IT, Finance, HR)

**Attributes:**
- `id` - Primary key
- `entity_id` - Foreign key to entities
- `code` - Unique code (e.g., "IT", "FIN", "OPS")
- `name` - Department name (e.g., "Information Technology")
- `description` - Optional description
- `head_id` - Foreign key to users (Department Head)
- `parent_id` - Foreign key to departments (for sub-departments)
- `is_active` - Status
- `created_at`, `updated_at`

**Business Rules:**
- Department code must be unique within entity
- Departments are HQ-level only
- Can have hierarchical structure (parent-child)
- Cannot delete department with active staff

**Example Data:**
```php
[
    'code' => 'IT',
    'name' => 'Information Technology',
    'parent_id' => null,
]

[
    'code' => 'IT-DEV',
    'name' => 'Development Team',
    'parent_id' => 1, // IT Department
]
```

---

### 2.6 Staff Assignment

**Purpose:** Link users to branches/departments with positions

**Attributes:**
- `id` - Primary key
- `user_id` - Foreign key to users
- `entity_id` - Foreign key to entities
- `branch_id` - Foreign key to branches (nullable)
- `department_id` - Foreign key to departments (nullable)
- `position` - Job title (e.g., "Teller", "Branch Manager", "Developer")
- `employment_type` - Enum: 'permanent', 'contract', 'temporary'
- `started_at` - Assignment start date
- `ended_at` - Assignment end date (nullable)
- `is_primary` - Boolean (primary assignment)
- `created_at`, `updated_at`

**Business Rules:**
- User must have at least one active assignment
- User can have multiple assignments (e.g., acting roles)
- Only one assignment can be primary
- Branch staff must have branch_id
- HQ staff must have department_id
- Cannot have both branch_id and department_id

**Example Data:**
```php
// Branch Staff
[
    'user_id' => 123,
    'branch_id' => 5,
    'department_id' => null,
    'position' => 'Senior Teller',
    'employment_type' => 'permanent',
    'is_primary' => true,
]

// HQ Staff
[
    'user_id' => 456,
    'branch_id' => null,
    'department_id' => 2, // IT Department
    'position' => 'Senior Developer',
    'employment_type' => 'permanent',
    'is_primary' => true,
]
```

---

## 3. Integration with RBAC

### 3.1 User Model Enhancement

**Current:**
```php
class User extends Authenticatable
{
    use HasRoles; // Spatie Permission
    
    protected $fillable = [
        'name', 'email', 'password', 
        'entity_id', 'branch_id', 'role'
    ];
}
```

**Enhanced:**
```php
class User extends Authenticatable
{
    use HasRoles, HasOrganization;
    
    protected $fillable = [
        'name', 'email', 'password', 
        'entity_id', // Keep for quick access
        'employee_number', // Staff ID
        'phone', 'avatar',
        'is_active', 'joined_at', 'left_at'
    ];
    
    // Relationships
    public function entity() { return $this->belongsTo(Entity::class); }
    public function assignments() { return $this->hasMany(StaffAssignment::class); }
    public function primaryAssignment() { 
        return $this->hasOne(StaffAssignment::class)->where('is_primary', true); 
    }
    
    // Helper Methods
    public function getPrimaryBranch() { ... }
    public function getPrimaryDepartment() { ... }
    public function isHQStaff() { ... }
    public function isBranchStaff() { ... }
    public function canAccessBranch($branchId) { ... }
}
```

### 3.2 Organizational Scoping

**Scope Hierarchy:**
```
Entity > Region > Branch > User
Entity > Department > User
```

**Access Rules:**

| Role | Entity Access | Branch Access | Department Access |
|------|---------------|---------------|-------------------|
| Super Admin | All entities | All branches | All departments |
| HQ Admin | Own entity | All branches | All departments |
| Regional Manager | Own entity | Region branches | N/A |
| Branch Manager | Own entity | Own branch + subordinates | N/A |
| Branch Staff | Own entity | Own branch only | N/A |
| Department Head | Own entity | N/A | Own department |
| Department Staff | Own entity | N/A | Own department |

**Implementation:**
```php
// Global Scope for Entity
class EntityScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (auth()->check() && !auth()->user()->hasRole('super-admin')) {
            $builder->where('entity_id', auth()->user()->entity_id);
        }
    }
}

// Branch Scope
class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $user = auth()->user();
        
        if ($user->hasRole('branch_staff')) {
            $builder->where('branch_id', $user->getPrimaryBranch()->id);
        } elseif ($user->hasRole('branch_manager')) {
            $builder->whereIn('branch_id', $user->getManagedBranches()->pluck('id'));
        }
        // HQ staff and admins see all
    }
}
```

---

## 4. Features & Functionality

### 4.1 Entity Management

**Who Can Access:** Super Admin only

**Features:**
- ✅ Create new entity
- ✅ Edit entity details
- ✅ View entity list
- ✅ Activate/deactivate entity
- ✅ Configure entity-level settings
- ❌ Delete entity (soft delete only)

**UI Location:** `/admin/entities`

---

### 4.2 Branch Management

**Who Can Access:** 
- Super Admin (all entities)
- HQ Admin (own entity)
- Regional Manager (own region)

**Features:**
- ✅ Create new branch
- ✅ Edit branch details
- ✅ View branch list (with filters)
- ✅ Assign branch manager
- ✅ Configure branch settings
- ✅ View branch staff list
- ✅ View branch performance metrics
- ✅ Activate/deactivate branch
- ❌ Delete branch (soft delete only)

**UI Location:** `/admin/branches`

**Branch List Filters:**
- Region
- State
- Status (active/inactive)
- Type (HQ/branch/mini)

**Branch Details View:**
- Basic info
- Manager details
- Staff list (with roles)
- Operating hours
- Performance metrics
- Recent transactions

---

### 4.3 Department Management

**Who Can Access:**
- Super Admin
- HQ Admin

**Features:**
- ✅ Create new department
- ✅ Edit department details
- ✅ View department hierarchy
- ✅ Assign department head
- ✅ View department staff list
- ✅ Activate/deactivate department
- ❌ Delete department (soft delete only)

**UI Location:** `/admin/departments`

---

### 4.4 Staff Management

**Who Can Access:**
- Super Admin (all staff)
- HQ Admin (all staff in entity)
- Branch Manager (own branch staff)
- Department Head (own department staff)

**Features:**
- ✅ Create new user
- ✅ Edit user details
- ✅ View staff list (with filters)
- ✅ Assign to branch/department
- ✅ Assign roles (RBAC)
- ✅ Transfer staff (branch/department)
- ✅ View staff history
- ✅ Activate/deactivate user
- ✅ Reset password
- ❌ Delete user (soft delete only)

**UI Location:** `/admin/staff`

**Staff List Filters:**
- Branch
- Department
- Role
- Status (active/inactive)
- Employment type

**Staff Details View:**
- Personal info
- Current assignment
- Assignment history
- Roles & permissions
- Login history
- Audit trail

---

### 4.5 Staff Transfer

**Who Can Initiate:**
- HQ Admin
- Branch Manager (request only)

**Workflow:**
```
1. Initiate Transfer Request
   ↓
2. Approval (if required)
   ↓
3. End current assignment
   ↓
4. Create new assignment
   ↓
5. Update user's primary assignment
   ↓
6. Notify user
```

**Transfer Types:**
- Branch to Branch
- Department to Department
- Branch to HQ (promotion)
- HQ to Branch (deployment)
- Temporary assignment (acting role)

**Audit Trail:**
- Who initiated
- Approval chain
- Effective date
- Reason for transfer

---

## 5. Database Schema

### 5.1 Entities Table
```sql
CREATE TABLE entities (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    registration_number VARCHAR(50),
    license_number VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    postcode VARCHAR(10),
    phone VARCHAR(20),
    email VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    settings JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_code (code),
    INDEX idx_is_active (is_active)
);
```

### 5.2 Regions Table
```sql
CREATE TABLE regions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    entity_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (entity_id) REFERENCES entities(id),
    UNIQUE KEY unique_region_code (entity_id, code),
    INDEX idx_entity_id (entity_id),
    INDEX idx_is_active (is_active)
);
```

### 5.3 Branches Table
```sql
CREATE TABLE branches (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    entity_id BIGINT UNSIGNED NOT NULL,
    region_id BIGINT UNSIGNED NULL,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('hq', 'branch', 'mini_branch') DEFAULT 'branch',
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    postcode VARCHAR(10),
    phone VARCHAR(20),
    email VARCHAR(255),
    manager_id BIGINT UNSIGNED NULL,
    opening_hours JSON,
    is_active BOOLEAN DEFAULT TRUE,
    opened_at DATE,
    closed_at DATE NULL,
    settings JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (entity_id) REFERENCES entities(id),
    FOREIGN KEY (region_id) REFERENCES regions(id),
    FOREIGN KEY (manager_id) REFERENCES users(id),
    UNIQUE KEY unique_branch_code (entity_id, code),
    INDEX idx_entity_id (entity_id),
    INDEX idx_region_id (region_id),
    INDEX idx_type (type),
    INDEX idx_is_active (is_active)
);
```

### 5.4 Departments Table
```sql
CREATE TABLE departments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    entity_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    head_id BIGINT UNSIGNED NULL,
    parent_id BIGINT UNSIGNED NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (entity_id) REFERENCES entities(id),
    FOREIGN KEY (head_id) REFERENCES users(id),
    FOREIGN KEY (parent_id) REFERENCES departments(id),
    UNIQUE KEY unique_dept_code (entity_id, code),
    INDEX idx_entity_id (entity_id),
    INDEX idx_parent_id (parent_id),
    INDEX idx_is_active (is_active)
);
```

### 5.5 Staff Assignments Table
```sql
CREATE TABLE staff_assignments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NULL,
    department_id BIGINT UNSIGNED NULL,
    position VARCHAR(255) NOT NULL,
    employment_type ENUM('permanent', 'contract', 'temporary') DEFAULT 'permanent',
    started_at DATE NOT NULL,
    ended_at DATE NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (entity_id) REFERENCES entities(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    INDEX idx_user_id (user_id),
    INDEX idx_entity_id (entity_id),
    INDEX idx_branch_id (branch_id),
    INDEX idx_department_id (department_id),
    INDEX idx_is_primary (is_primary),
    INDEX idx_dates (started_at, ended_at),
    
    CHECK (branch_id IS NOT NULL OR department_id IS NOT NULL),
    CHECK (NOT (branch_id IS NOT NULL AND department_id IS NOT NULL))
);
```

### 5.6 Users Table Update
```sql
ALTER TABLE users 
    ADD COLUMN employee_number VARCHAR(50) UNIQUE AFTER id,
    ADD COLUMN phone VARCHAR(20) AFTER email,
    ADD COLUMN avatar VARCHAR(255) AFTER phone,
    ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER avatar,
    ADD COLUMN joined_at DATE AFTER is_active,
    ADD COLUMN left_at DATE NULL AFTER joined_at,
    ADD INDEX idx_employee_number (employee_number),
    ADD INDEX idx_entity_id (entity_id),
    ADD INDEX idx_is_active (is_active);
```

---

## 6. API Endpoints

### 6.1 Entity Management
```
GET    /api/admin/entities              - List all entities
POST   /api/admin/entities              - Create entity
GET    /api/admin/entities/{id}         - Get entity details
PUT    /api/admin/entities/{id}         - Update entity
DELETE /api/admin/entities/{id}         - Soft delete entity
PATCH  /api/admin/entities/{id}/toggle  - Activate/deactivate
```

### 6.2 Branch Management
```
GET    /api/admin/branches              - List branches (filtered by access)
POST   /api/admin/branches              - Create branch
GET    /api/admin/branches/{id}         - Get branch details
PUT    /api/admin/branches/{id}         - Update branch
DELETE /api/admin/branches/{id}         - Soft delete branch
PATCH  /api/admin/branches/{id}/toggle  - Activate/deactivate
GET    /api/admin/branches/{id}/staff   - Get branch staff list
GET    /api/admin/branches/{id}/metrics - Get branch metrics
```

### 6.3 Department Management
```
GET    /api/admin/departments           - List departments
POST   /api/admin/departments           - Create department
GET    /api/admin/departments/{id}      - Get department details
PUT    /api/admin/departments/{id}      - Update department
DELETE /api/admin/departments/{id}      - Soft delete department
GET    /api/admin/departments/{id}/staff - Get department staff
```

### 6.4 Staff Management
```
GET    /api/admin/staff                 - List staff (filtered by access)
POST   /api/admin/staff                 - Create user
GET    /api/admin/staff/{id}            - Get user details
PUT    /api/admin/staff/{id}            - Update user
DELETE /api/admin/staff/{id}            - Soft delete user
PATCH  /api/admin/staff/{id}/toggle     - Activate/deactivate
POST   /api/admin/staff/{id}/assign     - Assign to branch/department
POST   /api/admin/staff/{id}/transfer   - Transfer staff
GET    /api/admin/staff/{id}/history    - Get assignment history
POST   /api/admin/staff/{id}/reset-password - Reset password
```

---

## 7. UI/UX Requirements

### 7.1 Branch List View

**Layout:** Table with filters

**Columns:**
- Branch Code
- Branch Name
- Type (badge)
- Region
- City/State
- Manager
- Staff Count
- Status (active/inactive)
- Actions

**Filters:**
- Search (code, name)
- Region dropdown
- State dropdown
- Type dropdown
- Status toggle

**Actions:**
- View details
- Edit
- View staff
- Activate/Deactivate

---

### 7.2 Branch Details View

**Tabs:**
1. **Overview**
   - Basic info
   - Contact details
   - Operating hours
   - Manager info

2. **Staff**
   - Staff list table
   - Add staff button
   - Transfer staff button

3. **Performance**
   - Key metrics
   - Charts
   - Recent transactions

4. **Settings**
   - Branch-specific configurations
   - Scope overrides

---

### 7.3 Staff List View

**Layout:** Table with filters

**Columns:**
- Employee Number
- Name
- Email
- Branch/Department
- Position
- Role(s)
- Status
- Actions

**Filters:**
- Search (name, email, employee number)
- Branch dropdown
- Department dropdown
- Role dropdown
- Status toggle

**Actions:**
- View details
- Edit
- Assign role
- Transfer
- Activate/Deactivate

---

### 7.4 Staff Details View

**Tabs:**
1. **Profile**
   - Personal info
   - Contact details
   - Photo

2. **Assignment**
   - Current assignment
   - Assignment history
   - Transfer button

3. **Roles & Permissions**
   - Assigned roles
   - Direct permissions
   - Add/remove roles

4. **Activity**
   - Login history
   - Audit trail
   - Recent actions

---

## 8. Business Rules & Validations

### 8.1 Branch Rules
- ✅ Branch code must be unique within entity
- ✅ Only one HQ per entity
- ✅ Branch must have at least one active staff
- ✅ Cannot deactivate branch with active facilities
- ✅ Manager must have 'branch_manager' role
- ✅ Cannot delete branch with transaction history

### 8.2 Staff Rules
- ✅ Employee number must be unique
- ✅ User must have at least one active assignment
- ✅ Only one primary assignment allowed
- ✅ Cannot assign to both branch and department
- ✅ Role must match assignment (branch staff → branch, HQ staff → department)
- ✅ Cannot deactivate user with pending approvals

### 8.3 Transfer Rules
- ✅ Cannot transfer to same location
- ✅ Must have approval for cross-region transfer
- ✅ Must end current assignment before new one
- ✅ Temporary assignment has end date
- ✅ Cannot transfer user with active transactions

---

## 9. Reporting Requirements

### 9.1 Organization Reports

**Branch Performance Report:**
- Metrics per branch
- Comparison across branches
- Trend analysis

**Staff Distribution Report:**
- Staff count by branch
- Staff count by department
- Staff count by role

**Transfer History Report:**
- All transfers in period
- Transfer reasons
- Average tenure per branch

### 9.2 Compliance Reports

**BNM Reporting:**
- Branch details
- Staff credentials
- License information

**Audit Reports:**
- Access logs by branch
- Role changes
- Assignment changes

---

## 10. Integration Points

### 10.1 With RBAC
- User roles scoped to organization
- Permissions filtered by branch/department
- Access control based on hierarchy

### 10.2 With Scope Overrides
- Branch-level feature configurations
- Department-level settings
- User-level overrides

### 10.3 With Audit Trail
- All organizational changes logged
- Staff movements tracked
- Access patterns monitored

### 10.4 With Runtime
- Branch context in transactions
- Staff assignment validation
- Organizational scoping in queries

---

## 11. Implementation Phases

### Phase 1: Core Models & Migrations
- ✅ Create entities table
- ✅ Create regions table
- ✅ Create branches table
- ✅ Create departments table
- ✅ Create staff_assignments table
- ✅ Update users table
- ✅ Create Eloquent models
- ✅ Define relationships

### Phase 2: Seeders & Test Data
- ✅ Entity seeder
- ✅ Branch seeder (HQ + 5 branches)
- ✅ Department seeder
- ✅ Staff assignment seeder
- ✅ Update user factory

### Phase 3: API & Business Logic
- ✅ Entity CRUD
- ✅ Branch CRUD
- ✅ Department CRUD
- ✅ Staff CRUD
- ✅ Assignment logic
- ✅ Transfer logic
- ✅ Validation rules

### Phase 4: UI Components
- ✅ Branch list & details
- ✅ Department list & details
- ✅ Staff list & details
- ✅ Assignment forms
- ✅ Transfer workflow

### Phase 5: Integration
- ✅ Integrate with RBAC
- ✅ Apply organizational scoping
- ✅ Update existing features
- ✅ Audit trail integration

### Phase 6: Testing & Documentation
- ✅ Unit tests
- ✅ Integration tests
- ✅ User acceptance testing
- ✅ Documentation

---

## 12. Success Criteria

### 12.1 Functional
- ✅ Can create and manage entities
- ✅ Can create and manage branches
- ✅ Can create and manage departments
- ✅ Can assign staff to branches/departments
- ✅ Can transfer staff between locations
- ✅ Access control works based on organization
- ✅ Audit trail captures all changes

### 12.2 Performance
- ✅ Branch list loads < 1 second
- ✅ Staff list loads < 1 second
- ✅ Organizational queries optimized
- ✅ No N+1 query issues

### 12.3 Security
- ✅ Users can only access their scope
- ✅ Sensitive operations require approval
- ✅ All changes are audited
- ✅ Role-based access enforced

---

## Status: 📋 REQUIREMENTS DEFINED

Next steps:
1. Review requirements dengan stakeholders
2. Validate organizational structure
3. Confirm business rules
4. Start Phase 1 implementation
