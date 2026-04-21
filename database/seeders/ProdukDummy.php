<?php

namespace Database\Seeders;

use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukDummy extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [

            // AROMATHERAPY
            [
                'sku' => 'DA-LAV-60ML',
                'barcode' => '8998000010001',
                'no_bpom' => 'NA18230100001',
                'nama' => 'Minyak Aromaterapi Lavender 60 mL',
                'id_kategori' => 'AR',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 18000,
                'harga_pack' => 17500,
                'stok' => 120,
                'sisa_pcs_terbuka' => 0,
                'batas_stok_minimum' => 20,
                'jumlah_restock' => 50,
            ],
            [
                'sku' => 'DA-LAV-140ML',
                'barcode' => '8998000010002',
                'no_bpom' => 'NA18230100002',
                'nama' => 'Minyak Aromaterapi Lavender 140 mL',
                'id_kategori' => 'AR',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 35000,
                'harga_pack' => 34000,
                'stok' => 80,
                'sisa_pcs_terbuka' => 0,
                'batas_stok_minimum' => 15,
                'jumlah_restock' => 40,
            ],

            // ESSENTIAL OIL
            [
                'sku' => 'DA-TEA-60ML',
                'barcode' => '8998000010003',
                'no_bpom' => 'NA18230100003',
                'nama' => 'Essential Oil Tea Tree 60 mL',
                'id_kategori' => 'EL',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 22000,
                'harga_pack' => 21000,
                'stok' => 95,
                'sisa_pcs_terbuka' => 0,
                'batas_stok_minimum' => 20,
                'jumlah_restock' => 50,
            ],
            [
                'sku' => 'DA-EUC-140ML',
                'barcode' => '8998000010004',
                'no_bpom' => 'NA18230100004',
                'nama' => 'Essential Oil Eucalyptus 140 mL',
                'id_kategori' => 'EL',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 36000,
                'harga_pack' => 35000,
                'stok' => 70,
                'sisa_pcs_terbuka' => 0,
                'batas_stok_minimum' => 20,
                'jumlah_restock' => 40,
            ],

            // HERBAL
            [
                'sku' => 'DA-JAE-60ML',
                'barcode' => '8998000010005',
                'no_bpom' => 'TR18230100005',
                'nama' => 'Minyak Jahe Herbal 60 mL',
                'id_kategori' => 'HB',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 15000,
                'harga_pack' => 14500,
                'stok' => 110,
                'sisa_pcs_terbuka' => 0,
                'batas_stok_minimum' => 25,
                'jumlah_restock' => 50,
            ],
            [
                'sku' => 'DA-KUN-140ML',
                'barcode' => '8998000010006',
                'no_bpom' => 'TR18230100006',
                'nama' => 'Minyak Kunyit Herbal 140 mL',
                'id_kategori' => 'HB',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 30000,
                'harga_pack' => 29000,
                'stok' => 65,
                'sisa_pcs_terbuka' => 0,
                'batas_stok_minimum' => 20,
                'jumlah_restock' => 40,
            ],

            // SPICE OIL
            [
                'sku' => 'DA-CNG-60ML',
                'barcode' => '8998000010007',
                'no_bpom' => 'TR18230100007',
                'nama' => 'Minyak Cengkeh 60 mL',
                'id_kategori' => 'SP01',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 17000,
                'harga_pack' => 16500,
                'stok' => 90,
                'sisa_pcs_terbuka' => 0,
                'batas_stok_minimum' => 20,
                'jumlah_restock' => 50,
            ],
            [
                'sku' => 'DA-KYT-140ML',
                'barcode' => '8998000010008',
                'no_bpom' => 'TR18230100008',
                'nama' => 'Minyak Kayu Manis 140 mL',
                'id_kategori' => 'SP01',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 34000,
                'harga_pack' => 33000,
                'stok' => 55,
                'sisa_pcs_terbuka' => 0,
                'batas_stok_minimum' => 15,
                'jumlah_restock' => 40,
            ],

            // THERAPEUTIC
            [
                'sku' => 'LR-URU-140ML',
                'barcode' => '8998000010009',
                'no_bpom' => 'QD18230100009',
                'nama' => 'Minyak Urut Herbal 140 mL',
                'id_kategori' => 'TH',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 32000,
                'harga_pack' => 31000,
                'stok' => 60,
                'sisa_pcs_terbuka' => 0,
                'batas_stok_minimum' => 20,
                'jumlah_restock' => 40,
            ],

            // VARIAN PACK & KARTON (BIAR MIRIP PUNYA KAMU)
            [
                'sku' => 'DA-LAV-KRT144',
                'barcode' => null,
                'no_bpom' => 'NA18230100001',
                'nama' => 'Minyak Aromaterapi Lavender Karton 144 pcs',
                'id_kategori' => 'AR',
                'satuan' => 'karton',
                'isi_per_pack' => 144,
                'harga' => 2400000,
                'harga_pack' => 2400000,
                'stok' => 10,
                'sisa_pcs_terbuka' => 0,
                'batas_stok_minimum' => 5,
                'jumlah_restock' => 20,
            ],
            [
                'sku' => 'DA-TEA-PCK72',
                'barcode' => null,
                'no_bpom' => 'NA18230100003',
                'nama' => 'Essential Oil Tea Tree Pack 72 pcs',
                'id_kategori' => 'EL',
                'satuan' => 'pack',
                'isi_per_pack' => 72,
                'harga' => 1500000,
                'harga_pack' => 1500000,
                'stok' => 15,
                'sisa_pcs_terbuka' => 0,
                'batas_stok_minimum' => 5,
                'jumlah_restock' => 20,
            ],

        ];

        foreach ($products as $product) {
            Produk::create($product);
        }
    }
}
