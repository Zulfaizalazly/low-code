<?php

namespace Database\Seeders;

use App\Models\Organization\Department;
use App\Models\Organization\Entity;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $entity = Entity::where('code', 'BR')->first();

        if (!$entity) {
            $this->command->error('Entity not found. Please run EntitySeeder first.');
            return;
        }

        // Create root departments
        $it = Department::create([
            'entity_id' => $entity->id,
            'code' => 'IT',
            'name' => 'Information Technology',
            'description' => 'Manages all IT infrastructure and systems',
            'is_active' => true,
        ]);

        $finance = Department::create([
            'entity_id' => $entity->id,
            'code' => 'FIN',
            'name' => 'Finance',
            'description' => 'Handles financial operations and reporting',
            'is_active' => true,
        ]);

        $hr = Department::create([
            'entity_id' => $entity->id,
            'code' => 'HR',
            'name' => 'Human Resources',
            'description' => 'Manages employee relations and recruitment',
            'is_active' => true,
        ]);

        $ops = Department::create([
            'entity_id' => $entity->id,
            'code' => 'OPS',
            'name' => 'Operations',
            'description' => 'Oversees daily operations and branch management',
            'is_active' => true,
        ]);

        $compliance = Department::create([
            'entity_id' => $entity->id,
            'code' => 'COMP',
            'name' => 'Compliance',
            'description' => 'Ensures regulatory compliance',
            'is_active' => true,
        ]);

        // Create sub-departments
        Department::create([
            'entity_id' => $entity->id,
            'code' => 'IT-DEV',
            'name' => 'Development Team',
            'description' => 'Software development and maintenance',
            'parent_id' => $it->id,
            'is_active' => true,
        ]);

        Department::create([
            'entity_id' => $entity->id,
            'code' => 'IT-INFRA',
            'name' => 'Infrastructure Team',
            'description' => 'Server and network management',
            'parent_id' => $it->id,
            'is_active' => true,
        ]);

        Department::create([
            'entity_id' => $entity->id,
            'code' => 'FIN-ACC',
            'name' => 'Accounting',
            'description' => 'Financial accounting and bookkeeping',
            'parent_id' => $finance->id,
            'is_active' => true,
        ]);

        Department::create([
            'entity_id' => $entity->id,
            'code' => 'FIN-AUD',
            'name' => 'Internal Audit',
            'description' => 'Internal auditing and controls',
            'parent_id' => $finance->id,
            'is_active' => true,
        ]);

        $this->command->info('Departments seeded successfully!');
    }
}
