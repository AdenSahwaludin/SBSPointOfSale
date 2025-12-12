<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['id_kategori' => 'HB', 'nama' => 'Herbal'],
            ['id_kategori' => 'EL', 'nama' => 'Essential Oil'],
            ['id_kategori' => 'SP01', 'nama' => 'Spice Oil'],
            ['id_kategori' => 'TH', 'nama' => 'Therapeutic Oil'],
            ['id_kategori' => 'AR', 'nama' => 'Aromatherapy'],
        ];

        foreach ($categories as $category) {
            Kategori::create($category);
        }
    }
}
