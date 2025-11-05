<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Services\TrustScoreService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrustScoreController extends Controller
{
    /**
     * Display trust score breakdown for a customer
     */
    public function show(string $id): Response
    {
        $pelanggan = Pelanggan::findOrFail($id);

        // Get detailed breakdown
        $breakdown = TrustScoreService::calculateFullScore($pelanggan);

        return Inertia::render('Admin/TrustScore/Show', [
            'pelanggan' => $pelanggan,
            'breakdown' => $breakdown,
        ]);
    }

    /**
     * Manually recalculate trust score for a customer
     */
    public function recalculate(string $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        $oldScore = $pelanggan->trust_score;
        $breakdown = TrustScoreService::updateTrustScore($pelanggan, true);
        $newScore = $breakdown['total'];

        return back()->with('success', "Trust score updated: {$oldScore} → {$newScore}");
    }

    /**
     * Batch recalculate all customers
     */
    public function recalculateAll()
    {
        $customers = Pelanggan::all();
        $updated = 0;

        foreach ($customers as $pelanggan) {
            $oldScore = $pelanggan->trust_score;
            $newScore = TrustScoreService::updateTrustScore($pelanggan);

            if ($oldScore != $newScore) {
                $updated++;
            }
        }

        return back()->with('success', "Recalculated trust scores for {$customers->count()} customers. {$updated} scores updated.");
    }
}
