<?php

namespace App\Services;

use App\Models\KonversiStok;
use App\Models\Produk;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KonversiStokService
{
    /**
     * Melakukan konversi stok dari karton ke pcs dengan logika parsial
     *
     * @param  int  $fromProdukId  ID produk sumber (karton)
     * @param  int  $toProdukId  ID produk tujuan (pcs)
     * @param  int  $qtyTo  Jumlah PCS yang diminta
     * @param  string  $mode  'penuh' atau 'parsial'
     * @param  int  $rasio  Rasio konversi (1 karton = ? pcs)
     * @param  string|null  $keterangan  Keterangan tambahan
     * @return KonversiStok Data konversi yang tersimpan
     *
     * @throws Exception Jika stok tidak cukup atau data tidak valid
     */
    public function convert(
        int $fromProdukId,
        int $toProdukId,
        int $qtyTo,
        string $mode = 'penuh',
        int $rasio = 1,
        ?string $keterangan = null
    ): KonversiStok {
        // Validasi input
        if ($qtyTo <= 0) {
            throw new Exception('Jumlah PCS yang diminta harus lebih dari 0');
        }

        if (! in_array($mode, ['penuh', 'parsial'])) {
            throw new Exception('Mode harus "penuh" atau "parsial"');
        }

        if ($rasio <= 0) {
            throw new Exception('Rasio konversi harus lebih dari 0');
        }

        return DB::transaction(function () use ($fromProdukId, $toProdukId, $qtyTo, $mode, $rasio, $keterangan) {
            // Lock untuk mencegah race condition
            $fromProduk = Produk::lockForUpdate()->findOrFail($fromProdukId);
            $toProduk = Produk::lockForUpdate()->findOrFail($toProdukId);

            // Validasi produk
            if ($fromProduk->satuan !== 'karton') {
                throw new Exception("Produk sumber harus bersatuan karton, bukan {$fromProduk->satuan}");
            }

            if ($toProduk->satuan !== 'pcs') {
                throw new Exception("Produk tujuan harus bersatuan pcs, bukan {$toProduk->satuan}");
            }

            if ($fromProduk->isi_per_pack <= 0) {
                throw new Exception('Produk sumber harus memiliki isi_per_pack > 0');
            }

            // Hitung kebutuhan berdasarkan mode
            $conversionData = $this->calculateConversion(
                $fromProduk,
                $qtyTo,
                $mode,
                $fromProduk->isi_per_pack
            );

            // Validasi stok cukup
            if ($conversionData['packs_needed'] > $fromProduk->stok) {
                throw new Exception(
                    "Stok karton tidak cukup. Dibutuhkan {$conversionData['packs_needed']} karton, ".
                    "tersedia {$fromProduk->stok} karton"
                );
            }

            // Update stok produk sumber (karton)
            $fromProduk->decrement('stok', $conversionData['packs_needed']);

            // Update buffer sisa PCS
            $newSisaBuffer = $conversionData['sisa_buffer_after'];
            $fromProduk->update(['sisa_pcs_terbuka' => $newSisaBuffer]);

            // Tambahkan stok produk tujuan (pcs)
            $toProduk->increment('stok', $qtyTo);

            // Buat record konversi dengan audit trail
            $konversi = KonversiStok::create([
                'from_produk_id' => $fromProdukId,
                'to_produk_id' => $toProdukId,
                'rasio' => $rasio,
                'qty_from' => $conversionData['packs_needed'],
                'qty_to' => $qtyTo,
                'mode' => $mode,
                'keterangan' => $keterangan,
                'packs_used' => $conversionData['packs_needed'],
                'dari_buffer' => $conversionData['dari_buffer'],
                'sisa_buffer_after' => $newSisaBuffer,
            ]);

            return $konversi;
        });
    }

    /**
     * Hitung kebutuhan konversi berdasarkan mode
     *
     * Logika:
     * 1. Jika ada sisa_pcs_terbuka dan cukup untuk qty_to, ambil dari buffer
     * 2. Jika tidak cukup di buffer, buka karton baru dan hitung sisa
     *
     * @param  Produk  $produk  Produk sumber dengan buffer
     * @param  int  $qtyTo  Jumlah PCS yang diminta
     * @param  string  $mode  Mode konversi
     * @param  int  $isiPerPack  Jumlah PCS per karton
     * @return array Berisi: packs_needed, dari_buffer, sisa_buffer_after
     */
    private function calculateConversion(
        Produk $produk,
        int $qtyTo,
        string $mode,
        int $isiPerPack
    ): array {
        $sisaBuffer = (int) $produk->sisa_pcs_terbuka;
        $dariBuffer = 0;
        $packsNeeded = 0;

        // Coba ambil dari buffer terlebih dahulu
        if ($sisaBuffer > 0 && $sisaBuffer >= $qtyTo) {
            // Buffer cukup, ambil dari buffer saja
            $dariBuffer = $qtyTo;
            $sisaBuffer -= $qtyTo;
        } elseif ($sisaBuffer > 0 && $sisaBuffer < $qtyTo) {
            // Buffer tidak cukup, ambil sebanyak mungkin dari buffer
            $dariBuffer = $sisaBuffer;
            $qtyTerserah = $qtyTo - $dariBuffer; // Sisa yang perlu dari karton baru

            // Hitung karton yang perlu dibuka
            $packsNeeded = ceil($qtyTerserah / $isiPerPack);

            // Hitung sisa dari karton terakhir
            $totalPcsAkhir = $packsNeeded * $isiPerPack;
            $sisaBuffer = $totalPcsAkhir - $qtyTerserah;
        } else {
            // Tidak ada buffer, hitung karton yang perlu dibuka
            $packsNeeded = ceil($qtyTo / $isiPerPack);

            // Hitung sisa dari karton terakhir
            $totalPcsAkhir = $packsNeeded * $isiPerPack;
            $sisaBuffer = $totalPcsAkhir - $qtyTo;
        }

        return [
            'packs_needed' => $packsNeeded,
            'dari_buffer' => $dariBuffer,
            'sisa_buffer_after' => $sisaBuffer,
        ];
    }

    /**
     * Reverse konversi stok (undo)
     *
     * @param  int  $konversiId  ID konversi yang akan di-reverse
     * @return bool Success
     *
     * @throws Exception
     */
    public function reverse(int $konversiId): bool
    {
        return DB::transaction(function () use ($konversiId) {
            $konversi = KonversiStok::lockForUpdate()->findOrFail($konversiId);

            $fromProduk = Produk::lockForUpdate()->findOrFail($konversi->from_produk_id);
            $toProduk = Produk::lockForUpdate()->findOrFail($konversi->to_produk_id);

            // Reverse stok produk sumber (tambah karton kembali)
            $fromProduk->increment('stok', $konversi->packs_used);

            // Reverse buffer sisa PCS
            // Logika restore buffer:
            // - Jika packs_used = 0, buffer asli = sisa_buffer_after + dari_buffer (ambil dari buffer saja)
            // - Jika packs_used > 0, buffer asli = dari_buffer (buffer lama habis, karton baru dibuka)
            if ($konversi->packs_used > 0) {
                // Ada karton yang dibuka, buffer original adalah yang diambil dari buffer lama
                $bufferOriginal = $konversi->dari_buffer;
            } else {
                // Tidak ada karton dibuka, semua dari buffer, restore buffer
                $bufferOriginal = $konversi->sisa_buffer_after + $konversi->dari_buffer;
            }
            $fromProduk->update(['sisa_pcs_terbuka' => $bufferOriginal]);

            // Reverse stok produk tujuan (kurangi pcs)
            $toProduk->decrement('stok', $konversi->qty_to);

            // Hapus record konversi
            $konversi->delete();

            return true;
        });
    }

    /**
     * Bulk reverse konversi stok
     *
     * @param  array  $konversiIds  Array ID konversi yang akan di-reverse
     * @return int Jumlah konversi yang berhasil di-reverse
     */
    public function bulkReverse(array $konversiIds): int
    {
        $reversed = 0;

        foreach ($konversiIds as $id) {
            try {
                if ($this->reverse($id)) {
                    $reversed++;
                }
            } catch (Exception $e) {
                Log::warning("Failed to reverse konversi_stok ID {$id}: {$e->getMessage()}");

                continue;
            }
        }

        return $reversed;
    }
}
