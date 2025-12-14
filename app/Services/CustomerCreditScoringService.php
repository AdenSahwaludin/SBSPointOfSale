<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Models\Transaksi;
use Carbon\Carbon;

/**
 * Comprehensive customer credit scoring and credit limit management service.
 * 
 * Handles:
 * - Auto-increase trust score based on transaction history
 * - Auto-update credit limit based on transaction activity
 * - Auto-adjustment of saldo kredit (available credit balance)
 * - Account age bonus calculation
 */
class CustomerCreditScoringService
{
    /**
     * Auto-increase credit limit and saldo kredit after a transaction.
     * 
     * Rules:
     * - Credit limit increases based on transaction frequency and total value
     * - Saldo kredit (available balance) increases when credit is fully paid
     * - Increase happens only if customer is eligible (trust_score >= 70)
     * 
     * @param Pelanggan $pelanggan
     * @return array ['limit_increased' => bool, 'new_limit' => int, 'increase_amount' => int]
     */
    public static function autoIncreaseCredit(Pelanggan $pelanggan): array
    {
        $originalLimit = (int) $pelanggan->credit_limit;
        
        // Only eligible customers can get credit limit increase
        if ($pelanggan->trust_score < 70) {
            return [
                'limit_increased' => false,
                'new_limit' => $originalLimit,
                'increase_amount' => 0,
                'reason' => 'Trust score below 70. Not eligible for credit increase.',
            ];
        }

        // Get transaction count and total value for last 6 months
        $sixMonthsAgo = Carbon::now()->subMonths(6);
        $transactions = Transaksi::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('tanggal', '>=', $sixMonthsAgo)
            ->where(function ($query) {
                $query->where('status_pembayaran', 'LUNAS')
                    ->orWhere('jenis_transaksi', 'TUNAI');
            })
            ->get();

        $transactionCount = $transactions->count();
        $totalSpending = (int) $transactions->sum('total');

        // Recalculate base limit using existing calculation
        $calculation = CreditLimitService::calculateCreditLimit($pelanggan);
        $newCalculatedLimit = $calculation['credit_limit'];

        // Determine increase amount based on transaction frequency
        $increaseAmount = self::calculateCreditIncreaseAmount(
            $transactionCount,
            $totalSpending,
            $pelanggan->trust_score
        );

        // New limit = calculated limit + activity bonus (but don't decrease existing limit)
        $newLimit = max($newCalculatedLimit + $increaseAmount, $originalLimit);

        // Update if there's an increase
        if ($newLimit > $originalLimit) {
            $pelanggan->forceFill(['credit_limit' => $newLimit])->save();
            
            return [
                'limit_increased' => true,
                'new_limit' => $newLimit,
                'increase_amount' => $newLimit - $originalLimit,
                'reason' => "Activity bonus: {$transactionCount} transactions in 6 months (Total: Rp ".number_format($totalSpending, 0, ',', '.').")",
            ];
        }

        return [
            'limit_increased' => false,
            'new_limit' => $originalLimit,
            'increase_amount' => 0,
            'reason' => 'Not enough transaction activity for increase.',
        ];
    }

    /**
     * Calculate credit increase amount based on transaction activity.
     * 
     * Rules:
     * - 1-2 transactions: +0 (no bonus)
     * - 3-5 transactions: +10% of total spending in 6 months
     * - 6-10 transactions: +15% of total spending in 6 months
     * - 11+ transactions: +20% of total spending in 6 months
     * 
     * Additional: 
     * - Trust score 75-89: 1.2x multiplier
     * - Trust score 90+: 1.5x multiplier
     * 
     * @param int $transactionCount
     * @param int $totalSpending
     * @param int $trustScore
     * @return int
     */
    private static function calculateCreditIncreaseAmount(
        int $transactionCount,
        int $totalSpending,
        int $trustScore
    ): int {
        // No bonus for insufficient transaction count
        if ($transactionCount < 3) {
            return 0;
        }

        // Base calculation: percentage of total spending in 6 months
        $frequencyPercentage = match (true) {
            $transactionCount >= 11 => 0.20,  // 20% bonus
            $transactionCount >= 6 => 0.15,   // 15% bonus
            $transactionCount >= 3 => 0.10,   // 10% bonus
            default => 0.0,
        };

        $bonusAmount = (int) ($totalSpending * $frequencyPercentage);

        // Apply trust score bonus multiplier
        $trustMultiplier = match (true) {
            $trustScore >= 90 => 1.5,   // 50% higher bonus
            $trustScore >= 75 => 1.2,   // 20% higher bonus
            default => 1.0,
        };

        $finalIncreaseAmount = (int) ($bonusAmount * $trustMultiplier);

        // Round to nearest thousand for cleaner numbers
        return (int) (round($finalIncreaseAmount / 1000) * 1000);
    }

