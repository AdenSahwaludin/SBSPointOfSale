<?php

use App\Models\Pelanggan;
use App\Services\TrustScoreService;
use Carbon\Carbon;

it('increases trust score by +10 for accounts older than 30 days', function () {
    // Create a customer with created_at 31 days ago and baseline trust_score 50
    $date = Carbon::now()->subDays(31);

    $pelanggan = Pelanggan::create([
        'id_pelanggan' => Pelanggan::generateNextId(),
        'nama' => 'Test Pelanggan',
        'trust_score' => 50,
    ]);

    // created_at is not mass-assignable in the model; set it explicitly then save
    $pelanggan->created_at = $date;
    $pelanggan->updated_at = $date;
    $pelanggan->save();

    // sanity-check: created_at is set and at least 31 days old
    // compute diff from created_at to now to avoid sign issues
    $ageDays = $pelanggan->created_at->diffInDays(now());
    expect($ageDays)->toBeGreaterThanOrEqual(31);

    // Apply account-age rule (pass a fresh instance to avoid stale in-memory state)
    TrustScoreService::applyAccountAgeRule($pelanggan->fresh());

    // Reload and assert the trust score was updated by the service
    $pelanggan->refresh();
    expect($pelanggan->trust_score)->toBe(60);
});
