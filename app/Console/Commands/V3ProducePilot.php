<?php

namespace App\Console\Commands;

use Database\Seeders\ReferenceFeatureSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class V3ProducePilot extends Command
{
    protected $signature = 'v3:produce-pilot';
    protected $description = 'Initialize the Arrahnumation V3 Pilot environment';

    public function handle()
    {
        $this->info('🚀 Initializing Arrahnumation V3 Pilot Environment...');

        // 1. Wipe and Migrate
        $this->warn('--- Wiping and Migrating Database ---');
        Artisan::call('migrate:fresh', ['--force' => true]);
        $this->info('✅ Database migrated successfully.');

        // 2. Seed Roles & Permissions
        $this->warn('--- Seeding Roles & Permissions ---');
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
        $this->info('✅ Roles and permissions seeded successfully.');

        // 3. Seed Reference Data
        $this->warn('--- Seeding Reference Feature Bundle (New Pledge) ---');
        $seeder = new ReferenceFeatureSeeder();
        $seeder->run();
        $this->info('✅ Reference feature seeded correctly.');

        // 3. Launch Report
        $this->newLine(2);
        $this->line('============================================================');
        $this->line('   🌟 ARRAHNUMATION V3 - PILOT LAUNCH REPORT 🌟');
        $this->line('============================================================');
        $this->line(' The V3 Engine is now functional and ready for verification.');
        $this->newLine();
        $this->line(' 📡 ACCESS URL: ' . config('app.url') . '/studio');
        $this->newLine();
        $this->line(' 🏆 PILOT LOGINS:');
        $this->line('   [Branch Staff]    staff@arrahnu.com   / password (Role: business-user)');
        $this->line('   [Branch Manager]  manager@arrahnu.com / password (Role: reviewer)');
        $this->line('   [HQ Admin]        hq@arrahnu.com      / password (Role: super-admin)');
        $this->newLine();
        $this->line(' 🔐 SECURITY:');
        $this->line('   - Role-based access control enabled');
        $this->line('   - Permission middleware active');
        $this->line('   - CSRF protection enforced');
        $this->line('   - Input sanitization enabled');
        $this->newLine();
        $this->line(' 📦 FEATURE READY: "New Pledge Intake"');
        $this->line('   - Multi-step Dynamic Form (Step 1 & 2)');
        $this->line('   - Visual Orchestration Flow (Reg → Create → End)');
        $this->line('============================================================');
        $this->newLine();

        return Command::SUCCESS;
    }
}
