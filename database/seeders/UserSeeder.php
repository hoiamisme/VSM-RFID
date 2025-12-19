<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'Ahmad Yani',
                'email' => 'ahmad.yani@unhan.ac.id',
                'phone' => '081234567890',
                'user_type' => 'employee',
                'address' => 'Jakarta Selatan',
                'employee_id' => 'EMP001',
                'is_active' => true,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'phone' => '081234567891',
                'user_type' => 'guest',
                'address' => 'Jakarta Pusat',
                'institution' => 'Universitas Indonesia',
                'is_active' => true,
            ],
            [
                'name' => 'Citra Dewi',
                'email' => 'citra.dewi@unhan.ac.id',
                'phone' => '081234567892',
                'user_type' => 'employee',
                'address' => 'Jakarta Timur',
                'employee_id' => 'EMP002',
                'is_active' => true,
            ],
            [
                'name' => 'Doni Pratama',
                'email' => 'doni.pratama@example.com',
                'phone' => '081234567893',
                'user_type' => 'guest',
                'address' => 'Jakarta Barat',
                'institution' => 'Institut Teknologi Bandung',
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
