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
        if (! $pelanggan->created_at) {
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
        $pTepat = 0; // +2 per on-time, max +20
        $pTelat = 0; // -5 per late
        $pGagal = 0; // -25 per failed (VOID)

        $installments = \App\Models\JadwalAngsuran::whereHas('kontrakKredit', function ($q) use ($pelanggan) {
            $q->where('id_pelanggan', $pelanggan->id_pelanggan);
        })->get(['status', 'paid_at', 'jatuh_tempo']);

        foreach ($installments as $angsuran) {
            $status = (string) $angsuran->status;
            if ($status === 'LUNAS') {
                // On-time if paid_at <= due date
                if ($angsuran->paid_at && $angsuran->jatuh_tempo && $angsuran->paid_at->lessThanOrEqualTo($angsuran->jatuh_tempo)) {
                    $pTepat += 2;
                } else {
                    $pTelat += 5;
                }
            } elseif ($status === 'VOID') {
                $pGagal += 25;
            }
        }

        // Apply cap to P_tepat
        $pTepat = min($pTepat, 20);

        // P_frekuensi: +5 if >= 3 transactions per month in last 3 months (avg >= 3/mo = total 9)
        $threeMonthsAgo = now()->subMonths(3);
        $recentTxnCount = \App\Models\Transaksi::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->whereIn('jenis_transaksi', ['TUNAI', 'TRANSFER'])
            ->where('tanggal', '>=', $threeMonthsAgo)
            ->count();
        $pFrekuensi = $recentTxnCount >= 9 ? 5 : 0;

        // P_nilai: +5 if average transaction > store median
        $pNilai = 0;
        $allTotals = \App\Models\Transaksi::whereIn('jenis_transaksi', ['TUNAI', 'TRANSFER'])->pluck('total');
        if ($allTotals->count() > 0) {
            $median = $allTotals->map(fn ($t) => (float) $t)->median();
            $avg = (float) \App\Models\Transaksi::where('id_pelanggan', $pelanggan->id_pelanggan)
                ->whereIn('jenis_transaksi', ['TUNAI', 'TRANSFER'])
                ->avg('total');
            if ($avg > $median) {
                $pNilai = 5;
            }
        }

        // P_tunggakan: -10 if any active arrears (DUE or LATE)
        $hasActiveArrears = \App\Models\JadwalAngsuran::whereHas('kontrakKredit', function ($q) use ($pelanggan) {
            $q->where('id_pelanggan', $pelanggan->id_pelanggan);
        })->whereIn('status', ['DUE', 'LATE'])->exists();

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
            'p_umur' => $pUmur,
            'p_tepat' => $pTepat,
            'p_telat' => -$pTelat,
            'p_gagal' => -$pGagal,
            'p_frekuensi' => $pFrekuensi,
            'p_nilai' => $pNilai,
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
