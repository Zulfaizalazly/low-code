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
        // 1. Create Demo HQ Admin
        User::create([
            'name' => 'HQ Admin',
            'email' => 'admin@arrahnu.com',
            'password' => Hash::make('password'),
            'role' => 'hq_admin',
            'entity_id' => 1,
            'branch_id' => null,
        ]);

        // 2. Create Demo Branch Staff
        User::create([
            'name' => 'Branch Staff One',
            'email' => 'staff1@arrahnu.com',
            'password' => Hash::make('password'),
            'role' => 'branch_staff',
            'entity_id' => 1,
            'branch_id' => 101, // KL Branch
        ]);

        // 3. Create Demo Branch Manager
        User::create([
            'name' => 'Branch Manager One',
            'email' => 'manager1@arrahnu.com',
            'password' => Hash::make('password'),
            'role' => 'branch_manager',
            'entity_id' => 1,
            'branch_id' => 101,
        ]);

        // 4. Create Staff at different branch (for scoping tests)
        User::create([
            'name' => 'Branch Staff Two',
            'email' => 'staff2@arrahnu.com',
            'password' => Hash::make('password'),
            'role' => 'branch_staff',
            'entity_id' => 1,
            'branch_id' => 102, // JB Branch
        ]);
    }
}
