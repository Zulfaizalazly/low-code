<?php

namespace Database\Seeders;

use App\Models\Organization\Branch;
use App\Models\Organization\Entity;
use App\Models\Organization\Region;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
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

        // Create HQ
        Branch::create([
            'entity_id' => $entity->id,
            'region_id' => null,
            'code' => 'BR-HQ',
            'name' => 'Ibu Pejabat Bank Rakyat',
            'type' => 'hq',
            'address' => 'Menara Kembar Bank Rakyat, No. 33, Jalan Rakyat',
            'city' => 'Kuala Lumpur',
            'state' => 'Wilayah Persekutuan',
            'postcode' => '50470',
            'phone' => '03-2612 9000',
            'email' => 'hq@bankrakyat.com.my',
            'opening_hours' => [
                'monday' => ['open' => '09:00', 'close' => '17:00'],
                'tuesday' => ['open' => '09:00', 'close' => '17:00'],
                'wednesday' => ['open' => '09:00', 'close' => '17:00'],
                'thursday' => ['open' => '09:00', 'close' => '17:00'],
                'friday' => ['open' => '09:00', 'close' => '17:00'],
                'saturday' => ['open' => 'closed', 'close' => 'closed'],
                'sunday' => ['open' => 'closed', 'close' => 'closed'],
            ],
            'is_active' => true,
            'opened_at' => now()->subYears(10),
        ]);

        // Get regions
        $centralRegion = Region::where('code', 'NORTH')->where('entity_id', $entity->id)->first();
        $northRegion = Region::where('code', 'CENTRAL')->where('entity_id', $entity->id)->first();
        $southRegion = Region::where('code', 'SOUTH')->where('entity_id', $entity->id)->first();

        // Create branches
        $branches = [
            [
                'region_id' => $centralRegion?->id,
                'code' => 'BR-001',
                'name' => 'Cawangan Bukit Bintang',
                'city' => 'Kuala Lumpur',
                'state' => 'Wilayah Persekutuan',
                'postcode' => '55100',
            ],
            [
                'region_id' => $centralRegion?->id,
                'code' => 'BR-002',
                'name' => 'Cawangan Shah Alam',
                'city' => 'Shah Alam',
                'state' => 'Selangor',
                'postcode' => '40000',
            ],
            [
                'region_id' => $northRegion?->id,
                'code' => 'BR-003',
                'name' => 'Cawangan Penang',
                'city' => 'Georgetown',
                'state' => 'Pulau Pinang',
                'postcode' => '10200',
            ],
            [
                'region_id' => $northRegion?->id,
                'code' => 'BR-004',
                'name' => 'Cawangan Ipoh',
                'city' => 'Ipoh',
                'state' => 'Perak',
                'postcode' => '30000',
            ],
            [
                'region_id' => $southRegion?->id,
                'code' => 'BR-005',
                'name' => 'Cawangan Johor Bahru',
                'city' => 'Johor Bahru',
                'state' => 'Johor',
                'postcode' => '80000',
            ],
        ];

        foreach ($branches as $branchData) {
            Branch::create([
                'entity_id' => $entity->id,
                'region_id' => $branchData['region_id'],
                'code' => $branchData['code'],
                'name' => $branchData['name'],
                'type' => 'branch',
                'address' => 'No. 123, Jalan ' . $branchData['city'],
                'city' => $branchData['city'],
                'state' => $branchData['state'],
                'postcode' => $branchData['postcode'],
                'phone' => '03-' . rand(1000, 9999) . ' ' . rand(1000, 9999),
                'email' => strtolower(str_replace(' ', '', $branchData['code'])) . '@bankrakyat.com.my',
                'opening_hours' => [
                    'monday' => ['open' => '09:30', 'close' => '16:00'],
                    'tuesday' => ['open' => '09:30', 'close' => '16:00'],
                    'wednesday' => ['open' => '09:30', 'close' => '16:00'],
                    'thursday' => ['open' => '09:30', 'close' => '16:00'],
                    'friday' => ['open' => '09:30', 'close' => '16:00'],
                    'saturday' => ['open' => 'closed', 'close' => 'closed'],
                    'sunday' => ['open' => 'closed', 'close' => 'closed'],
                ],
                'is_active' => true,
                'opened_at' => now()->subYears(rand(1, 5)),
            ]);
        }

        $this->command->info('Branches seeded successfully!');
    }
}
