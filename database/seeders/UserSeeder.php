<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\UserAdmin;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrator',
                'email' => 'admin@zona.test',
                'password' => 'password',
                'role' => 'administrator',
            ],
            [
                'name' => 'Operator User',
                'email' => 'operator.user@zona.test',
                'password' => 'password',
                'role' => 'operator_user',
            ],
            [
                'name' => 'Operator Transaksi',
                'email' => 'operator.transaksi@zona.test',
                'password' => 'password',
                'role' => 'operator_transaksi',
            ],
            [
                'name' => 'Petugas Pengaduan',
                'email' => 'pengaduan@zona.test',
                'password' => 'password',
                'role' => 'pengaduan',
            ],
        ];

        foreach ($users as $user) {
            UserAdmin::updateOrCreate(
                ['email' => $user['email']], // unique key
                [
                    'name' => $user['name'],
                    'password' => Hash::make($user['password']),
                    'role' => $user['role'],
                ]
            );
        }
    }
}
