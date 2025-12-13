<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            KategoriSeeder::class,
            PelangganSeeder::class,
            PenggunaSeeder::class,
            ProdukSeeder::class,
            // KonversiStokSeeder::class,
            // TransaksiSeeder::class,
            // TransaksiBulkSeeder::class,
            // KontrakKreditSeeder::class,
            // JadwalAngsuranSeeder::class,
        ]);
    }
}
