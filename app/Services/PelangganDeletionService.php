<?php

namespace App\Services;

use App\Models\Pelanggan;

/**
 * Service untuk validasi dan penanganan penghapusan pelanggan
 * Memastikan pelanggan hanya dapat dihapus jika tidak memiliki relasi terkait
 */
class PelangganDeletionService
{
    /**
     * Validasi apakah pelanggan dapat dihapus
     * Mengembalikan array dengan status validasi dan pesan detail
     *
     * @return array ['can_delete' => bool, 'message' => string, 'reasons' => array]
     */
    public function validateDeletion(Pelanggan $pelanggan): array
    {
        $reasons = [];

        // Cek riwayat transaksi
        $transactionCount = $pelanggan->transaksi()->count();
        if ($transactionCount > 0) {
            $reasons[] = "Memiliki {$transactionCount} riwayat transaksi";
        }

        // Cek kontrak kredit aktif
        $contractCount = $pelanggan->kontrakKredit()->count();
        if ($contractCount > 0) {
            $reasons[] = "Memiliki {$contractCount} kontrak kredit";
        }

        // Cek saldo kredit yang belum lunas
        $outstandingBalance = (float) $pelanggan->saldo_kredit;
        if ($outstandingBalance > 0) {
            $reasons[] = 'Memiliki saldo kredit yang belum lunas (Rp '.number_format($outstandingBalance, 0, ',', '.').')';
        }

        // Jika ada alasan, tidak bisa dihapus
        if (! empty($reasons)) {
            $reasonList = implode(', ', $reasons);

            return [
                'can_delete' => false,
                'message' => "Pelanggan tidak dapat dihapus karena: {$reasonList}",
                'reasons' => $reasons,
            ];
        }

        return [
            'can_delete' => true,
            'message' => 'Pelanggan dapat dihapus',
            'reasons' => [],
        ];
    }

    /**
     * Get detailed information tentang why customer cannot be deleted
     *
     * @return array Detailed information
     */
    public function getDeletionBlockReasons(Pelanggan $pelanggan): array
    {
        $details = [];

        // Transaction details
        $transactionCount = $pelanggan->transaksi()->count();
        if ($transactionCount > 0) {
            $totalAmount = $pelanggan->transaksi()->sum('total');
            $details['transactions'] = [
                'count' => $transactionCount,
                'total_amount' => (float) $totalAmount,
                'message' => "Pelanggan telah melakukan {$transactionCount} transaksi dengan total Rp ".number_format($totalAmount, 0, ',', '.'),
            ];
        }

        // Credit contract details
        $contractCount = $pelanggan->kontrakKredit()->count();
        if ($contractCount > 0) {
            $details['contracts'] = [
                'count' => $contractCount,
                'message' => "Pelanggan memiliki {$contractCount} kontrak kredit yang masih terdaftar",
            ];
        }

        // Outstanding balance details
        $outstandingBalance = (float) $pelanggan->saldo_kredit;
        if ($outstandingBalance > 0) {
            $details['outstanding_balance'] = [
                'amount' => $outstandingBalance,
                'message' => 'Saldo kredit yang belum lunas: Rp '.number_format($outstandingBalance, 0, ',', '.'),
            ];
        }

        return $details;
    }
}
