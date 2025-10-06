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
        // Cari ID kategori Minyak Akar Lawang
        $idKategori = Kategori::where('nama', 'Minyak Akar Lawang')->value('id_kategori');

        // Tambahkan produk baru
        Produk::create([
            'id_produk' => '8997000610060',
            'nama' => 'Minyak Akar Lawang Cap Daun 60 ml',
            'gambar' => '/images/produk/minyak-akar-lawang-60ml.jpg',
            'nomor_bpom' => 'POM QD185616211',
            'harga' => 14000,
            'biaya_produk' => 8500,
            'stok' => 500,
            'batas_stok' => 48,
            'satuan' => 'pcs',
            'satuan_pack' => 'karton',
            'isi_per_pack' => 144,
            'harga_pack' => 1440000,
            'min_beli_diskon' => 3,
            'harga_diskon_unit' => 13500,
            'harga_diskon_pack' => 1440000,
            'id_kategori' => $idKategori,
        ]);
    }
}
