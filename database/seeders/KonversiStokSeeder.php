<?php

namespace Database\Seeders;

use App\Models\KonversiStok;
use App\Models\Produk;
use Illuminate\Database\Seeder;

class KonversiStokSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get products for conversion
        $kartonProduk = Produk::where('sku', 'HB-MKP-KRT144')->first();
        $pcsProduk = Produk::where('sku', 'HB-MKP-60ML')->first();

        if ($kartonProduk && $pcsProduk) {
            KonversiStok::create([
                'from_produk_id' => $kartonProduk->id_produk,
                'to_produk_id' => $pcsProduk->id_produk,
                'rasio' => 144,
                'qty_from' => 1,
                'qty_to' => 144,
                'keterangan' => 'Konversi 1 karton menjadi 144 pcs',
            ]);
        }
    }
}
