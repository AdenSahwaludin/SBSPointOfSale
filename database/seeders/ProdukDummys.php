<?php

namespace Database\Seeders;

use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukDummys extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $baseProducts = [
            ['code' => 'LAV', 'name' => 'Lavender', 'cat' => 'AR', 'price' => 18000],
            ['code' => 'EUC', 'name' => 'Eucalyptus', 'cat' => 'EL', 'price' => 20000],
            ['code' => 'TEA', 'name' => 'Tea Tree', 'cat' => 'EL', 'price' => 22000],
            ['code' => 'JAE', 'name' => 'Jahe', 'cat' => 'HB', 'price' => 15000],
            ['code' => 'KUN', 'name' => 'Kunyit', 'cat' => 'HB', 'price' => 16000],
            ['code' => 'TEM', 'name' => 'Temulawak', 'cat' => 'HB', 'price' => 17000],
            ['code' => 'SRH', 'name' => 'Sereh', 'cat' => 'SP01', 'price' => 16000],
            ['code' => 'CNG', 'name' => 'Cengkeh', 'cat' => 'SP01', 'price' => 17000],
            ['code' => 'KYM', 'name' => 'Kayu Manis', 'cat' => 'SP01', 'price' => 18000],
            ['code' => 'KYP', 'name' => 'Kayu Putih', 'cat' => 'TH', 'price' => 19000],
            ['code' => 'NLM', 'name' => 'Nilam', 'cat' => 'AR', 'price' => 21000],
            ['code' => 'MLT', 'name' => 'Melati', 'cat' => 'AR', 'price' => 20000],
            ['code' => 'MNT', 'name' => 'Mint', 'cat' => 'EL', 'price' => 18000],
            ['code' => 'RSM', 'name' => 'Rosemary', 'cat' => 'EL', 'price' => 22000],
            ['code' => 'LNG', 'name' => 'Lengkuas', 'cat' => 'HB', 'price' => 15000],
            ['code' => 'JRW', 'name' => 'Jeruk Wangi', 'cat' => 'AR', 'price' => 17000],
            ['code' => 'GRL', 'name' => 'Geranium', 'cat' => 'AR', 'price' => 23000],
            ['code' => 'SND', 'name' => 'Cendana', 'cat' => 'AR', 'price' => 25000],
            ['code' => 'KAF', 'name' => 'Kaffir Lime', 'cat' => 'SP01', 'price' => 18000],
            ['code' => 'BTN', 'name' => 'Bintang Anis', 'cat' => 'SP01', 'price' => 20000],
        ];

        $sizes = [
            ['label' => '60ML', 'ml' => 60, 'multiplier' => 1],
            ['label' => '140ML', 'ml' => 140, 'multiplier' => 2],
            ['label' => '275ML', 'ml' => 275, 'multiplier' => 4],
        ];

        $no = 1;

        foreach ($baseProducts as $bp) {
            foreach ($sizes as $size) {

                // PCS
                Produk::create([
                    'sku' => "DA-{$bp['code']}-{$size['label']}",
                    'barcode' => '8998' . str_pad($no++, 9, '0', STR_PAD_LEFT),
                    'no_bpom' => 'NA1823' . str_pad($no, 6, '0', STR_PAD_LEFT),
                    'nama' => "Minyak {$bp['name']} {$size['ml']} mL",
                    'id_kategori' => $bp['cat'],
                    'satuan' => 'pcs',
                    'isi_per_pack' => 1,
                    'harga' => $bp['price'] * $size['multiplier'],
                    'harga_pack' => ($bp['price'] * $size['multiplier']) - 1000,
                    'stok' => rand(10, 150),
                    'sisa_pcs_terbuka' => 0,
                    'batas_stok_minimum' => 20,
                    'jumlah_restock' => 50,
                ]);

                // PACK 36
                Produk::create([
                    'sku' => "DA-{$bp['code']}-PCK36",
                    'barcode' => null,
                    'no_bpom' => 'NA1823' . str_pad($no, 6, '0', STR_PAD_LEFT),
                    'nama' => "Minyak {$bp['name']} Pack 36 pcs ({$size['ml']} mL)",
                    'id_kategori' => $bp['cat'],
                    'satuan' => 'pack',
                    'isi_per_pack' => 36,
                    'harga' => ($bp['price'] * $size['multiplier']) * 36,
                    'harga_pack' => ($bp['price'] * $size['multiplier']) * 36,
                    'stok' => rand(5, 50),
                    'sisa_pcs_terbuka' => 0,
                    'batas_stok_minimum' => 10,
                    'jumlah_restock' => 20,
                ]);

                // KARTON
                Produk::create([
                    'sku' => "DA-{$bp['code']}-KRT144",
                    'barcode' => null,
                    'no_bpom' => 'NA1823' . str_pad($no, 6, '0', STR_PAD_LEFT),
                    'nama' => "Minyak {$bp['name']} Karton 144 pcs ({$size['ml']} mL)",
                    'id_kategori' => $bp['cat'],
                    'satuan' => 'karton',
                    'isi_per_pack' => 144,
                    'harga' => ($bp['price'] * $size['multiplier']) * 144,
                    'harga_pack' => ($bp['price'] * $size['multiplier']) * 144,
                    'stok' => rand(1, 20),
                    'sisa_pcs_terbuka' => 0,
                    'batas_stok_minimum' => 5,
                    'jumlah_restock' => 15,
                ]);
            }
        }
    }
}

