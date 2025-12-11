<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGoodsInRequest;
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
    public function show($id): Response
    {
        $goodsIn = \App\Models\GoodsIn::with(['details.produk', 'kasir', 'admin'])
            ->findOrFail($id);

        return Inertia::render('Kasir/GoodsIn/Show', [
            'goodsIn' => $goodsIn,
        ]);
    }
}
