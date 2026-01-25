<?php

namespace Database\Seeders;

use App\Models\Produk;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // A. Produk 60 mL (per pcs)
            [
                'sku' => 'DA-GDP-60ML',
                'barcode' => '8997000610001',
                'no_bpom' => 'QD.185616211',
                'nama' => 'Minyak Gandarpura Cap Daun 60 mL',
                'id_kategori' => 'HB',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 16000,
                'harga_pack' => 15500, // harga per 3 pcs
                'stok' => 200,
            ],
            [
                'sku' => 'DA-SRH-60ML',
                'barcode' => '8997000610002',
                'no_bpom' => 'QD.185616212',
                'nama' => 'Minyak Sereh Cap Daun 60 mL',
                'id_kategori' => 'EL',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 16000,
                'harga_pack' => 15500,
                'stok' => 200,
            ],
            [
                'sku' => 'DA-ALK-60ML',
                'barcode' => '8997000610003',
                'no_bpom' => 'QD.185616213',
                'nama' => 'Minyak Akar Lawang Cap Daun 60 mL',
                'id_kategori' => 'SP01',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 14000,
                'harga_pack' => 13500,
                'stok' => 200,
            ],
            [
                'sku' => 'DA-KYP-60ML',
                'barcode' => '8997000610004',
                'no_bpom' => 'QD.185616214',
                'nama' => 'Minyak Kayu Putih Cap Daun 60 mL',
                'id_kategori' => 'TH',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 18000,
                'harga_pack' => 17800,
                'stok' => 200,
            ],

            // B. Produk 140 mL (per pcs)
            [
                'sku' => 'LR-GSU-140ML',
                'barcode' => '8997000610005',
                'no_bpom' => 'QD.185616215',
                'nama' => 'Minyak Gosok Urut Cap Laron 140 mL',
                'id_kategori' => 'AR',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 35000,
                'harga_pack' => 34500,
                'stok' => 150,
            ],
            [
                'sku' => 'DA-ALK-140ML',
                'barcode' => '8997000610006',
                'no_bpom' => 'QD.185616216',
                'nama' => 'Minyak Akar Lawang Cap Daun 140 mL',
                'id_kategori' => 'HB',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 25000,
                'harga_pack' => 24500,
                'stok' => 150,
            ],
            [
                'sku' => 'DA-KYP-140ML',
                'barcode' => '8997000610007',
                'no_bpom' => 'QD.185616217',
                'nama' => 'Minyak Kayu Putih Cap Daun 140 mL',
                'id_kategori' => 'EL',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 36000,
                'harga_pack' => 35500,
                'stok' => 150,
            ],

            // C. Produk 275 mL (per pcs)
            [
                'sku' => 'LR-GSU-275ML',
                'barcode' => '8997000610008',
                'no_bpom' => 'QD.185616218',
                'nama' => 'Minyak Gosok Urut Cap Laron 275 mL',
                'id_kategori' => 'SP01',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 70000,
                'harga_pack' => 69000,
                'stok' => 100,
            ],
            [
                'sku' => 'DA-ALK-275ML',
                'barcode' => '8997000610009',
                'no_bpom' => 'QD.185616219',
                'nama' => 'Minyak Akar Lawang Cap Daun 275 mL',
                'id_kategori' => 'TH',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 50000,
                'harga_pack' => 49000,
                'stok' => 100,
            ],
            [
                'sku' => 'DA-KYP-275ML',
                'barcode' => '8997000610010',
                'no_bpom' => 'QD.185616220',
                'nama' => 'Minyak Kayu Putih Cap Daun 275 mL',
                'id_kategori' => 'AR',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 72000,
                'harga_pack' => 71000,
                'stok' => 100,
            ],

            // D. Produk Karton 60 mL (1 karton = 144 pcs)
            [
                'sku' => 'DA-GDP-KRT144',
                'barcode' => null,
                'no_bpom' => 'QD.185616211',
                'nama' => 'Minyak Gandarpura Cap Daun Karton 144 pcs (60 mL)',
                'id_kategori' => 'HB',
                'satuan' => 'karton',
                'isi_per_pack' => 144,
                'harga' => 1584000, // 144 x Rp 11.000
                'harga_pack' => 1584000,
                'stok' => 10,
            ],
            [
                'sku' => 'DA-SRH-KRT144',
                'barcode' => null,
                'no_bpom' => 'QD.185616212',
                'nama' => 'Minyak Sereh Cap Daun Karton 144 pcs (60 mL)',
                'id_kategori' => 'EL',
                'satuan' => 'karton',
                'isi_per_pack' => 144,
                'harga' => 1584000, // 144 x Rp 11.000
                'harga_pack' => 1584000,
                'stok' => 10,
            ],
            [
                'sku' => 'DA-ALK-KRT144',
                'barcode' => null,
                'no_bpom' => 'QD.185616213',
                'nama' => 'Minyak Akar Lawang Cap Daun Karton 144 pcs (60 mL)',
                'id_kategori' => 'SP01',
                'satuan' => 'karton',
                'isi_per_pack' => 144,
                'harga' => 1440000, // 144 x Rp 10.000
                'harga_pack' => 1440000,
                'stok' => 10,
            ],
            [
                'sku' => 'DA-KYP-KRT144',
                'barcode' => null,
                'no_bpom' => 'QD.185616214',
                'nama' => 'Minyak Kayu Putih Cap Daun Karton 144 pcs (60 mL)',
                'id_kategori' => 'TH',
                'satuan' => 'karton',
                'isi_per_pack' => 144,
                'harga' => 2160000, // 144 x Rp 15.000
                'harga_pack' => 2160000,
                'stok' => 10,
            ],

            // E. Produk Pack 140 mL (1 pack = 72 pcs)
            [
                'sku' => 'LR-GSU-PCK72',
                'barcode' => null,
                'no_bpom' => 'QD.185616215',
                'nama' => 'Minyak Gosok Urut Cap Laron Pack 72 pcs (140 mL)',
                'id_kategori' => 'AR',
                'satuan' => 'pack',
                'isi_per_pack' => 72,
                'harga' => 1800000, // 72 x Rp 25.000
                'harga_pack' => 1800000,
                'stok' => 5,
            ],
            [
                'sku' => 'DA-ALK-PCK72',
                'barcode' => null,
                'no_bpom' => 'QD.185616216',
                'nama' => 'Minyak Akar Lawang Cap Daun Pack 72 pcs (140 mL)',
                'id_kategori' => 'HB',
                'satuan' => 'pack',
                'isi_per_pack' => 72,
                'harga' => 1368000, // 72 x Rp 19.000
                'harga_pack' => 1368000,
                'stok' => 5,
            ],
            [
                'sku' => 'DA-KYP-PCK72',
                'barcode' => null,
                'no_bpom' => 'QD.185616217',
                'nama' => 'Minyak Kayu Putih Cap Daun Pack 72 pcs (140 mL)',
                'id_kategori' => 'EL',
                'satuan' => 'pack',
                'isi_per_pack' => 72,
                'harga' => 2160000, // 72 x Rp 30.000
                'harga_pack' => 2160000,
                'stok' => 5,
            ],

            // F. Produk Pack 275 mL (1 pack = 36 pcs)
            [
                'sku' => 'LR-GSU-PCK36',
                'barcode' => null,
                'no_bpom' => 'QD.185616218',
                'nama' => 'Minyak Gosok Urut Cap Laron Pack 36 pcs (275 mL)',
                'id_kategori' => 'SP01',
                'satuan' => 'pack',
                'isi_per_pack' => 36,
                'harga' => 1620000, // 36 x Rp 45.000
                'harga_pack' => 1620000,
                'stok' => 5,
            ],
            [
                'sku' => 'DA-ALK-PCK36',
                'barcode' => null,
                'no_bpom' => 'QD.185616219',
                'nama' => 'Minyak Akar Lawang Cap Daun Pack 36 pcs (275 mL)',
                'id_kategori' => 'TH',
                'satuan' => 'pack',
                'isi_per_pack' => 36,
                'harga' => 1368000, // 36 x Rp 38.000
                'harga_pack' => 1368000,
                'stok' => 5,
            ],
            [
                'sku' => 'DA-KYP-PCK36',
                'barcode' => null,
                'no_bpom' => 'QD.185616220',
                'nama' => 'Minyak Kayu Putih Cap Daun Pack 36 pcs (275 mL)',
                'id_kategori' => 'AR',
                'satuan' => 'pack',
                'isi_per_pack' => 36,
                'harga' => 2052000, // 36 x Rp 57.000
                'harga_pack' => 2052000,
                'stok' => 5,
            ],
        ];

        foreach ($products as $product) {
            Produk::create($product);
        }
    }
}
