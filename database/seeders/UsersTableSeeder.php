<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserAdmin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan tabel (aman karena belum ada data)
        DB::table('sessions')->delete();
        DB::table('password_reset_tokens')->delete();
        DB::table('users')->delete();

        // Insert Administrator
        UserAdmin::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'administrator',
            'remember_token' => Str::random(10),
        ]);

        // Insert Operator User
        UserAdmin::create([
            'name' => 'Operator User',
            'email' => 'operatoruser@example.com',
            'password' => Hash::make('password'),
            'role' => 'operator_user',
            'remember_token' => Str::random(10),
        ]);

        // Insert Operator Transaksi
        UserAdmin::create([
            'name' => 'Operator Transaksi',
            'email' => 'operatortransaksi@example.com',
            'password' => Hash::make('password'),
            'role' => 'operator_transaksi',
            'remember_token' => Str::random(10),
        ]);

        // Insert Pengaduan
        UserAdmin::create([
            'name' => 'Pengaduan',
            'email' => 'pengaduan@example.com',
            'password' => Hash::make('password'),
            'role' => 'pengaduan',
            'remember_token' => Str::random(10),
        ]);
    }
}
