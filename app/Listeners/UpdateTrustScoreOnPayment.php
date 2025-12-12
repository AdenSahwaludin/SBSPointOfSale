<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Services\CreditLimitService;
use App\Services\TrustScoreService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateTrustScoreOnPayment implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PaymentReceived $event): void
    {
        $pembayaran = $event->pembayaran;

        // Get the customer from payment
        $pelanggan = $pembayaran->pelanggan;

        if (! $pelanggan) {
            Log::warning("Payment {$pembayaran->id_pembayaran} has no associated customer");

            return;
        }

        try {
            // Recalculate trust score
            $oldScore = $pelanggan->trust_score;
            $oldLimit = $pelanggan->credit_limit;

            $newScore = TrustScoreService::updateTrustScore($pelanggan);

            // Recalculate credit limit after trust score update
            $pelanggan->refresh(); // Ensure we have the latest trust score
            $newLimit = CreditLimitService::updateCreditLimit($pelanggan);

            Log::info("Trust score and credit limit updated for {$pelanggan->nama}", [
                'customer_id' => $pelanggan->id_pelanggan,
                'old_score' => $oldScore,
                'new_score' => $newScore,
                'old_limit' => $oldLimit,
                'new_limit' => $newLimit,
                'payment_id' => $pembayaran->id_pembayaran,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update trust score/credit limit for customer {$pelanggan->id_pelanggan}", [
                'error' => $e->getMessage(),
                'payment_id' => $pembayaran->id_pembayaran,
            ]);
        }
    }
}
