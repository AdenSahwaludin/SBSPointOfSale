<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['nama' => 'Makanan Ringan'],
            ['nama' => 'Minuman'],
            ['nama' => 'Produk Susu'],
            ['nama' => 'Roti & Kue'],
            ['nama' => 'Bumbu & Rempah'],
            ['nama' => 'Mie Instan'],
            ['nama' => 'Beras & Tepung'],
            ['nama' => 'Produk Kesehatan'],
            ['nama' => 'Perawatan Tubuh'],
            ['nama' => 'Kebutuhan Rumah'],
        ];

        foreach ($categories as $category) {
            Kategori::create($category);
        }
    }
}
