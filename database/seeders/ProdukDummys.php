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
        // Manual Products (Migrated from ProdukDummy.php)
        $manualProducts = [
            // AROMATHERAPY
            [
                'sku' => 'DA-LAV-60ML',
                'nama' => 'Minyak Aromaterapi Lavender 60 mL',
                'id_kategori' => 'AR',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 18000,
                'harga_pack' => 17500,
                'stok' => 120,
                'no_bpom' => 'NA18230100001',
            ],
            [
                'sku' => 'DA-LAV-140ML',
                'nama' => 'Minyak Aromaterapi Lavender 140 mL',
                'id_kategori' => 'AR',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 35000,
                'harga_pack' => 34000,
                'stok' => 80,
                'no_bpom' => 'NA18230100002',
            ],
            // ESSENTIAL OIL
            [
                'sku' => 'DA-TEA-60ML',
                'nama' => 'Essential Oil Tea Tree 60 mL',
                'id_kategori' => 'EL',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 22000,
                'harga_pack' => 21000,
                'stok' => 95,
                'no_bpom' => 'NA18230100003',
            ],
            [
                'sku' => 'DA-EUC-140ML',
                'nama' => 'Essential Oil Eucalyptus 140 mL',
                'id_kategori' => 'EL',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 36000,
                'harga_pack' => 35000,
                'stok' => 70,
                'no_bpom' => 'NA18230100004',
            ],
            // HERBAL
            [
                'sku' => 'DA-JAE-60ML',
                'nama' => 'Minyak Jahe Herbal 60 mL',
                'id_kategori' => 'HB',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 15000,
                'harga_pack' => 14500,
                'stok' => 110,
                'no_bpom' => 'TR18230100005',
            ],
            [
                'sku' => 'DA-KUN-140ML',
                'nama' => 'Minyak Kunyit Herbal 140 mL',
                'id_kategori' => 'HB',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 30000,
                'harga_pack' => 29000,
                'stok' => 65,
                'no_bpom' => 'TR18230100006',
            ],
            // SPICE OIL
            [
                'sku' => 'DA-CNG-60ML',
                'nama' => 'Minyak Cengkeh 60 mL',
                'id_kategori' => 'SP01',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 17000,
                'harga_pack' => 16500,
                'stok' => 90,
                'no_bpom' => 'TR18230100007',
            ],
            [
                'sku' => 'DA-KYT-140ML',
                'nama' => 'Minyak Kayu Manis 140 mL',
                'id_kategori' => 'SP01',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 34000,
                'harga_pack' => 33000,
                'stok' => 55,
                'no_bpom' => 'TR18230100008',
            ],
            // THERAPEUTIC
            [
                'sku' => 'LR-URU-140ML',
                'nama' => 'Minyak Urut Herbal 140 mL',
                'id_kategori' => 'TH',
                'satuan' => 'pcs',
                'isi_per_pack' => 1,
                'harga' => 32000,
                'harga_pack' => 31000,
                'stok' => 60,
                'no_bpom' => 'QD18230100009',
            ],
            // PACKS & KARTONS
            [
                'sku' => 'DA-LAV-KRT144',
                'nama' => 'Minyak Aromaterapi Lavender Karton 144 pcs',
                'id_kategori' => 'AR',
                'satuan' => 'karton',
                'isi_per_pack' => 144,
                'harga' => 2400000,
                'harga_pack' => 2400000,
                'stok' => 10,
                'no_bpom' => 'NA18230100001',
            ],
            [
                'sku' => 'DA-TEA-PCK72',
                'nama' => 'Essential Oil Tea Tree Pack 72 pcs',
                'id_kategori' => 'EL',
                'satuan' => 'pack',
                'isi_per_pack' => 72,
                'harga' => 1500000,
                'harga_pack' => 1500000,
                'stok' => 15,
                'no_bpom' => 'NA18230100003',
            ],
        ];

        $companyCode = '70006';
        $no = 200; // Start sequence for manual/dummy products

        foreach ($manualProducts as $mp) {
            $mp['barcode'] = ($mp['satuan'] === 'pcs') ? $this->generateEAN13('899', $companyCode, $no++) : null;
            $mp['sisa_pcs_terbuka'] = 0;
            $mp['batas_stok_minimum'] = 20;
            $mp['jumlah_restock'] = 50;
            
            // Use updateOrCreate to avoid unique SKU conflict
            Produk::updateOrCreate(['sku' => $mp['sku']], $mp);
        }

        // Generated Products
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

        foreach ($baseProducts as $bp) {
            foreach ($sizes as $size) {
                $skuBase = "DA-{$bp['code']}-{$size['label']}";
                
                // PCS
                Produk::updateOrCreate(['sku' => $skuBase], [
                    'barcode' => $this->generateEAN13('899', $companyCode, $no++),
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
                Produk::updateOrCreate(['sku' => "{$skuBase}-PCK36"], [
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
                Produk::updateOrCreate(['sku' => "{$skuBase}-KRT144"], [
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

    private function generateEAN13($prefix, $company, $sequence)
    {
        $code = $prefix . str_pad($company, 5, '0', STR_PAD_LEFT) . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int)$code[$i];
            if ($i % 2 === 0) {
                $sum += $digit;
            } else {
                $sum += $digit * 3;
            }
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        return $code . $checkDigit;
    }
}
