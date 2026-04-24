<?php

namespace Database\Seeders;

use App\Models\Organization\Entity;
use Illuminate\Database\Seeder;

class EntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Entity::create([
            'code' => 'BR',
            'name' => 'Bank Rakyat',
            'registration_number' => '123456-A',
            'license_number' => 'BNM/LIC/2024/001',
            'address' => 'Menara Kembar Bank Rakyat, No. 33, Jalan Rakyat',
            'city' => 'Kuala Lumpur',
            'state' => 'Wilayah Persekutuan',
            'postcode' => '50470',
            'phone' => '03-2612 9000',
            'email' => 'info@bankrakyat.com.my',
            'is_active' => true,
            'settings' => [
                'currency' => 'MYR',
                'timezone' => 'Asia/Kuala_Lumpur',
                'business_hours' => [
                    'start' => '09:00',
                    'end' => '17:00',
                ],
            ],
        ]);

        $this->command->info('Entity seeded successfully!');
    }
}
