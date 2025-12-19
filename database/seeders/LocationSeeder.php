<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    public function run()
    {
        $locations = [
            [
                'name' => 'Pintu Utama',
                'code' => 'MAIN',
                'description' => 'Pintu masuk utama kampus',
                'floor' => 'Ground',
                'building' => 'Gedung A',
                'capacity' => null,
                'requires_special_access' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Ruang Dekanat',
                'code' => 'DEK',
                'description' => 'Kantor Dekanat',
                'floor' => 'Lantai 2',
                'building' => 'Gedung A',
                'capacity' => 20,
                'requires_special_access' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Aula',
                'code' => 'AULA',
                'description' => 'Aula serba guna',
                'floor' => 'Lantai 1',
                'building' => 'Gedung B',
                'capacity' => 200,
                'requires_special_access' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Lab Informatika',
                'code' => 'LAB-IT',
                'description' => 'Laboratorium komputer',
                'floor' => 'Lantai 3',
                'building' => 'Gedung C',
                'capacity' => 40,
                'requires_special_access' => false,
                'is_active' => true,
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
