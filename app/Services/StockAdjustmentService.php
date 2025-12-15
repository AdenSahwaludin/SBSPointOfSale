<?php

namespace App\Services;

use App\AdjustmentType;
use App\Models\Produk;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;

class StockAdjustmentService
{
    /**
     * Create a stock adjustment and update the product stock
     *
     * @param  int  $produkId  The ID of the product to adjust
     * @param  string  $tipe  The adjustment type (must match AdjustmentType enum values)
     * @param  int  $qtyAdjustment  The quantity to adjust (positive number)
     * @param  string  $alasan  The reason for the adjustment
     * @param  string  $penggunaId  The ID of the user making the adjustment
     * @return StockAdjustment The created stock adjustment record
     *
     * @throws \Exception If product not found or validation fails
     */
    public function createAdjustment(
        int $produkId,
        string $tipe,
        int $qtyAdjustment,
        string $alasan,
        string $penggunaId
    ): StockAdjustment {
        // Validate adjustment type
        $adjustmentType = AdjustmentType::tryFrom($tipe);
        if (! $adjustmentType) {
            throw new \InvalidArgumentException("Invalid adjustment type: {$tipe}");
        }

        // Validate quantity
        if ($qtyAdjustment <= 0) {
            throw new \InvalidArgumentException('Adjustment quantity must be greater than 0');
        }

        return DB::transaction(function () use ($produkId, $adjustmentType, $qtyAdjustment, $alasan, $penggunaId) {
            // Get the product
            $produk = Produk::findOrFail($produkId);

            // Calculate the actual change to stock
            $stockChange = $this->calculateStockChange($adjustmentType, $qtyAdjustment);

            // Check if negative adjustment would result in negative stock
            if ($stockChange < 0 && $produk->stok + $stockChange < 0) {
                throw new \LogicException(
                    "Cannot adjust stock: Current stock ({$produk->stok}) is insufficient for adjustment ({$qtyAdjustment})"
                );
            }

            // Update product stock
            $produk->increment('stok', $stockChange);

            // Create the stock adjustment record
            $adjustment = StockAdjustment::create([
                'id_produk' => $produkId,
                'tipe' => $adjustmentType->value,
                'jumlah_penyesuaian' => $qtyAdjustment,
                'alasan' => $alasan,
                'id_pengguna' => $penggunaId,
                'tanggal_adjustment' => now(),
            ]);

            // Load relationships for the response
            return $adjustment->load(['produk', 'pengguna']);
        });
    }

    /**
     * Calculate the stock change based on adjustment type
     *
     * @param  AdjustmentType  $type  The adjustment type
     * @param  int  $quantity  The quantity to adjust
     * @return int The calculated stock change (positive or negative)
     */
    private function calculateStockChange(AdjustmentType $type, int $quantity): int
    {
        // Positive adjustments: add to stock
        if ($type->isPositive()) {
            return $quantity;
        }

        // Negative adjustments: subtract from stock
        if ($type->isNegative()) {
            return -$quantity;
        }

        // For PenyesuaianOpname, the quantity can be positive or negative
        // based on the actual stock count vs system stock
        if ($type === AdjustmentType::PenyesuaianOpname) {
            return $quantity;
        }

        throw new \LogicException("Unhandled adjustment type: {$type->value}");
    }
}
