<?php

namespace Database\Seeders;

use App\Models\Organization\Branch;
use App\Models\Organization\Department;
use App\Models\Organization\StaffAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class StaffAssignmentSeeder extends Seeder
{
    /**
     * Create staff assignments linking users to branches and departments.
     */
    public function run(): void
    {
        $hq = Branch::where('type', 'hq')->first();
        $branches = Branch::where('type', 'branch')->orderBy('code')->get();
        $branch1 = $branches->first();
        $branch2 = $branches->skip(1)->first() ?? $branch1;

        $itDept = Department::where('code', 'IT')->first();
        $opsDept = Department::where('code', 'OPS')->first();

        $admin = User::where('email', 'admin@arrahnu.com')->first();
        $manager1 = User::where('email', 'manager1@arrahnu.com')->first();
        $manager2 = User::where('email', 'manager2@arrahnu.com')->first();
        $staff1 = User::where('email', 'staff1@arrahnu.com')->first();
        $staff2 = User::where('email', 'staff2@arrahnu.com')->first();
        $staff3 = User::where('email', 'staff3@arrahnu.com')->first();

        if (!$admin || !$manager1 || !$staff1) {
            $this->command->error('Users not found. Run UserSeeder first.');
            return;
        }

        // Admin → HQ, IT Department
        StaffAssignment::create([
            'user_id' => $admin->id,
            'entity_id' => 1,
            'branch_id' => $hq?->id,
            'department_id' => $itDept?->id,
            'position' => 'Head of IT',
            'employment_type' => 'permanent',
            'started_at' => $admin->joined_at ?? now()->subYears(5),
            'is_primary' => true,
        ]);

        // Manager 1 → Branch 1, Operations
        StaffAssignment::create([
            'user_id' => $manager1->id,
            'entity_id' => 1,
            'branch_id' => $branch1->id,
            'department_id' => $opsDept?->id,
            'position' => 'Branch Manager',
            'employment_type' => 'permanent',
            'started_at' => $manager1->joined_at ?? now()->subYears(3),
            'is_primary' => true,
        ]);

        // Manager 2 → Branch 2, Operations
        if ($manager2) {
            StaffAssignment::create([
                'user_id' => $manager2->id,
                'entity_id' => 1,
                'branch_id' => $branch2->id,
                'department_id' => $opsDept?->id,
                'position' => 'Branch Manager',
                'employment_type' => 'permanent',
                'started_at' => $manager2->joined_at ?? now()->subYears(2),
                'is_primary' => true,
            ]);
        }

        // Staff 1 → Branch 1, Operations (Teller)
        StaffAssignment::create([
            'user_id' => $staff1->id,
            'entity_id' => 1,
            'branch_id' => $branch1->id,
            'department_id' => $opsDept?->id,
            'position' => 'Teller',
            'employment_type' => 'permanent',
            'started_at' => $staff1->joined_at ?? now()->subYear(),
            'is_primary' => true,
        ]);

        // Staff 2 → Branch 1, Operations (Teller)
        if ($staff2) {
            StaffAssignment::create([
                'user_id' => $staff2->id,
                'entity_id' => 1,
                'branch_id' => $branch1->id,
                'department_id' => $opsDept?->id,
                'position' => 'Teller',
                'employment_type' => 'permanent',
                'started_at' => $staff2->joined_at ?? now()->subMonths(8),
                'is_primary' => true,
            ]);
        }

        // Staff 3 → Branch 2, Operations (Teller)
        if ($staff3) {
            StaffAssignment::create([
                'user_id' => $staff3->id,
                'entity_id' => 1,
                'branch_id' => $branch2->id,
                'department_id' => $opsDept?->id,
                'position' => 'Teller',
                'employment_type' => 'permanent',
                'started_at' => $staff3->joined_at ?? now()->subMonths(6),
                'is_primary' => true,
            ]);
        }

        // Update branch manager_id references
        if ($branch1 && $manager1) {
            $branch1->update(['manager_id' => $manager1->id]);
        }
        if ($branch2 && $manager2) {
            $branch2->update(['manager_id' => $manager2->id]);
        }

        $this->command->info('Staff assignments seeded successfully!');
    }
}
