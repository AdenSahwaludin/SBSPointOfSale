<?php

namespace Database\Seeders;

use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'sku' => 'HB-MKP-60ML',
                'barcode' => '8997000610060',
                'no_bpom' => 'POM QD185616211',
                'nama' => 'Minyak Kayu Putih 60ml',
                'id_kategori' => 'HB',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 15000,
                'stok' => 100,
            ],
            [
                'sku' => 'HB-MKP-KRT144',
                'barcode' => null,
                'no_bpom' => 'POM QD185616211',
                'nama' => 'Minyak Kayu Putih Karton 144pcs',
                'id_kategori' => 'HB',
                'satuan' => 'karton',
                'isi_per_pack' => 144,
                'harga' => 2000000,
                'stok' => 5,
            ],
            [
                'sku' => 'EL-EUC-30ML',
                'barcode' => '8997000610061',
                'no_bpom' => 'POM QD185616212',
                'nama' => 'Minyak Eucalyptus 30ml',
                'id_kategori' => 'EL',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 18000,
                'stok' => 75,
            ],
            [
                'sku' => 'SP01-SRH-100ML',
                'barcode' => '8997000610062',
                'no_bpom' => 'POM QD185616213',
                'nama' => 'Minyak Sereh 100ml',
                'id_kategori' => 'SP01',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 25000,
                'stok' => 50,
            ],
            [
                'sku' => 'TH-CNG-50ML',
                'barcode' => '8997000610063',
                'no_bpom' => 'POM QD185616214',
                'nama' => 'Minyak Cengkeh 50ml',
                'id_kategori' => 'TH',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 20000,
                'stok' => 30,
            ],
        ];

        foreach ($products as $product) {
            Produk::create($product);
        }
    }
}
