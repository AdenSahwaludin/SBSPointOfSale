<?php

namespace Database\Seeders;

use App\Models\Pelanggan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PelangganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'id_pelanggan' => 'P001',
                'nama' => 'Umum',
                'email' => null,
                'telepon' => null,
                'kota' => null,
                'alamat' => null,
                'aktif' => true,
                'tanggal_daftar' => now(),
            ],
            [
                'id_pelanggan' => 'P002',
                'nama' => 'Budi Santoso',
                'email' => 'budi.santoso@email.com',
                'telepon' => '081234567890',
                'kota' => 'Jakarta',
                'alamat' => 'Jl. Merdeka No. 123, Jakarta Pusat',
                'aktif' => true,
                'tanggal_daftar' => now(),
            ],
            [
                'id_pelanggan' => 'P003',
                'nama' => 'Siti Rahayu',
                'email' => 'siti.rahayu@email.com',
                'telepon' => '082345678901',
                'kota' => 'Bandung',
                'alamat' => 'Jl. Asia Afrika No. 456, Bandung',
                'aktif' => true,
                'tanggal_daftar' => now(),
            ],
            [
                'id_pelanggan' => 'P004',
                'nama' => 'Ahmad Hidayat',
                'email' => 'ahmad.hidayat@email.com',
                'telepon' => '083456789012',
                'kota' => 'Surabaya',
                'alamat' => 'Jl. Pemuda No. 789, Surabaya',
                'aktif' => true,
                'tanggal_daftar' => now(),
            ],
            [
                'id_pelanggan' => 'P005',
                'nama' => 'Dewi Lestari',
                'email' => 'dewi.lestari@email.com',
                'telepon' => '084567890123',
                'kota' => 'Yogyakarta',
                'alamat' => 'Jl. Malioboro No. 101, Yogyakarta',
                'aktif' => true,
                'tanggal_daftar' => now(),
            ],
            [
                'id_pelanggan' => 'P006',
                'nama' => 'Roni Wijaya',
                'email' => 'roni.wijaya@email.com',
                'telepon' => '085678901234',
                'kota' => 'Medan',
                'alamat' => 'Jl. Gatot Subroto No. 202, Medan',
                'aktif' => true,
                'tanggal_daftar' => now(),
            ],
            [
                'id_pelanggan' => 'P007',
                'nama' => 'Maya Sari',
                'email' => 'maya.sari@email.com',
                'telepon' => '086789012345',
                'kota' => 'Makassar',
                'alamat' => 'Jl. Sultan Hasanuddin No. 303, Makassar',
                'aktif' => true,
                'tanggal_daftar' => now(),
            ],
            [
                'id_pelanggan' => 'P008',
                'nama' => 'Andi Pratama',
                'email' => 'andi.pratama@email.com',
                'telepon' => '087890123456',
                'kota' => 'Palembang',
                'alamat' => 'Jl. Sudirman No. 404, Palembang',
                'aktif' => true,
                'tanggal_daftar' => now(),
            ],
            [
                'id_pelanggan' => 'P009',
                'nama' => 'Rina Kusuma',
                'email' => 'rina.kusuma@email.com',
                'telepon' => '088901234567',
                'kota' => 'Semarang',
                'alamat' => 'Jl. Diponegoro No. 505, Semarang',
                'aktif' => true,
                'tanggal_daftar' => now(),
            ],
            [
                'id_pelanggan' => 'P010',
                'nama' => 'Doni Setiawan',
                'email' => 'doni.setiawan@email.com',
                'telepon' => '089012345678',
                'kota' => 'Denpasar',
                'alamat' => 'Jl. Gajah Mada No. 606, Denpasar',
                'aktif' => true,
                'tanggal_daftar' => now(),
            ],
        ];

        foreach ($customers as $customer) {
            Pelanggan::create($customer);
        }
    }
}
