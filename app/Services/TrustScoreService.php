<?php

namespace App\Services;

use App\Models\Pelanggan;

class TrustScoreService
{
    /**
     * Apply the account-age rule from the briefing:
     * - Baseline 50
     * - ≥ 30 days: +10 (min 60)
     * - ≥ 180 days: +20 (min 70)
     * The update is monotonic (never decreases existing trust_score).
     */
    public static function applyAccountAgeRule(Pelanggan $pelanggan): void
    {
        if (! $pelanggan->created_at || $pelanggan->id_pelanggan === 'P001') {
            return;
        }

        // Compute days from created_at to now to avoid sign/timezone inconsistencies
        $ageDays = $pelanggan->created_at->diffInDays(now());

        $minScore = 50;
        if ($ageDays >= 180) {
            $minScore = 70; // 50 + 20
        } elseif ($ageDays >= 30) {
            $minScore = 60; // 50 + 10
        }

        // Ensure trust_score does not decrease; clamp upper bound at 100
        $newScore = max((int) $pelanggan->trust_score, $minScore);
        $newScore = min($newScore, 100);

        if ($newScore !== (int) $pelanggan->trust_score) {
            // Use forceFill to ensure the attribute is set regardless of mass-assignment rules
            $pelanggan->forceFill(['trust_score' => $newScore])->save();
        }
    }

