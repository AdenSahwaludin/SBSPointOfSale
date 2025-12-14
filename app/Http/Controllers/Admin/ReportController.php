<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $perPage = $request->get('per_page', 15);
        // Default to current month if no date provided
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $status = $request->get('status', 'all');

        $query = Transaksi::with(['pelanggan', 'kasir'])
            ->orderBy('tanggal', 'desc');

        // Filter by date range
        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        // Filter by status
        if ($status && $status !== 'all') {
            $query->where('status_pembayaran', $status);
        }

        $transaksi = $query->paginate($perPage)->withQueryString();

        // Calculate stats based on filtered data
        $statsQuery = Transaksi::query();
        if ($startDate) {
            $statsQuery->whereDate('tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $statsQuery->whereDate('tanggal', '<=', $endDate);
        }
        if ($status && $status !== 'all') {
            $statsQuery->where('status_pembayaran', $status);
        }

        $stats = [
            'total_transaksi' => (clone $statsQuery)->count(),
            'total_pendapatan' => (clone $statsQuery)->where('status_pembayaran', 'LUNAS')->sum('total'),
            'total_lunas' => (clone $statsQuery)->where('status_pembayaran', 'LUNAS')->count(),
            'total_menunggu' => (clone $statsQuery)->where('status_pembayaran', 'MENUNGGU')->count(),
            'total_batal' => (clone $statsQuery)->where('status_pembayaran', 'BATAL')->count(),
        ];

        return Inertia::render('Admin/Reports/Index', [
            'transaksi' => $transaksi,
            'stats' => $stats,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
            ],
        ]);
    }
}
