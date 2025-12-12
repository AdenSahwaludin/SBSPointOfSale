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

            // Create the main PO record with draft status to allow item management
            $goodsIn = GoodsIn::create([
                'nomor_po' => $nomorPO,
                'status' => GoodsInStatus::Draft->value,
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

    /**
     * Add item to GoodsIn (PO)
     *
     * @param  GoodsIn  $goodsIn  The GoodsIn instance
     * @param  int  $id_produk  Product ID
     * @param  int  $qty_request  Quantity requested
     * @return GoodsInDetail The created detail
     *
     * @throws \LogicException If PO is not in draft status
     * @throws \InvalidArgumentException If product already exists in this PO
     */
    public function addItemToGoodsIn(GoodsIn $goodsIn, int $id_produk, int $qty_request): GoodsInDetail
    {
        // Only allow adding if PO status is 'draft'
        if ($goodsIn->status !== GoodsInStatus::Draft->value) {
            throw new \LogicException('Hanya PO dengan status draft yang dapat diubah.');
        }

        // Check if produk already exists in this PO
        $existing = $goodsIn->details()->where('id_produk', $id_produk)->first();
        if ($existing) {
            $produk = \App\Models\Produk::findOrFail($id_produk);
            throw new \InvalidArgumentException("Produk {$produk->nama} sudah ada di PO ini. Ubah qty-nya jika ingin menambah.");
        }

        return DB::transaction(function () use ($goodsIn, $id_produk, $qty_request) {
            return GoodsInDetail::create([
                'id_goods_in' => $goodsIn->id_goods_in,
                'id_produk' => $id_produk,
                'qty_request' => $qty_request,
                'qty_received' => 0,
            ]);
        });
    }

    /**
     * Update item quantity in GoodsIn
     *
     * @param  GoodsInDetail  $detail  The detail to update
     * @param  int  $qty_request  New quantity
     * @return GoodsInDetail The updated detail
     *
     * @throws \LogicException If PO is not in draft status
     */
    public function updateItemQty(GoodsInDetail $detail, int $qty_request): GoodsInDetail
    {
        $goodsIn = $detail->goodsIn;

        // Only allow updating if PO status is 'draft'
        if ($goodsIn->status !== GoodsInStatus::Draft->value) {
            throw new \LogicException('Hanya PO dengan status draft yang dapat diubah.');
        }

        return DB::transaction(function () use ($detail, $qty_request) {
            $detail->update(['qty_request' => $qty_request]);

            return $detail;
        });
    }

    /**
     * Remove item from GoodsIn
     *
     * @param  GoodsInDetail  $detail  The detail to remove
     * @return bool True if successfully removed
     *
     * @throws \LogicException If PO is not in draft status
     */
    public function removeItemFromGoodsIn(GoodsInDetail $detail): bool
    {
        $goodsIn = $detail->goodsIn;

        // Only allow removing if PO status is 'draft'
        if ($goodsIn->status !== GoodsInStatus::Draft->value) {
            throw new \LogicException('Hanya PO dengan status draft yang dapat diubah.');
        }

        return DB::transaction(function () use ($detail) {
            return $detail->delete();
        });
    }

    /**
     * Submit GoodsIn (PO) for approval
     *
     * @param  GoodsIn  $goodsIn  The GoodsIn to submit
     * @return GoodsIn The updated GoodsIn
     *
     * @throws \LogicException If PO is not in draft status or has no items
     */
    public function submitGoodsIn(GoodsIn $goodsIn): GoodsIn
    {
        // Check if PO has items
        $itemCount = $goodsIn->details()->count();
        if ($itemCount === 0) {
            throw new \LogicException('PO harus memiliki minimal 1 item untuk diajukan.');
        }

        return DB::transaction(function () use ($goodsIn) {
            $goodsIn->update([
                'status' => GoodsInStatus::Submitted->value,
            ]);

            return $goodsIn->load(['details.produk', 'kasir']);
        });
    }
}
