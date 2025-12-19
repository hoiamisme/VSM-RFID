<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RfidCard;

class RfidCardSeeder extends Seeder
{
    public function run()
    {
        $cards = [
            [
                'uid' => 'E004012345ABCD',
                'user_id' => 1,
                'card_number' => 'CARD-001',
                'status' => 'active',
                'registered_at' => now(),
            ],
            [
                'uid' => 'E004012345ABCE',
                'user_id' => 2,
                'card_number' => 'CARD-002',
                'status' => 'active',
                'registered_at' => now(),
                'expired_at' => now()->addDays(30),
            ],
            [
                'uid' => 'E004012345ABCF',
                'user_id' => 3,
                'card_number' => 'CARD-003',
                'status' => 'active',
                'registered_at' => now(),
            ],
            [
                'uid' => 'E004012345ABD0',
                'user_id' => 4,
                'card_number' => 'CARD-004',
                'status' => 'active',
                'registered_at' => now(),
                'expired_at' => now()->addDays(7),
            ],
        ];

        foreach ($cards as $card) {
            RfidCard::create($card);
        }
    }
}
