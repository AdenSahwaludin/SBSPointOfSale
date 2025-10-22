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
                'trust_score' => 50,
                'credit_limit' => 0,
                'status_kredit' => 'aktif',
                'saldo_kredit' => 0,
            ],
            [
                'id_pelanggan' => 'P002',
                'nama' => 'Budi Santoso',
                'email' => 'budi.santoso@email.com',
                'telepon' => '081234567890',
                'kota' => 'Jakarta',
                'alamat' => 'Jl. Merdeka No. 123, Jakarta Pusat',
                'aktif' => true,
                'trust_score' => 75,
                'credit_limit' => 5000000,
                'status_kredit' => 'aktif',
                'saldo_kredit' => 0,
            ],
            [
                'id_pelanggan' => 'P003',
                'nama' => 'Siti Nurhaliza',
                'email' => 'siti.nur@email.com',
                'telepon' => '081234567891',
                'kota' => 'Bandung',
                'alamat' => 'Jl. Asia Afrika No. 45, Bandung',
                'aktif' => true,
                'trust_score' => 80,
                'credit_limit' => 7500000,
                'status_kredit' => 'aktif',
                'saldo_kredit' => 0,
            ],
        ];

        foreach ($customers as $customer) {
            Pelanggan::create($customer);
        }
    }
}
