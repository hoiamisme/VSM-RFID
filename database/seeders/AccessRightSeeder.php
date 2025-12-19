<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccessRight;

class AccessRightSeeder extends Seeder
{
    public function run()
    {
        $accessRights = [
            // Employee 1 (Ahmad Yani) - Full access
            ['user_id' => 1, 'location_id' => 1, 'can_access' => true, 'access_type' => 'permanent'],
            ['user_id' => 1, 'location_id' => 2, 'can_access' => true, 'access_type' => 'permanent'],
            ['user_id' => 1, 'location_id' => 3, 'can_access' => true, 'access_type' => 'permanent'],
            ['user_id' => 1, 'location_id' => 4, 'can_access' => true, 'access_type' => 'permanent'],
            
            // Guest (Budi Santoso) - Limited access
            ['user_id' => 2, 'location_id' => 1, 'can_access' => true, 'access_type' => 'temporary', 'valid_from' => now(), 'valid_until' => now()->addDays(30)],
            ['user_id' => 2, 'location_id' => 3, 'can_access' => true, 'access_type' => 'temporary', 'valid_from' => now(), 'valid_until' => now()->addDays(30)],
            ['user_id' => 2, 'location_id' => 2, 'can_access' => false, 'access_type' => 'temporary'], // Denied to Dekanat
            
            // Employee 2 (Citra Dewi) - Partial access
            ['user_id' => 3, 'location_id' => 1, 'can_access' => true, 'access_type' => 'permanent'],
            ['user_id' => 3, 'location_id' => 4, 'can_access' => true, 'access_type' => 'permanent'],
            
            // Guest 2 (Doni Pratama) - Very limited access
            ['user_id' => 4, 'location_id' => 1, 'can_access' => true, 'access_type' => 'temporary', 'valid_from' => now(), 'valid_until' => now()->addDays(7)],
        ];

        foreach ($accessRights as $right) {
            AccessRight::create($right);
        }
    }
}
