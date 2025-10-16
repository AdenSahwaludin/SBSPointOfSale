<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Produk;
use App\Models\Pelanggan;
use App\Models\KontrakKredit;
use App\Models\JadwalAngsuran;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): Response
    {
        // Periode waktu
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        
        // ========================================
        // RINGKASAN PENJUALAN
        // ========================================
        $salesStats = [
            'today' => [
                'total' => Transaksi::whereDate('tanggal', $today)
                    ->where('status_pembayaran', 'LUNAS')
                    ->sum('total'),
                'count' => Transaksi::whereDate('tanggal', $today)->count(),
            ],
            'this_month' => [
                'total' => Transaksi::where('tanggal', '>=', $thisMonth)
                    ->where('status_pembayaran', 'LUNAS')
                    ->sum('total'),
                'count' => Transaksi::where('tanggal', '>=', $thisMonth)->count(),
            ],
            'last_month' => [
                'total' => Transaksi::whereBetween('tanggal', [
                    $lastMonth,
                    $lastMonth->copy()->endOfMonth()
                ])->where('status_pembayaran', 'LUNAS')->sum('total'),
            ],
        ];

        // Hitung pertumbuhan
        $salesStats['growth'] = $salesStats['last_month']['total'] > 0
            ? (($salesStats['this_month']['total'] - $salesStats['last_month']['total']) / $salesStats['last_month']['total']) * 100
            : 0;

        // ========================================
        // STATUS STOK PRODUK
        // ========================================
        $stockStats = [
            'total_products' => Produk::count(),
            'low_stock' => Produk::whereBetween('stok', [1, 10])->count(),
            'out_of_stock' => Produk::where('stok', '<=', 0)->count(),
            'total_items' => Produk::sum('stok'),
        ];

        // ========================================
        // CICILAN PINTAR & KREDIT
        // ========================================
        $creditStats = [
            'active_contracts' => KontrakKredit::where('status', 'AKTIF')->count(),
            'total_receivable' => JadwalAngsuran::whereIn('status', ['DUE', 'LATE'])
                ->sum(DB::raw('jumlah_tagihan - jumlah_dibayar')),
            'overdue_count' => JadwalAngsuran::where('status', 'LATE')->count(),
            'this_month_collection' => JadwalAngsuran::where('status', 'PAID')
                ->where('paid_at', '>=', $thisMonth)
                ->sum('jumlah_dibayar'),
        ];

        // ========================================
        // PELANGGAN
        // ========================================
        $customerStats = [
            'total' => Pelanggan::count(),
            'active' => Pelanggan::where('aktif', 1)->count(),
            'high_trust' => Pelanggan::where('trust_score', '>=', 70)->count(),
            'new_this_month' => Pelanggan::where('created_at', '>=', $thisMonth)->count(),
        ];

        // ========================================
        // TOP PRODUCTS (Bulan ini)
        // ========================================
        $topProducts = DB::table('transaksi_detail')
            ->join('transaksi', 'transaksi_detail.nomor_transaksi', '=', 'transaksi.nomor_transaksi')
            ->join('produk', 'transaksi_detail.id_produk', '=', 'produk.id_produk')
            ->where('transaksi.tanggal', '>=', $thisMonth)
            ->where('transaksi.status_pembayaran', 'LUNAS')
            ->select(
                'produk.nama',
                DB::raw('SUM(transaksi_detail.jumlah) as total_terjual'),
                DB::raw('SUM(transaksi_detail.subtotal) as total_revenue')
            )
            ->groupBy('produk.id_produk', 'produk.nama')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        // ========================================
        // TRANSAKSI TERBARU
        // ========================================
        $recentTransactions = Transaksi::with(['pelanggan', 'kasir'])
            ->orderBy('tanggal', 'desc')
            ->limit(10)
            ->get();

        // ========================================
        // ANGSURAN JATUH TEMPO MINGGU INI
        // ========================================
        $upcomingPayments = JadwalAngsuran::with(['kontrakKredit.pelanggan'])
            ->whereIn('status', ['DUE', 'LATE'])
            ->whereBetween('jatuh_tempo', [
                $today,
                $today->copy()->addDays(7)
            ])
            ->orderBy('jatuh_tempo', 'asc')
            ->limit(10)
            ->get();

        // ========================================
        // PRODUK STOK RENDAH
        // ========================================
        $lowStockProducts = Produk::with('kategori')
            ->where('stok', '>', 0)
            ->where('stok', '<=', 10)
            ->orderBy('stok', 'asc')
            ->limit(10)
            ->get();

        // ========================================
        // CHART DATA: Penjualan 7 hari terakhir
        // ========================================
        $salesChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $salesChart[] = [
                'date' => $date->format('d M'),
                'amount' => Transaksi::whereDate('tanggal', $date)
                    ->where('status_pembayaran', 'LUNAS')
                    ->sum('total'),
                'count' => Transaksi::whereDate('tanggal', $date)->count(),
            ];
        }

        return Inertia::render('Admin/Dashboard', [
            'salesStats' => $salesStats,
            'stockStats' => $stockStats,
            'creditStats' => $creditStats,
            'customerStats' => $customerStats,
            'topProducts' => $topProducts,
            'recentTransactions' => $recentTransactions,
            'upcomingPayments' => $upcomingPayments,
            'lowStockProducts' => $lowStockProducts,
            'salesChart' => $salesChart,
        ]);
    }
}
