<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class PenggunaSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['id_pengguna' => '001-ADN'],
            [
                'nama' => 'Aden Sahwaludin',
                'email' => 'admin@admin.com',
                'password' => 'admin123', // otomatis di-hash setter
                'role' => 'admin',
            ]
        );
    }
}
