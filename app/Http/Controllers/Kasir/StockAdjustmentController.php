<?php

namespace App\Http\Controllers\Kasir;

use App\AdjustmentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStockAdjustmentRequest;
use App\Models\Produk;
use App\Models\StockAdjustment;
use App\Services\StockAdjustmentService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class StockAdjustmentController extends Controller
{
    public function __construct(private StockAdjustmentService $stockAdjustmentService) {}

    /**
     * Display a listing of stock adjustments.
     */
    public function index(): Response
    {
        $adjustments = StockAdjustment::with(['produk', 'pengguna'])
            ->orderBy('tanggal_adjustment', 'desc')
            ->get();

        return Inertia::render('Kasir/StockAdjustment/Index', [
            'adjustments' => $adjustments,
        ]);
    }

    /**
     * Show the form for creating a new stock adjustment.
     */
    public function create(): Response
    {
        $produk = Produk::with('kategori')
            ->orderBy('nama')
            ->get();

        $adjustmentTypes = collect(AdjustmentType::cases())->map(function ($type) {
            return [
                'value' => $type->value,
                'label' => $type->label(),
            ];
        });

        return Inertia::render('Kasir/StockAdjustment/Create', [
            'produk' => $produk,
            'adjustmentTypes' => $adjustmentTypes,
        ]);
    }

    /**
     * Store a newly created stock adjustment in storage.
     */
    public function store(CreateStockAdjustmentRequest $request)
    {
        $kasirId = Auth::user()->id_pengguna;
        $validated = $request->validated();

        try {
            $adjustment = $this->stockAdjustmentService->createAdjustment(
                $validated['id_produk'],
                $validated['tipe'],
                $validated['qty_adjustment'],
                $validated['alasan'] ?? '',
                $kasirId
            );

            return redirect()
                ->route('kasir.stock-adjustment.index')
                ->with('success', 'Penyesuaian stok berhasil dibuat untuk produk: '.$adjustment->produk->nama);
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal membuat penyesuaian stok: '.$e->getMessage()])
                ->withInput();
        }
    }
}
