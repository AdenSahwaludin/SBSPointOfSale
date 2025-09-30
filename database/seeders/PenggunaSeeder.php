<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class PenggunaSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['001-ADN', 'Aden Sahwaludin', 'admin@sbs.com', 'admin123', 'admin'],
            ['002-KSR', 'Kasir Sari Bumi', 'kasir@sbs.com', 'kasir123', 'kasir'],
        ];

        foreach ($users as [$id, $nama, $email, $password, $role]) {
            User::updateOrCreate(['id_pengguna' => $id], compact('nama', 'email', 'password', 'role'));
        }
    }
}
