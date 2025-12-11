<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveRejectPORequest;
use App\Services\GoodsInService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class GoodsInApprovalController extends Controller
{
    public function __construct(private GoodsInService $goodsInService) {}

    /**
     * Display a listing of pending POs.
     */
    public function index(): Response
    {
        $pendingPOs = $this->goodsInService->getPendingPOs();

        return Inertia::render('Admin/GoodsInApproval/Index', [
            'pendingPOs' => $pendingPOs,
        ]);
    }

    /**
     * Display the specified PO for approval.
     */
    public function show($id): Response
    {
        $goodsIn = \App\Models\GoodsIn::with(['details.produk', 'kasir'])
            ->findOrFail($id);

        return Inertia::render('Admin/GoodsInApproval/Show', [
            'goodsIn' => $goodsIn,
        ]);
    }

    /**
     * Approve the specified PO.
     */
    public function approve($id, ApproveRejectPORequest $request)
    {
        $adminId = Auth::user()->id_pengguna;

        try {
            $goodsIn = $this->goodsInService->approvePO(
                $id,
                $adminId,
                $request->validated()['catatan'] ?? null
            );

            return redirect()
                ->route('admin.goods-in-approval.index')
                ->with('success', 'Purchase Order '.$goodsIn->nomor_po.' berhasil disetujui.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal menyetujui Purchase Order: '.$e->getMessage()]);
        }
    }

    /**
     * Reject the specified PO.
     */
    public function reject($id, ApproveRejectPORequest $request)
    {
        $adminId = Auth::user()->id_pengguna;

        try {
            $goodsIn = $this->goodsInService->rejectPO(
                $id,
                $adminId,
                $request->validated()['catatan'] ?? null
            );

            return redirect()
                ->route('admin.goods-in-approval.index')
                ->with('success', 'Purchase Order '.$goodsIn->nomor_po.' berhasil ditolak.');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Gagal menolak Purchase Order: '.$e->getMessage()]);
        }
    }
}
