<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGoodsInRequest;
use App\Http\Requests\RecordGoodsReceivedRequest;
use App\Http\Requests\StoreGoodsInItemRequest;
use App\Models\GoodsIn;
use App\Models\GoodsInDetail;
use App\Models\Produk;
use App\Services\GoodsInService;
use App\Services\InventoryReportService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class GoodsInController extends Controller
{
    public function __construct(
        private GoodsInService $goodsInService,
        private InventoryReportService $inventoryReportService
    ) {}

    /**
     * Display a listing of POs created by current kasir.
     */
    public function index(): Response
    {
        $kasirId = Auth::user()->id_pengguna;
        $pos = $this->goodsInService->getPOsByKasir($kasirId);

        return Inertia::render('Kasir/GoodsIn/Index', [
            'pos' => $pos,
        ]);
    }

    /**
     * Show the form for creating a new PO.
     */
    public function create(): Response
    {
        $productsBelowROP = $this->inventoryReportService->getProductsBelowROP();

        return Inertia::render('Kasir/GoodsIn/Create', [
            'productsBelowROP' => $productsBelowROP,
        ]);
    }

    /**
     * Store a newly created PO in storage.
     */
    public function store(CreateGoodsInRequest $request)
    {
        $kasirId = Auth::user()->id_pengguna;

        try {
            $goodsIn = $this->goodsInService->createPORequest(
                $kasirId,
                $request->validated()['items']
            );

            return redirect()
                ->route('kasir.goods-in.show', $goodsIn->id_goods_in)
                ->with('success', 'Purchase Order berhasil dibuat dengan nomor: '.$goodsIn->nomor_po);
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal membuat Purchase Order: '.$e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified PO.
     */
    public function show(GoodsIn $goodsIn): Response
    {
        $goodsIn->load(['details.produk', 'kasir', 'admin']);

        // Get available products (not already in this PO)
        $addedProductIds = $goodsIn->details()->pluck('id_produk')->toArray();
        $availableProducts = Produk::whereNotIn('id_produk', $addedProductIds)
            ->orderBy('nama')
            ->get();

        return Inertia::render('Kasir/GoodsIn/Show', [
            'goodsIn' => $goodsIn,
            'availableProducts' => $availableProducts,
        ]);
    }

    /**
     * Add item to PO.
     */
    public function addItem(StoreGoodsInItemRequest $request, GoodsIn $goodsIn)
    {
        try {
            $validated = $request->validated();
            $this->goodsInService->addItemToGoodsIn(
                $goodsIn,
                $validated['id_produk'],
                $validated['qty_request']
            );

            return redirect()
                ->route('kasir.goods-in.show', $goodsIn->id_goods_in)
                ->with('success', 'Item berhasil ditambahkan ke PO.');
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        } catch (\LogicException $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal menambahkan item: '.$e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Update item quantity in PO.
     */
    public function updateItem(StoreGoodsInItemRequest $request, GoodsIn $goodsIn, $id_detail)
    {
        try {
            $detail = GoodsInDetail::where('id_goods_in_detail', $id_detail)
                ->where('id_goods_in', $goodsIn->id_goods_in)
                ->firstOrFail();

            $validated = $request->validated();
            $this->goodsInService->updateItemQty($detail, $validated['qty_request']);

            return redirect()
                ->route('kasir.goods-in.show', $goodsIn->id_goods_in)
                ->with('success', 'Kuantitas item berhasil diperbarui.');
        } catch (\LogicException $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal memperbarui item: '.$e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove item from PO.
     */
    public function removeItem(GoodsIn $goodsIn, $id_detail)
    {
        try {
            $detail = GoodsInDetail::where('id_goods_in_detail', $id_detail)
                ->where('id_goods_in', $goodsIn->id_goods_in)
                ->firstOrFail();

            $this->goodsInService->removeItemFromGoodsIn($detail);

            return redirect()
                ->route('kasir.goods-in.show', $goodsIn->id_goods_in)
                ->with('success', 'Item berhasil dihapus dari PO.');
        } catch (\LogicException $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal menghapus item: '.$e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Submit PO for approval.
     */
    public function submit(GoodsIn $goodsIn)
    {
        try {
            $this->goodsInService->submitGoodsIn($goodsIn);

            return redirect()
                ->route('kasir.goods-in.show', $goodsIn->id_goods_in)
                ->with('success', 'PO berhasil diajukan untuk persetujuan.');
        } catch (\LogicException $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal mengajukan PO: '.$e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Delete PO (only if in draft status).
     */
    public function destroy(GoodsIn $goodsIn)
    {
        try {
            // Only allow deleting draft POs
            if ($goodsIn->status !== 'draft') {
                return back()
                    ->withErrors(['error' => 'Hanya PO dengan status draft yang dapat dihapus.']);
            }

            // Delete all related details first
            $goodsIn->details()->delete();

            // Delete the PO itself
            $goodsIn->delete();

            return redirect()
                ->route('kasir.goods-in.index')
                ->with('success', 'PO berhasil dihapus.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal menghapus PO: '.$e->getMessage()]);
        }
    }

    /**
     * Show approved POs for receiving goods.
     */
    public function receivingIndex(): Response
    {
        $kasirId = Auth::user()->id_pengguna;

        // Get approved POs with received goods count
        $approvedPOs = $this->goodsInService->getApprovedPOsForReceiving()->toArray();

        return Inertia::render('Kasir/GoodsIn/ReceivingIndex', [
            'approvedPOs' => $approvedPOs,
        ]);
    }

    /**
     * Show form to record received goods for a specific PO.
     */
    public function receivingShow(GoodsIn $goodsIn): Response
    {
        // Only show if PO is approved
        if ($goodsIn->status !== 'approved') {
            abort(403, 'Hanya PO dengan status approved yang dapat dicatat barangnya.');
        }

        $goodsIn->load(['details.produk', 'kasir', 'receivedGoods']);

        // Get items that haven't been fully received yet
        $pendingItems = $goodsIn->details()->with('produk')->get()->map(function ($detail) {
            return [
                'id_goods_in_detail' => $detail->id_goods_in_detail,
                'id_produk' => $detail->id_produk,
                'nama_produk' => $detail->produk->nama,
                'sku' => $detail->produk->sku,
                'qty_request' => $detail->qty_request,
                'qty_received' => $detail->qty_received,
                'qty_remaining' => $detail->qty_request - $detail->qty_received,
            ];
        });

        // Get already received goods
        $receivedGoods = $this->goodsInService->getReceivedGoodsByPO($goodsIn);

        return Inertia::render('Kasir/GoodsIn/ReceivingShow', [
            'goodsIn' => $goodsIn,
            'pendingItems' => $pendingItems,
            'receivedGoods' => $receivedGoods,
        ]);
    }

    /**
     * Record received goods for approved PO.
     */
    public function recordReceived(RecordGoodsReceivedRequest $request, GoodsIn $goodsIn)
    {
        try {
            // Only allow recording if PO is approved
            if ($goodsIn->status !== 'approved') {
                return back()
                    ->withErrors(['error' => 'Hanya PO dengan status approved yang dapat dicatat barangnya.']);
            }

            $kasirId = Auth::user()->id_pengguna;
            $validated = $request->validated();

            $this->goodsInService->recordReceivedGoods($goodsIn, $validated['items'], $kasirId);

            return redirect()
                ->route('kasir.goods-in.receiving-show', $goodsIn->id_goods_in)
                ->with('success', 'Barang berhasil dicatat.');
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        } catch (\LogicException $e) {
            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal mencatat barang: '.$e->getMessage()])
                ->withInput();
        }
    }
}
