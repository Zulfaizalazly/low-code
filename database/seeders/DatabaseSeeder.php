<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles & Permissions (must be first)
        $this->call(RolePermissionSeeder::class);

        // 2. Organization structure (entity → regions → branches → departments)
        $this->call(EntitySeeder::class);
        $this->call(RegionSeeder::class);
        $this->call(BranchSeeder::class);
        $this->call(DepartmentSeeder::class);

        // 3. Get the first real branch for user assignment
        $firstBranch = \App\Models\Organization\Branch::where('type', 'branch')->first();
        $secondBranch = \App\Models\Organization\Branch::where('type', 'branch')
            ->where('id', '!=', $firstBranch?->id)
            ->first();
        $branchId1 = $firstBranch?->id ?? 1;
        $branchId2 = $secondBranch?->id ?? $branchId1;

        // 4. Create Demo Users with real branch IDs
        $admin = User::create([
            'name' => 'HQ Admin',
            'email' => 'admin@arrahnu.com',
            'password' => Hash::make('password'),
            'role' => 'hq_admin',
            'entity_id' => 1,
            'branch_id' => null,
        ]);
        $admin->assignRole('super-admin');

        $staff1 = User::create([
            'name' => 'Branch Staff One',
            'email' => 'staff1@arrahnu.com',
            'password' => Hash::make('password'),
            'role' => 'branch_staff',
            'entity_id' => 1,
            'branch_id' => $branchId1,
        ]);
        $staff1->assignRole('branch_staff');

        $manager1 = User::create([
            'name' => 'Branch Manager One',
            'email' => 'manager1@arrahnu.com',
            'password' => Hash::make('password'),
            'role' => 'branch_manager',
            'entity_id' => 1,
            'branch_id' => $branchId1,
        ]);
        $manager1->assignRole('branch_manager');

        $staff2 = User::create([
            'name' => 'Branch Staff Two',
            'email' => 'staff2@arrahnu.com',
            'password' => Hash::make('password'),
            'role' => 'branch_staff',
            'entity_id' => 1,
            'branch_id' => $branchId2,
        ]);
        $staff2->assignRole('branch_staff');

        // 5. Staff assignments
        $this->call(StaffAssignmentSeeder::class);

        // 6. Reference feature (new-pledge) with flow, pages, steps, fields
        $this->call(ReferenceFeatureSeeder::class);

        // 7. Branch dashboard test data (depends on users + features)
        $this->call(BranchDashboardSeeder::class);
    }
}