    /**
     * Restore saldo kredit when a credit transaction is fully paid.
     * 
     * When a customer pays off their credit, their available credit balance
     * (saldo_kredit) is restored, allowing them to make new credit transactions.
     * 
     * @param Pelanggan $pelanggan
     * @param int $paidAmount
     * @return array ['saldo_restored' => bool, 'new_saldo' => int]
     */
    public static function restoreCreditBalance(Pelanggan $pelanggan, int $paidAmount): array
    {
        $originalSaldo = (int) $pelanggan->saldo_kredit;
        
        // Restore saldo (reduce outstanding debt)
        $newSaldo = max(0, $originalSaldo - $paidAmount);

        if ($newSaldo !== $originalSaldo) {
            $pelanggan->forceFill(['saldo_kredit' => $newSaldo])->save();
            
            return [
                'saldo_restored' => true,
                'original_saldo' => $originalSaldo,
                'new_saldo' => $newSaldo,
                'amount_restored' => $originalSaldo - $newSaldo,
            ];
        }

        return [
            'saldo_restored' => false,
            'original_saldo' => $originalSaldo,
            'new_saldo' => $newSaldo,
            'amount_restored' => 0,
        ];
    }

    /**
     * Get comprehensive credit score breakdown for customer.
     * 
     * Includes:
     * - Current trust score and factors
     * - Credit limit information
     * - Available saldo kredit
     * - Recent transaction activity
     * - Eligibility status
     * 
     * @param Pelanggan $pelanggan
     * @return array
     */
    public static function getDetailedScoreBreakdown(Pelanggan $pelanggan): array
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6);
        $transactionsLast6Months = Transaksi::where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('tanggal', '>=', $sixMonthsAgo)
            ->where(function ($query) {
                $query->where('status_pembayaran', 'LUNAS')
                    ->orWhere('jenis_transaksi', 'TUNAI');
            })
            ->count();

        $calculation = CreditLimitService::calculateCreditLimit($pelanggan);
        $eligibility = CreditLimitService::checkEligibility($pelanggan->trust_score);

        return [
            'trust_score' => $pelanggan->trust_score,
            'credit_limit' => (int) $pelanggan->credit_limit,
            'saldo_kredit' => (int) $pelanggan->saldo_kredit,
            'available_credit' => (int) $pelanggan->credit_limit - (int) $pelanggan->saldo_kredit,
            'transactions_6_months' => $transactionsLast6Months,
            'membership_days' => $pelanggan->created_at->diffInDays(Carbon::now()),
            'status_kredit' => $pelanggan->status_kredit,
            'eligibility' => $eligibility,
            'limit_breakdown' => $calculation['breakdown'],
            'trust_factor' => $calculation['trust_factor'],
        ];
    }

    /**
     * Check if customer is eligible for credit transaction.
     * 
     * @param Pelanggan $pelanggan
     * @return array ['eligible' => bool, 'available_limit' => int, 'message' => string]
     */
    public static function isCreditEligible(Pelanggan $pelanggan): array
    {
        $eligibility = CreditLimitService::checkEligibility($pelanggan->trust_score);

        if (! $eligibility['eligible']) {
            return [
                'eligible' => false,
                'available_limit' => 0,
                'message' => $eligibility['message'],
            ];
        }

        $availableCredit = (int) $pelanggan->credit_limit - (int) $pelanggan->saldo_kredit;

        if ($availableCredit <= 0) {
            return [
                'eligible' => false,
                'available_limit' => 0,
                'message' => 'Credit limit habis. Silakan lunasi cicilan terlebih dahulu.',
            ];
        }

        return [
            'eligible' => true,
            'available_limit' => $availableCredit,
            'message' => 'Pelanggan memenuhi syarat untuk kredit.',
        ];
    }
}
