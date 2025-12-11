<?php

namespace App\Services;

use App\GoodsInStatus;
use App\Models\GoodsIn;
use App\Models\GoodsInDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GoodsInService
{
    /**
     * Create a new PO (Purchase Order) request
     *
     * @param  string  $kasirId  The ID of the kasir creating the PO
     * @param  array  $items  Array of items with format: [['id_produk' => int, 'qty_request' => int], ...]
     * @return GoodsIn The created PO with details
     *
     * @throws \Exception If validation or creation fails
     */
    public function createPORequest(string $kasirId, array $items): GoodsIn
    {
        // Validate items array
        if (empty($items)) {
            throw new \InvalidArgumentException('Items array cannot be empty');
        }

        return DB::transaction(function () use ($kasirId, $items) {
            // Generate unique PO number
            $nomorPO = GoodsIn::generateNomorPO();

            // Create the main PO record
            $goodsIn = GoodsIn::create([
                'nomor_po' => $nomorPO,
                'status' => GoodsInStatus::Submitted->value,
                'tanggal_request' => now(),
                'id_kasir' => $kasirId,
            ]);

            // Create PO details for each item
            foreach ($items as $item) {
                if (! isset($item['id_produk']) || ! isset($item['qty_request'])) {
                    throw new \InvalidArgumentException('Each item must have id_produk and qty_request');
                }

                if ($item['qty_request'] <= 0) {
                    throw new \InvalidArgumentException('Quantity requested must be greater than 0');
                }

                GoodsInDetail::create([
                    'id_goods_in' => $goodsIn->id_goods_in,
                    'id_produk' => $item['id_produk'],
                    'qty_request' => $item['qty_request'],
                    'qty_received' => 0,
                ]);
            }

            // Load relationships for the response
            return $goodsIn->load(['details.produk', 'kasir']);
        });
    }

    /**
     * Approve a PO request
     *
     * @param  int  $poId  The ID of the PO to approve
     * @param  string  $adminId  The ID of the admin approving the PO
     * @param  string|null  $catatan  Optional approval notes
     * @return GoodsIn The updated PO
     *
     * @throws \Exception If PO not found or invalid status
     */
    public function approvePO(int $poId, string $adminId, ?string $catatan = null): GoodsIn
    {
        return DB::transaction(function () use ($poId, $adminId, $catatan) {
            $goodsIn = GoodsIn::findOrFail($poId);

            // Validate that PO is in submitted status
            if ($goodsIn->status !== GoodsInStatus::Submitted->value) {
                throw new \LogicException('Only POs with submitted status can be approved');
            }

            // Update the PO status to approved
            $goodsIn->update([
                'status' => GoodsInStatus::Approved->value,
                'tanggal_approval' => now(),
                'catatan_approval' => $catatan,
                'id_admin' => $adminId,
            ]);

            return $goodsIn->load(['details.produk', 'kasir', 'admin']);
        });
    }

    /**
     * Reject a PO request
     *
     * @param  int  $poId  The ID of the PO to reject
     * @param  string  $adminId  The ID of the admin rejecting the PO
     * @param  string|null  $catatan  Optional rejection notes (recommended)
     * @return GoodsIn The updated PO
     *
     * @throws \Exception If PO not found or invalid status
     */
    public function rejectPO(int $poId, string $adminId, ?string $catatan = null): GoodsIn
    {
        return DB::transaction(function () use ($poId, $adminId, $catatan) {
            $goodsIn = GoodsIn::findOrFail($poId);

            // Validate that PO is in submitted status
            if ($goodsIn->status !== GoodsInStatus::Submitted->value) {
                throw new \LogicException('Only POs with submitted status can be rejected');
            }

            // Update the PO status to rejected
            $goodsIn->update([
                'status' => GoodsInStatus::Rejected->value,
                'tanggal_approval' => now(),
                'catatan_approval' => $catatan,
                'id_admin' => $adminId,
            ]);

            return $goodsIn->load(['details.produk', 'kasir', 'admin']);
        });
    }

    /**
     * Get all pending POs (with submitted status)
     *
     * @return Collection Collection of pending POs with their details
     */
    public function getPendingPOs(): Collection
    {
        return GoodsIn::with(['details.produk', 'kasir'])
            ->where('status', GoodsInStatus::Submitted->value)
            ->orderBy('tanggal_request', 'asc')
            ->get();
    }

    /**
     * Get all POs created by a specific kasir
     *
     * @param  string  $kasirId  The ID of the kasir
     * @return Collection Collection of POs with their details
     */
    public function getPOsByKasir(string $kasirId): Collection
    {
        return GoodsIn::with(['details.produk', 'admin'])
            ->where('id_kasir', $kasirId)
            ->orderBy('tanggal_request', 'desc')
            ->get();
    }
}
