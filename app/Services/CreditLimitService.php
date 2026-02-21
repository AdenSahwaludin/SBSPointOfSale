<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Transaksi;
use Carbon\Carbon;

class CreditLimitService
{
    /**
     * Calculate credit limit for a customer based on trust score and transaction history.
     *
     * Formula from brief/Catatan_Limit.txt:
     * 1. Calculate limit_base (max of 3 methods):
     *    - 50% of largest transaction
     *    - 50% of average of top 3 transactions
     *    - 30% of total spending in last 6 months
     *
     * 2. Apply trust score factor:
     *    - TS < 50  → 0.0× (rejected)
     *    - TS 50-59 → 0.7×
     *    - TS 60-74 → 1.0×
     *    - TS 75-89 → 1.3×
     *    - TS ≥ 90  → 1.5×
     *
     * 3. Final: credit_limit = limit_base × factor (rounded to thousands)
     *
     * @return array ['limit_base' => int, 'trust_factor' => float, 'credit_limit' => int, 'breakdown' => array]
     */
    public static function calculateCreditLimit(Pelanggan $pelanggan): array
    {
        // Get completed transactions (LUNAS or fully paid KREDIT)
        $transactions = Transaksi::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where(function ($query) {
                $query->where('status_pembayaran', 'LUNAS')
                    ->orWhere(function ($q) {
                        $q->where('jenis_transaksi', 'KREDIT')
                            ->whereHas('kontrakKredit.jadwalAngsuran', function ($sq) {
                                $sq->where('status', 'PAID');
                            });
                    });
            })
            ->orderBy('total', 'desc')
            ->get();

        // If no transaction history, return 0
        if ($transactions->isEmpty()) {
            return [
                'limit_base' => 0,
                'trust_factor' => 0,
                'credit_limit' => 0,
                'breakdown' => [
                    'method_1_half_largest' => 0,
                    'method_2_avg_top3' => 0,
                    'method_3_six_months' => 0,
                    'selected_base' => 0,
                ],
            ];
        }

        // Method 1: 50% of largest transaction
        $largestTransaction = $transactions->first()->total ?? 0;
        $method1 = (int) ($largestTransaction * 0.5);

        // Method 2: 50% of average of top 3 transactions
        $top3 = $transactions->take(3);
        $avgTop3 = $top3->avg('total') ?? 0;
        $method2 = (int) ($avgTop3 * 0.5);

        // Method 3: 30% of total spending in last 6 months
        $sixMonthsAgo = Carbon::now()->subMonths(6);
        $totalLast6Months = Transaksi::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('tanggal', '>=', $sixMonthsAgo)
            ->sum('total');
        $method3 = (int) ($totalLast6Months * 0.3);

        // Select the largest value as base
        $limitBase = max($method1, $method2, $method3);

        // Determine trust score factor
        $trustScore = $pelanggan->trust_score;
        $trustFactor = self::getTrustScoreFactor($trustScore);

        // Calculate final credit limit (rounded to nearest thousand)
        $creditLimit = (int) (round(($limitBase * $trustFactor) / 1000) * 1000);

        return [
            'limit_base' => $limitBase,
            'trust_factor' => $trustFactor,
            'credit_limit' => $creditLimit,
            'breakdown' => [
                'method_1_half_largest' => $method1,
                'method_2_avg_top3' => $method2,
                'method_3_six_months' => $method3,
                'selected_base' => $limitBase,
            ],
        ];
    }

    /**
     * Get trust score multiplier factor.
     */
    private static function getTrustScoreFactor(int $trustScore): float
    {
        if ($trustScore < 50) {
            return 0.0; // Rejected
        } elseif ($trustScore >= 50 && $trustScore <= 59) {
            return 0.7;
        } elseif ($trustScore >= 60 && $trustScore <= 74) {
            return 1.0;
        } elseif ($trustScore >= 75 && $trustScore <= 89) {
            return 1.3;
        } else { // >= 90
            return 1.5;
        }
    }

    /**
     * Update customer's credit limit based on current trust score and transaction history.
     *
     * @param  bool  $returnBreakdown  Return full breakdown if true
     * @return array|int
     */
    public static function updateCreditLimit(Pelanggan $pelanggan, bool $returnBreakdown = false)
    {
        $calculation = self::calculateCreditLimit($pelanggan);

        // Only update if changed
        if ((int) $pelanggan->credit_limit !== $calculation['credit_limit']) {
            $pelanggan->forceFill(['credit_limit' => $calculation['credit_limit']])->save();
        }

        return $returnBreakdown ? $calculation : $calculation['credit_limit'];
    }

    /**
     * Determine credit eligibility based on trust score.
     *
     * Screening Cicilan Pintar tiers:
     * - TS < 50  → REJECTED (cannot apply for credit)
     * - TS 50-70 → MANUAL_REVIEW (requires DP minimum 20%)
     * - TS >= 71 → APPROVED (credit approved)
     *
     * @return array ['eligible' => bool, 'status' => string, 'message' => string]
     */
    public static function checkEligibility(int $trustScore): array
    {
        if ($trustScore < 50) {
            return [
                'eligible' => false,
                'status' => 'REJECTED',
                'message' => 'Trust score terlalu rendah. Tidak memenuhi syarat cicilan.',
            ];
        } elseif ($trustScore >= 50 && $trustScore <= 70) {
            return [
                'eligible' => true,
                'status' => 'MANUAL_REVIEW',
                'message' => 'Memerlukan peninjauan manual. Pertimbangkan untuk meminta DP lebih besar (minimal 20%).',
            ];
        } else { // >= 71
            return [
                'eligible' => true,
                'status' => 'APPROVED',
                'message' => 'Layak untuk cicilan. Proses dapat dilanjutkan.',
            ];
        }
    }
}