    /**
     * Calculate the full trust score breakdown for a customer based on rules.
     * Returns an associative array with each component and the total.
     */
    public static function calculateFullScore(Pelanggan $pelanggan): array
    {
        $baseline = 50;

        // P001 is Pelanggan Umum, always return 50
        if ($pelanggan->id_pelanggan === 'P001') {
            return [
                'baseline' => $baseline,
                'account_age' => 0,
                'p_umur' => 0,
                'installment_history' => 0,
                'p_tepat' => 0,
                'p_telat' => 0,
                'p_gagal' => 0,
                'shopping_frequency' => 0,
                'p_frekuensi' => 0,
                'transaction_value' => 0,
                'p_nilai' => 0,
                'active_arrears' => 0,
                'p_tunggakan' => 0,
                'total' => $baseline,
            ];
        }

        // P_umur: 30-179 days = +10, >= 180 days = +20
        $pUmur = 0;
        if ($pelanggan->created_at) {
            $ageDays = $pelanggan->created_at->diffInDays(now());
            if ($ageDays >= 180) {
                $pUmur = 20;
            } elseif ($ageDays >= 30) {
                $pUmur = 10;
            }
        }

        // Installment History Components
        $pTepat = 0; // +2 per completed contract on time (tenor 3/6/9/12), max +20
        $pTelat = 0; // -5 per late installment
        $pGagal = 0; // -25 per failed (VOID) installment

        $contracts = \App\Models\KontrakKredit::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->with('jadwalAngsuran')
            ->get();

        foreach ($contracts as $contract) {
            $tenor = (int) $contract->tenor_bulan;
            $installments = $contract->jadwalAngsuran;
            
            // Check if contract is completed (LUNAS or all installments are PAID)
            $isCompleted = ($contract->status === 'LUNAS') || ($installments->isNotEmpty() && $installments->where('status', '!=', 'PAID')->isEmpty());
            
            $hasLateOrFailed = false;
            foreach ($installments as $angsuran) {
                $status = (string) $angsuran->status;
                $isLate = false;
                
                if ($status === 'PAID') {
                    if ($angsuran->paid_at && $angsuran->jatuh_tempo && $angsuran->paid_at->greaterThan($angsuran->jatuh_tempo)) {
                        $isLate = true;
                    }
                } elseif ($status === 'LATE' || $angsuran->isOverdue()) {
                    $isLate = true;
                } elseif ($status === 'VOID') {
                    $pGagal += 25;
                    $hasLateOrFailed = true;
                }
                
                if ($isLate) {
                    $pTelat += 5;
                    $hasLateOrFailed = true;
                }
            }
            
            // Ptepat: +2 per completed contract on time (tenor 3/6/9/12 months)
            if ($isCompleted && !$hasLateOrFailed && in_array($tenor, [3, 6, 9, 12])) {
                $pTepat += 2;
            }
        }

        // Apply cap to P_tepat
        $pTepat = min($pTepat, 20);

        // P_frekuensi: +5 if customer has >= 3 transactions in each of the last 3 months
        $m1 = \App\Models\Transaksi::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('status_pembayaran', '!=', 'BATAL')
            ->whereBetween('tanggal', [now()->subMonth(), now()])
            ->count();
        $m2 = \App\Models\Transaksi::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('status_pembayaran', '!=', 'BATAL')
            ->whereBetween('tanggal', [now()->subMonths(2), now()->subMonth()])
            ->count();
        $m3 = \App\Models\Transaksi::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('status_pembayaran', '!=', 'BATAL')
            ->whereBetween('tanggal', [now()->subMonths(3), now()->subMonths(2)])
            ->count();
        $pFrekuensi = ($m1 >= 3 && $m2 >= 3 && $m3 >= 3) ? 5 : 0;

        // P_nilai: +5 if average transaction > store median
        $pNilai = 0;
        $allTotals = \App\Models\Transaksi::where('status_pembayaran', '!=', 'BATAL')->pluck('total');
        if ($allTotals->count() > 0) {
            $median = $allTotals->map(fn ($t) => (float) $t)->median();
            $avg = (float) \App\Models\Transaksi::where('id_pelanggan', $pelanggan->id_pelanggan)
                ->where('status_pembayaran', '!=', 'BATAL')
                ->avg('total');
            if ($avg > $median) {
                $pNilai = 5;
            }
        }

        // P_tunggakan: -10 if any active arrears (DUE or LATE that is overdue, meaning jatuh_tempo < Carbon::today())
        $hasActiveArrears = \App\Models\JadwalAngsuran::whereHas('kontrakKredit', function ($q) use ($pelanggan) {
            $q->where('id_pelanggan', $pelanggan->id_pelanggan);
        })
        ->where('status', '!=', 'PAID')
        ->where('jatuh_tempo', '<', \Carbon\Carbon::today())
        ->exists();

        $pTunggakan = $hasActiveArrears ? 10 : 0;

        // TS = 50 + P_umur + P_tepat + P_frekuensi + P_nilai - P_telat - P_gagal - P_tunggakan
        $total = $baseline 
            + $pUmur 
            + $pTepat 
            + $pFrekuensi 
            + $pNilai 
            - $pTelat 
            - $pGagal 
            - $pTunggakan;

        // Clamp to 0..100
        $total = max(0, min(100, (int) round($total)));

        return [
            'baseline' => $baseline,
            // Dual-mapped keys for compatibility with CLI and other services
            'account_age' => $pUmur,
            'p_umur' => $pUmur,
            'installment_history' => $pTepat - $pTelat - $pGagal,
            'p_tepat' => $pTepat,
            'p_telat' => -$pTelat,
            'p_gagal' => -$pGagal,
            'shopping_frequency' => $pFrekuensi,
            'p_frekuensi' => $pFrekuensi,
            'transaction_value' => $pNilai,
            'p_nilai' => $pNilai,
            'active_arrears' => -$pTunggakan,
            'p_tunggakan' => -$pTunggakan,
            'total' => $total,
        ];
    }

    /**
     * Recalculate the customer's trust score and persist it.
     * If $withBreakdown is true, returns the breakdown array; otherwise returns the new score (int).
     */
    public static function updateTrustScore(Pelanggan $pelanggan, bool $withBreakdown = false)
    {
        $breakdown = self::calculateFullScore($pelanggan);

        if ((int) $pelanggan->trust_score !== (int) $breakdown['total']) {
            $pelanggan->forceFill(['trust_score' => (int) $breakdown['total']])->save();
        }

        return $withBreakdown ? $breakdown : (int) $breakdown['total'];
    }
}
