<?php

namespace Database\Seeders;

use App\Models\Organization\Entity;
use App\Models\Organization\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
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

        $regions = [
            [
                'code' => 'NORTH',
                'name' => 'Northern Region',
                'description' => 'Covers Perlis, Kedah, Penang, and Perak',
            ],
            [
                'code' => 'CENTRAL',
                'name' => 'Central Region',
                'description' => 'Covers Selangor, Kuala Lumpur, and Putrajaya',
            ],
            [
                'code' => 'SOUTH',
                'name' => 'Southern Region',
                'description' => 'Covers Negeri Sembilan, Melaka, and Johor',
            ],
            [
                'code' => 'EAST',
                'name' => 'East Coast Region',
                'description' => 'Covers Pahang, Terengganu, and Kelantan',
            ],
            [
                'code' => 'SABAH',
                'name' => 'Sabah Region',
                'description' => 'Covers Sabah',
            ],
            [
                'code' => 'SARAWAK',
                'name' => 'Sarawak Region',
                'description' => 'Covers Sarawak',
            ],
        ];

        foreach ($regions as $regionData) {
            Region::create([
                'entity_id' => $entity->id,
                'code' => $regionData['code'],
                'name' => $regionData['name'],
                'description' => $regionData['description'],
                'is_active' => true,
            ]);
        }

        $this->command->info('Regions seeded successfully!');
    }
}
