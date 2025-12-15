<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk sinkronisasi dan validasi credit pelanggan
 * Memastikan credit_limit dan saldo_kredit selalu konsisten
 */
class CreditSyncService
{
    /**
     * Validasi status kredit pelanggan sebelum transaksi
     * Memastikan pelanggan dengan status_kredit != 'aktif' tidak dapat melakukan transaksi kredit
     *
     * @return array ['valid' => bool, 'message' => string]
     */
    public function validateCreditEligibility(string $idPelanggan): array
    {
        $pelanggan = Pelanggan::find($idPelanggan);

        if (! $pelanggan) {
            return [
                'valid' => false,
                'message' => 'Pelanggan tidak ditemukan',
            ];
        }

        // Check status kredit
        if ($pelanggan->status_kredit !== 'aktif') {
            return [
                'valid' => false,
                'message' => 'Status kredit pelanggan: '.$pelanggan->status_kredit.'. Akses kredit tidak tersedia.',
            ];
        }

        return [
            'valid' => true,
            'message' => 'Pelanggan memiliki akses kredit aktif',
        ];
    }

    /**
     * Sinkronisasi credit_limit dan saldo_kredit berdasarkan transaksi actual
     * Menghitung ulang saldo berdasarkan:
     * - Total kredit yang digunakan dari semua transaksi KREDIT yang belum LUNAS
     * - Restore kredit dari transaksi yang sudah LUNAS
     */
    public function syncCreditBalance(string $idPelanggan): void
    {
        $pelanggan = Pelanggan::find($idPelanggan);

        if (! $pelanggan) {
            return;
        }

        // Hitung total outstanding (kredit yang masih belum dibayar)
        $outstandingAmount = Transaksi::where('id_pelanggan', $idPelanggan)
            ->where('jenis_transaksi', Transaksi::JENIS_KREDIT)
            ->whereIn('status_pembayaran', [Transaksi::STATUS_MENUNGGU])
            ->sum(DB::raw('(total - dp)'));

        $outstandingAmount = max(0, (float) $outstandingAmount);

        // Hitung total credit yang telah diproses (baik belum atau sudah lunas)
        $totalProcessedCredit = Transaksi::where('id_pelanggan', $idPelanggan)
            ->where('jenis_transaksi', Transaksi::JENIS_KREDIT)
            ->where('status_pembayaran', '!=', Transaksi::STATUS_BATAL)
            ->sum(DB::raw('(total - dp)'));

        $totalProcessedCredit = max(0, (float) $totalProcessedCredit);

        // Ambil original credit limit dari history atau gunakan default
        $originalCreditLimit = $this->getOriginalCreditLimit($idPelanggan);

        // Hitung available credit limit
        $availableCredit = $originalCreditLimit - $outstandingAmount;
        $availableCredit = max(0, $availableCredit);

        // Update pelanggan
        $pelanggan->credit_limit = $availableCredit;
        $pelanggan->saldo_kredit = $outstandingAmount;
        $pelanggan->save();
    }

    /**
     * Restore credit limit ketika transaksi ditandai LUNAS
     * Memastikan saldo_kredit dikurangi dan credit_limit ditambah
     */
    public function restoreCreditFromLunasTransaction(Transaksi $transaksi): void
    {
        if ($transaksi->jenis_transaksi !== Transaksi::JENIS_KREDIT) {
            return;
        }

        $outstanding = max(0, (float) $transaksi->total - (float) $transaksi->dp);

        if ($outstanding <= 0) {
            return;
        }

        $pelanggan = Pelanggan::find($transaksi->id_pelanggan);

        if (! $pelanggan) {
            return;
        }

        // Restore available credit limit
        $pelanggan->credit_limit = ((float) $pelanggan->credit_limit) + $outstanding;

        // Reduce outstanding balance (saldo_kredit)
        $pelanggan->saldo_kredit = max(0, ((float) $pelanggan->saldo_kredit) - $outstanding);

        // Ensure status is still aktif if there's remaining balance
        if ($pelanggan->saldo_kredit > 0) {
            $pelanggan->status_kredit = 'aktif';
        }

        $pelanggan->save();
    }

    /**
     * Kurangi available credit saat transaksi kredit baru dibuat
     * Memastikan saldo_kredit bertambah dengan proporsi yang benar
     */
    public function deductCreditFromNewTransaction(string $idPelanggan, float $creditAmount): void
    {
        $pelanggan = Pelanggan::find($idPelanggan);

        if (! $pelanggan) {
            return;
        }

        $creditAmount = max(0, (float) $creditAmount);

        // Kurangi available limit
        $pelanggan->credit_limit = max(0, ((float) $pelanggan->credit_limit) - $creditAmount);

        // Tambah outstanding balance
        $pelanggan->saldo_kredit = ((float) $pelanggan->saldo_kredit) + $creditAmount;

        // Pastikan status aktif
        $pelanggan->status_kredit = 'aktif';

        $pelanggan->save();
    }

    /**
     * Get original credit limit sebelum transaksi apapun
     * Jika tidak ada history, gunakan nilai terbesar antara credit_limit + saldo_kredit atau kredit basis
     */
    private function getOriginalCreditLimit(string $idPelanggan): float
    {
        $pelanggan = Pelanggan::find($idPelanggan);

        if (! $pelanggan) {
            return 0;
        }

        // Original limit = current available + outstanding balance
        $available = (float) $pelanggan->credit_limit;
        $outstanding = (float) ($pelanggan->saldo_kredit ?? 0);

        return $available + $outstanding;
    }

    /**
     * Validasi konsistensi credit data
     * Mengembalikan info tentang ketidaksesuaian jika ada
     *
     * @return array ['consistent' => bool, 'issues' => array]
     */
    public function validateCreditConsistency(string $idPelanggan): array
    {
        $pelanggan = Pelanggan::find($idPelanggan);

        if (! $pelanggan) {
            return ['consistent' => false, 'issues' => ['Pelanggan tidak ditemukan']];
        }

        $issues = [];

        // Check 1: saldo_kredit harus non-negative
        if ((float) $pelanggan->saldo_kredit < 0) {
            $issues[] = 'saldo_kredit bernilai negatif: '.((float) $pelanggan->saldo_kredit);
        }

        // Check 2: credit_limit harus non-negative
        if ((float) $pelanggan->credit_limit < 0) {
            $issues[] = 'credit_limit bernilai negatif: '.((float) $pelanggan->credit_limit);
        }

        // Check 3: status_kredit harus 'aktif' atau 'nonaktif'
        if (! in_array($pelanggan->status_kredit, ['aktif', 'nonaktif'])) {
            $issues[] = 'status_kredit invalid: '.$pelanggan->status_kredit;
        }

        // Check 4: Jika saldo_kredit > 0, status harus aktif (atau sedang dalam status transisi)
        if ((float) $pelanggan->saldo_kredit > 0 && $pelanggan->status_kredit !== 'aktif') {
            $issues[] = 'saldo_kredit > 0 tetapi status_kredit = '.$pelanggan->status_kredit;
        }

        return [
            'consistent' => count($issues) === 0,
            'issues' => $issues,
        ];
    }
}
