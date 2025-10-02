<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'id_produk' => 'PRD001',
                'nama_produk' => 'Nasi Goreng Spesial',
                'deskripsi' => 'Nasi goreng dengan telur, ayam, dan sayuran',
                'harga' => 25000,
                'stok' => 50,
                'kategori' => 'Makanan',
                'barcode' => '123456789001',
                'status' => 'aktif',
            ],
            [
                'id_produk' => 'PRD002',
                'nama_produk' => 'Mie Ayam Bakso',
                'deskripsi' => 'Mie ayam dengan bakso dan pangsit',
                'harga' => 20000,
                'stok' => 30,
                'kategori' => 'Makanan',
                'barcode' => '123456789002',
                'status' => 'aktif',
            ],
            [
                'id_produk' => 'PRD003',
                'nama_produk' => 'Es Teh Manis',
                'deskripsi' => 'Teh manis dingin segar',
                'harga' => 5000,
                'stok' => 100,
                'kategori' => 'Minuman',
                'barcode' => '123456789003',
                'status' => 'aktif',
            ],
            [
                'id_produk' => 'PRD004',
                'nama_produk' => 'Kopi Hitam',
                'deskripsi' => 'Kopi hitam panas atau dingin',
                'harga' => 8000,
                'stok' => 80,
                'kategori' => 'Minuman',
                'barcode' => '123456789004',
                'status' => 'aktif',
            ],
            [
                'id_produk' => 'PRD005',
                'nama_produk' => 'Ayam Bakar',
                'deskripsi' => 'Ayam bakar bumbu kecap dengan nasi',
                'harga' => 35000,
                'stok' => 25,
                'kategori' => 'Makanan',
                'barcode' => '123456789005',
                'status' => 'aktif',
            ],
            [
                'id_produk' => 'PRD006',
                'nama_produk' => 'Jus Jeruk',
                'deskripsi' => 'Jus jeruk segar tanpa gula tambahan',
                'harga' => 12000,
                'stok' => 40,
                'kategori' => 'Minuman',
                'barcode' => '123456789006',
                'status' => 'aktif',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
