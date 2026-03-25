<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        // Sales Trend (Daily for the selected range)
        $salesTrend = (clone $statsQuery)
            ->where('status_pembayaran', 'LUNAS')
            ->selectRaw('DATE(tanggal) as date, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Payment Methods (LUNAS only)
        $paymentMethods = (clone $statsQuery)
            ->where('status_pembayaran', 'LUNAS')
            ->selectRaw('metode_bayar as method, COUNT(*) as count, SUM(total) as total')
            ->groupBy('metode_bayar')
            ->get();

        // Status Distribution
        $statusDistribution = (clone $statsQuery)
            ->selectRaw('status_pembayaran as status, COUNT(*) as count')
            ->groupBy('status_pembayaran')
            ->get();

        // Top Products
        // Get top 5 products sold in this period
        $topProductsQuery = \App\Models\TransaksiDetail::query()
            ->join('transaksi', 'transaksi.nomor_transaksi', '=', 'transaksi_detail.nomor_transaksi')
            ->join('produk', 'produk.id_produk', '=', 'transaksi_detail.id_produk')
            ->where('transaksi.status_pembayaran', 'LUNAS');

        if ($startDate) {
            $topProductsQuery->whereDate('transaksi.tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $topProductsQuery->whereDate('transaksi.tanggal', '<=', $endDate);
        }

        $topProducts = $topProductsQuery
            ->selectRaw('produk.nama, SUM(transaksi_detail.jumlah) as total_qty, SUM(transaksi_detail.subtotal) as total_revenue')
            ->groupBy('produk.id_produk', 'produk.nama')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return Inertia::render('Admin/Reports/Index', [
            'transaksi' => $transaksi,
            'stats' => $stats,
            'salesTrend' => $salesTrend,
            'paymentMethods' => $paymentMethods,
            'statusDistribution' => $statusDistribution,
            'topProducts' => $topProducts,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Export summary report as PDF
     */
    public function exportPdf(Request $request): HttpResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $status = $request->get('status', 'all');

        // Query for stats
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

        // Sales Trend
        $salesTrend = (clone $statsQuery)
            ->where('status_pembayaran', 'LUNAS')
            ->selectRaw('DATE(tanggal) as date, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Payment Methods
        $paymentMethods = (clone $statsQuery)
            ->where('status_pembayaran', 'LUNAS')
            ->selectRaw('metode_bayar as method, COUNT(*) as count, SUM(total) as total')
            ->groupBy('metode_bayar')
            ->get();

        // Status Distribution
        $statusDistribution = (clone $statsQuery)
            ->selectRaw('status_pembayaran as status, COUNT(*) as count')
            ->groupBy('status_pembayaran')
            ->get();

        // Top Products
        $topProductsQuery = \App\Models\TransaksiDetail::query()
            ->join('transaksi', 'transaksi.nomor_transaksi', '=', 'transaksi_detail.nomor_transaksi')
            ->join('produk', 'produk.id_produk', '=', 'transaksi_detail.id_produk')
            ->where('transaksi.status_pembayaran', 'LUNAS');

        if ($startDate) {
            $topProductsQuery->whereDate('transaksi.tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $topProductsQuery->whereDate('transaksi.tanggal', '<=', $endDate);
        }

        $topProducts = $topProductsQuery
            ->selectRaw('produk.nama, SUM(transaksi_detail.jumlah) as total_qty, SUM(transaksi_detail.subtotal) as total_revenue')
            ->groupBy('produk.id_produk', 'produk.nama')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Get transactions for detail table
        $query = Transaksi::with(['pelanggan', 'kasir'])->orderBy('tanggal', 'desc');
        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }
        if ($status && $status !== 'all') {
            $query->where('status_pembayaran', $status);
        }
        $transaksi = $query->get();

        $pdf = Pdf::loadView('exports.reports.summary', [
            'stats' => $stats,
            'salesTrend' => $salesTrend,
            'paymentMethods' => $paymentMethods,
            'statusDistribution' => $statusDistribution,
            'topProducts' => $topProducts,
            'transaksi' => $transaksi,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
        ])
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10);

        return $pdf->download('laporan-ringkasan-'.date('Y-m-d').'.pdf');
    }

    /**
     * Export summary report as CSV
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $status = $request->get('status', 'all');

        // Query for stats
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

        // Sales Trend
        $salesTrend = (clone $statsQuery)
            ->where('status_pembayaran', 'LUNAS')
            ->selectRaw('DATE(tanggal) as date, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Payment Methods
        $paymentMethods = (clone $statsQuery)
            ->where('status_pembayaran', 'LUNAS')
            ->selectRaw('metode_bayar as method, COUNT(*) as count, SUM(total) as total')
            ->groupBy('metode_bayar')
            ->get();

        // Status Distribution
        $statusDistribution = (clone $statsQuery)
            ->selectRaw('status_pembayaran as status, COUNT(*) as count')
            ->groupBy('status_pembayaran')
            ->get();

        // Top Products
        $topProductsQuery = \App\Models\TransaksiDetail::query()
            ->join('transaksi', 'transaksi.nomor_transaksi', '=', 'transaksi_detail.nomor_transaksi')
            ->join('produk', 'produk.id_produk', '=', 'transaksi_detail.id_produk')
            ->where('transaksi.status_pembayaran', 'LUNAS');

        if ($startDate) {
            $topProductsQuery->whereDate('transaksi.tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $topProductsQuery->whereDate('transaksi.tanggal', '<=', $endDate);
        }

        $topProducts = $topProductsQuery
            ->selectRaw('produk.nama, SUM(transaksi_detail.jumlah) as total_qty, SUM(transaksi_detail.subtotal) as total_revenue')
            ->groupBy('produk.id_produk', 'produk.nama')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Get transactions
        $query = Transaksi::with(['pelanggan', 'kasir'])->orderBy('tanggal', 'desc');
        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }
        if ($status && $status !== 'all') {
            $query->where('status_pembayaran', $status);
        }
        $transaksi = $query->get();

        $filename = 'laporan-ringkasan-'.date('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($stats, $salesTrend, $paymentMethods, $statusDistribution, $topProducts, $transaksi, $startDate, $endDate) {
            $output = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // ==== HEADER INFO ====
            fputcsv($output, ['LAPORAN RINGKASAN SARI BUMI SAKTI']);
            fputcsv($output, ['Periode: '.$startDate.' hingga '.$endDate]);
            fputcsv($output, ['Generated: '.date('d F Y H:i:s')]);
            fputcsv($output, []);

            // ==== STATISTIK UTAMA ====
            fputcsv($output, ['STATISTIK UTAMA']);
            fputcsv($output, ['Metrik', 'Nilai']);
            fputcsv($output, ['Total Transaksi', $stats['total_transaksi']]);
            fputcsv($output, ['Total Pendapatan', 'Rp '.number_format($stats['total_pendapatan'], 0, ',', '.')]);
            fputcsv($output, ['Status Lunas', $stats['total_lunas']]);
            fputcsv($output, ['Status Menunggu', $stats['total_menunggu']]);
            fputcsv($output, ['Status Batal', $stats['total_batal']]);
            fputcsv($output, []);

            // ==== TREND PENJUALAN HARIAN ====
            fputcsv($output, ['TREND PENJUALAN HARIAN']);
            fputcsv($output, ['Tanggal', 'Jumlah Transaksi', 'Total Pendapatan']);
            foreach ($salesTrend as $trend) {
                fputcsv($output, [
                    $trend->date,
                    $trend->count,
                    'Rp '.number_format($trend->revenue, 0, ',', '.'),
                ]);
            }
            fputcsv($output, []);

            // ==== METODE PEMBAYARAN ====
            fputcsv($output, ['METODE PEMBAYARAN']);
            fputcsv($output, ['Metode', 'Jumlah Transaksi', 'Total']);
            foreach ($paymentMethods as $method) {
                fputcsv($output, [
                    $method->method,
                    $method->count,
                    'Rp '.number_format($method->total, 0, ',', '.'),
                ]);
            }
            fputcsv($output, []);

            // ==== DISTRIBUSI STATUS ====
            fputcsv($output, ['DISTRIBUSI STATUS TRANSAKSI']);
            fputcsv($output, ['Status', 'Jumlah']);
            foreach ($statusDistribution as $dist) {
                fputcsv($output, [
                    $dist->status,
                    $dist->count,
                ]);
            }
            fputcsv($output, []);

            // ==== TOP 5 PRODUK TERLARIS ====
            fputcsv($output, ['TOP 5 PRODUK TERLARIS']);
            fputcsv($output, ['Produk', 'Jumlah Terjual', 'Total Penjualan']);
            foreach ($topProducts as $product) {
                fputcsv($output, [
                    $product->nama,
                    $product->total_qty,
                    'Rp '.number_format($product->total_revenue, 0, ',', '.'),
                ]);
            }
            fputcsv($output, []);

            // ==== DETAIL TRANSAKSI ====
            fputcsv($output, ['DETAIL TRANSAKSI']);
            fputcsv($output, ['No. Transaksi', 'Tanggal', 'Pelanggan', 'Kasir', 'Total', 'Status']);
            foreach ($transaksi as $t) {
                fputcsv($output, [
                    $t->nomor_transaksi,
                    date('d/m/Y H:i', strtotime($t->tanggal)),
                    $t->pelanggan ? $t->pelanggan->nama : '-',
                    $t->kasir ? $t->kasir->nama : '-',
                    'Rp '.number_format($t->total, 0, ',', '.'),
                    $t->status_pembayaran,
                ]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
