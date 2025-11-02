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
        if (!$pelanggan->created_at) {
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
        $newScore = max((int)$pelanggan->trust_score, $minScore);
        $newScore = min($newScore, 100);

        if ($newScore !== (int)$pelanggan->trust_score) {
            // Use forceFill to ensure the attribute is set regardless of mass-assignment rules
            $pelanggan->forceFill(['trust_score' => $newScore])->save();
        }
    }
}
