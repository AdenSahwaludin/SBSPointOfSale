<?php

namespace App\Helpers;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Support\Str;

class SKUGenerator
{
    /**
     * Generate SKU berdasarkan nama produk, kategori, dan satuan
     * Format: [KODE_KATEGORI]-[NAMA_TOKEN]-[KEMASAN]
     *
     * Contoh:
     * - HB-MKP120-KRT12 (17 karakter)
     * - EL-BLENDER-BOX24 (18 karakter)
     */
    public static function generate(string $namaProduk, string $idKategori, string $satuan, int $isiPerPack = 1): string
    {
        // Ambil kode kategori dari tabel kategori
        $kategori = Kategori::where('id_kategori', $idKategori)->value('id_kategori') ?? 'XX';

        // Buat token nama produk (3-6 huruf + angka volume jika ada)
        $namaToken = strtoupper(Str::of($namaProduk)
            ->replace([' ', '_', '-'], '')
            ->replaceMatches('/[^A-Z0-9]/i', '')
            ->substr(0, 6));

        // Tentukan kemasan berdasarkan satuan
        $kemasan = match ($satuan) {
            'pcs' => 'PCS',
            'karton' => 'KRT'.$isiPerPack,
            'pack' => 'PCK'.$isiPerPack,
            default => 'PCS',
        };

        // Gabungkan jadi SKU dasar
        $skuBase = "{$kategori}-{$namaToken}-{$kemasan}";
        $sku = $skuBase;
        $i = 1;

        // Cek apakah SKU sudah ada di DB, jika ya tambah suffix -001 dst.
        while (Produk::where('sku', $sku)->exists()) {
            $sku = $skuBase.'-'.str_pad($i++, 3, '0', STR_PAD_LEFT);
        }

        return $sku;
    }
}
