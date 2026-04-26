<?php

namespace Database\Seeders;

use App\Models\Organization\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Create demo users for all roles.
     *
     * Users:
     * - HQ Admin (super-admin) — no branch, accesses Studio + Admin Panel
     * - Branch Manager 1 — assigned to first branch
     * - Branch Manager 2 — assigned to second branch
     * - Branch Staff 1 — teller at first branch
     * - Branch Staff 2 — teller at first branch (second teller)
     * - Branch Staff 3 — teller at second branch
     */
    public function run(): void
    {
        $branches = Branch::where('type', 'branch')->orderBy('code')->get();
        $branch1 = $branches->first();
        $branch2 = $branches->skip(1)->first() ?? $branch1;

        if (!$branch1) {
            $this->command->error('No branches found. Run BranchSeeder first.');
            return;
        }

        // ─── HQ Admin (super-admin) ───
        $admin = User::create([
            'name' => 'Ahmad Razif',
            'email' => 'admin@arrahnu.com',
            'password' => Hash::make('password'),
            'role' => 'hq_admin',
            'entity_id' => 1,
            'branch_id' => null,
            'employee_number' => 'HQ-001',
            'phone' => '012-345 6789',
            'is_active' => true,
            'joined_at' => now()->subYears(5),
        ]);
        $admin->assignRole('super-admin');

        // ─── Demo Admin (SSO Login Gate) ───
        $demoAdmin = User::firstOrCreate(
            ['email' => 'admin@arrahnumation.com'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('SystemArrahnu1234!@#$'),
                'role' => 'hq_admin',
                'entity_id' => 1,
                'branch_id' => null,
                'employee_number' => 'HQ-DEMO',
                'phone' => '012-000 0000',
                'is_active' => true,
                'joined_at' => now(),
            ]
        );
        if (!$demoAdmin->hasRole('super-admin')) {
            $demoAdmin->assignRole('super-admin');
        }

        // ─── Branch Manager 1 ───
        $manager1 = User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'manager1@arrahnu.com',
            'password' => Hash::make('password'),
            'role' => 'branch_manager',
            'entity_id' => 1,
            'branch_id' => $branch1->id,
            'employee_number' => 'BR001-MGR',
            'phone' => '013-456 7890',
            'is_active' => true,
            'joined_at' => now()->subYears(3),
        ]);
        $manager1->assignRole('branch_manager');

        // ─── Branch Manager 2 ───
        $manager2 = User::create([
            'name' => 'Mohd Faizal',
            'email' => 'manager2@arrahnu.com',
            'password' => Hash::make('password'),
            'role' => 'branch_manager',
            'entity_id' => 1,
            'branch_id' => $branch2->id,
            'employee_number' => 'BR002-MGR',
            'phone' => '014-567 8901',
            'is_active' => true,
            'joined_at' => now()->subYears(2),
        ]);
        $manager2->assignRole('branch_manager');

        // ─── Branch Staff 1 (Teller, Branch 1) ───
        $staff1 = User::create([
            'name' => 'Nurul Aisyah',
            'email' => 'staff1@arrahnu.com',
            'password' => Hash::make('password'),
            'role' => 'branch_staff',
            'entity_id' => 1,
            'branch_id' => $branch1->id,
            'employee_number' => 'BR001-T01',
            'phone' => '015-678 9012',
            'is_active' => true,
            'joined_at' => now()->subYear(),
        ]);
        $staff1->assignRole('branch_staff');

        // ─── Branch Staff 2 (Teller, Branch 1) ───
        $staff2 = User::create([
            'name' => 'Muhammad Hafiz',
            'email' => 'staff2@arrahnu.com',
            'password' => Hash::make('password'),
            'role' => 'branch_staff',
            'entity_id' => 1,
            'branch_id' => $branch1->id,
            'employee_number' => 'BR001-T02',
            'phone' => '016-789 0123',
            'is_active' => true,
            'joined_at' => now()->subMonths(8),
        ]);
        $staff2->assignRole('branch_staff');

        // ─── Branch Staff 3 (Teller, Branch 2) ───
        $staff3 = User::create([
            'name' => 'Amirah Zainal',
            'email' => 'staff3@arrahnu.com',
            'password' => Hash::make('password'),
            'role' => 'branch_staff',
            'entity_id' => 1,
            'branch_id' => $branch2->id,
            'employee_number' => 'BR002-T01',
            'phone' => '017-890 1234',
            'is_active' => true,
            'joined_at' => now()->subMonths(6),
        ]);
        $staff3->assignRole('branch_staff');

        $this->command->info("Users seeded: 1 admin, 1 demo admin, 2 managers, 3 staff");
    }
}
