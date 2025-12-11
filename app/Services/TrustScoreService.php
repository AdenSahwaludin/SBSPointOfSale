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

        // Account age
        $accountAgeDelta = 0;
        if ($pelanggan->created_at) {
            $ageDays = $pelanggan->created_at->diffInDays(now());
            if ($ageDays >= 180) {
                $accountAgeDelta = 20;
            } elseif ($ageDays >= 30) {
                $accountAgeDelta = 10;
            }
        }

        // Installment history: +2 on-time, -5 late, -25 failed (VOID)
        $installmentDelta = 0;
        $installments = \App\Models\JadwalAngsuran::whereHas('kontrakKredit', function ($q) use ($pelanggan) {
            $q->where('id_pelanggan', $pelanggan->id_pelanggan);
        })->get(['status', 'paid_at', 'jatuh_tempo']);

        foreach ($installments as $angsuran) {
            $status = (string) $angsuran->status;
            if ($status === 'LUNAS') {
                // On-time if paid_at <= due date
                if ($angsuran->paid_at && $angsuran->jatuh_tempo && $angsuran->paid_at->lessThanOrEqualTo($angsuran->jatuh_tempo)) {
                    $installmentDelta += 2;
                } elseif ($angsuran->paid_at && $angsuran->jatuh_tempo && $angsuran->paid_at->greaterThan($angsuran->jatuh_tempo)) {
                    $installmentDelta -= 5;
                }
            } elseif ($status === 'VOID') {
                // Failed payment
                $installmentDelta -= 25;
            }
        }

        // Shopping frequency: +5 if >= 3 transactions in current month
        $now = now();
        $monthlyTxnCount = \App\Models\Transaksi::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->whereYear('tanggal', $now->year)
            ->whereMonth('tanggal', $now->month)
            ->count();
        $shoppingFrequencyDelta = $monthlyTxnCount >= 3 ? 5 : 0;

        // Transaction value: +5 if customer's average total > store median total
        $allTotals = \App\Models\Transaksi::pluck('total');
        $transactionValueDelta = 0;
        if ($allTotals->count() > 0) {
            $median = $allTotals->map(fn ($t) => (float) $t)->median();
            $avg = (float) \App\Models\Transaksi::where('id_pelanggan', $pelanggan->id_pelanggan)->avg('total');
            if ($avg > $median) {
                $transactionValueDelta = 5;
            }
        }

        // Active arrears: -10 for 1 late/due, -15 for >1
        $activeLateCount = \App\Models\JadwalAngsuran::whereHas('kontrakKredit', function ($q) use ($pelanggan) {
            $q->where('id_pelanggan', $pelanggan->id_pelanggan);
        })->whereIn('status', ['DUE', 'LATE'])->count();

        $activeArrearsDelta = 0;
        if ($activeLateCount > 1) {
            $activeArrearsDelta = -15;
        } elseif ($activeLateCount === 1) {
            $activeArrearsDelta = -10;
        }

        $total = $baseline
            + $accountAgeDelta
            + $installmentDelta
            + $shoppingFrequencyDelta
            + $transactionValueDelta
            + $activeArrearsDelta;

        // Clamp to 0..100
        $total = max(0, min(100, (int) round($total)));

        return [
            'baseline' => $baseline,
            'account_age' => $accountAgeDelta,
            'installment_history' => $installmentDelta,
            'shopping_frequency' => $shoppingFrequencyDelta,
            'transaction_value' => $transactionValueDelta,
            'active_arrears' => $activeArrearsDelta,
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
