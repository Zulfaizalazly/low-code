<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order matters:
     * 1. Roles & Permissions (no dependencies)
     * 2. Entity (no dependencies)
     * 3. Regions (depends on entity)
     * 4. Branches (depends on entity + regions)
     * 5. Departments (depends on entity)
     * 6. Users (depends on entity + branches)
     * 7. Staff Assignments (depends on users + branches + departments)
     * 8. Reference Feature (depends on users)
     * 9. Branch Dashboard data (depends on users + features + branches)
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            EntitySeeder::class,
            RegionSeeder::class,
            BranchSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
            StaffAssignmentSeeder::class,
            ReferenceFeatureSeeder::class,
            BranchDashboardSeeder::class,
        ]);
    }
}
