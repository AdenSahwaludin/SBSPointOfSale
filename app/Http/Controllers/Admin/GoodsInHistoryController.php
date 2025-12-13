<?php

namespace App\Http\Controllers\Admin;

use App\Models\GoodsIn;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GoodsInHistoryController
{
    /**
     * Display a listing of all Purchase Orders with their final status
     */
    public function index(Request $request): Response
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $status = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = GoodsIn::with(['details.produk', 'kasir', 'receivedGoods'])
            ->whereIn('status', ['approved', 'rejected', 'partial_received', 'received'])
            ->orderBy('tanggal_request', 'desc');

        // Search by nomor_po or kasir nama
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_po', 'like', "%{$search}%")
                    ->orWhereHas('kasir', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Filter by date range
        if ($startDate) {
            $query->whereDate('tanggal_request', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('tanggal_request', '<=', $endDate);
        }

        $poHistory = $query->paginate($perPage)->withQueryString();

        // Get available statuses for filter
        $availableStatuses = GoodsIn::whereIn('status', ['APPROVED', 'REJECTED', 'PARTIAL_RECEIVED', 'RECEIVED', 'CANCELED'])
            ->distinct()
            ->pluck('status')
            ->toArray();

        return Inertia::render('Admin/GoodsInHistory/Index', [
            'poHistory' => $poHistory,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'availableStatuses' => $availableStatuses,
        ]);
    }

    /**
     * Display the specified PO history detail
     */
    public function show($id): Response
    {
        $goodsIn = GoodsIn::with(['details.produk', 'kasir', 'receivedGoods'])
            ->findOrFail($id);

        // Only show if status is not pending (not in approval queue)
        if ($goodsIn->status === 'draft') {
            abort(404, 'Purchase Order tidak ditemukan');
        }

        return Inertia::render('Admin/GoodsInHistory/Show', [
            'po' => $goodsIn,
        ]);
    }
}
