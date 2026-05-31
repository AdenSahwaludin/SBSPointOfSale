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
            ProdukDummys::class,
            TransaksiSeederRandom::class,
        ]);

        // Automatically recalculate TS and CL for all customers after database seeding
        $this->command->info('Recalculating Trust Scores and Credit Limits for seeded customers...');
        \Illuminate\Support\Facades\Artisan::call('trust-score:recalculate', ['--all' => true]);
        $this->command->info(\Illuminate\Support\Facades\Artisan::output());
    }
}
