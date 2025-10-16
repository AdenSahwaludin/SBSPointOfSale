<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Produk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
 public function index(): Response
 {
  $today = Carbon::today();
  $kasirId = Auth::id();

  // ========================================
  // PERFORMA HARI INI
  // ========================================
  $todayStats = [
   'total_sales' => Transaksi::whereDate('tanggal', $today)
    ->where('id_kasir', $kasirId)
    ->where('status_pembayaran', 'LUNAS')
    ->sum('total'),
   'total_transactions' => Transaksi::whereDate('tanggal', $today)
    ->where('id_kasir', $kasirId)
    ->count(),
   'total_items_sold' => Transaksi::whereDate('tanggal', $today)
    ->where('id_kasir', $kasirId)
    ->where('status_pembayaran', 'LUNAS')
    ->sum('total_item'),
   'pending_transactions' => Transaksi::whereDate('tanggal', $today)
    ->where('id_kasir', $kasirId)
    ->where('status_pembayaran', 'MENUNGGU')
    ->count(),
  ];

  // ========================================
  // BREAKDOWN METODE PEMBAYARAN HARI INI
  // ========================================
  $paymentMethods = Transaksi::whereDate('tanggal', $today)
   ->where('id_kasir', $kasirId)
   ->where('status_pembayaran', 'LUNAS')
   ->select('metode_bayar', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as amount'))
   ->groupBy('metode_bayar')
   ->get();

  // ========================================
  // TRANSAKSI TERAKHIR HARI INI
  // ========================================
  $recentTransactions = Transaksi::with(['pelanggan'])
   ->whereDate('tanggal', $today)
   ->where('id_kasir', $kasirId)
   ->orderBy('tanggal', 'desc')
   ->limit(10)
   ->get();

  // ========================================
  // PRODUK TERLARIS HARI INI
  // ========================================
  $topProducts = DB::table('transaksi_detail')
   ->join('transaksi', 'transaksi_detail.nomor_transaksi', '=', 'transaksi.nomor_transaksi')
   ->join('produk', 'transaksi_detail.id_produk', '=', 'produk.id_produk')
   ->whereDate('transaksi.tanggal', $today)
   ->where('transaksi.id_kasir', $kasirId)
   ->where('transaksi.status_pembayaran', 'LUNAS')
   ->select(
    'produk.nama',
    DB::raw('SUM(transaksi_detail.jumlah) as total_qty'),
    DB::raw('SUM(transaksi_detail.subtotal) as total_amount')
   )
   ->groupBy('produk.id_produk', 'produk.nama')
   ->orderByDesc('total_qty')
   ->limit(5)
   ->get();

  // ========================================
  // PERINGATAN STOK RENDAH
  // ========================================
  $lowStockAlerts = Produk::with('kategori')
   ->where('stok', '>', 0)
   ->where('stok', '<=', 10)
   ->orderBy('stok', 'asc')
   ->limit(5)
   ->get();

  // ========================================
  // CHART: Penjualan per Jam Hari Ini
  // ========================================
  $hourlySales = Transaksi::whereDate('tanggal', $today)
   ->where('id_kasir', $kasirId)
   ->where('status_pembayaran', 'LUNAS')
   ->select(
    DB::raw('HOUR(tanggal) as hour'),
    DB::raw('COUNT(*) as count'),
    DB::raw('SUM(total) as amount')
   )
   ->groupBy('hour')
   ->orderBy('hour')
   ->get()
   ->keyBy('hour');

  // Fill missing hours with 0
  $salesByHour = [];
  for ($h = 0; $h < 24; $h++) {
   $salesByHour[] = [
    'hour' => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00',
    'count' => $hourlySales->get($h)->count ?? 0,
    'amount' => $hourlySales->get($h)->amount ?? 0,
   ];
  }

  // ========================================
  // PERFORMA MINGGU INI vs MINGGU LALU
  // ========================================
  $thisWeekStart = Carbon::now()->startOfWeek();
  $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();

  $weekComparison = [
   'this_week' => Transaksi::where('tanggal', '>=', $thisWeekStart)
    ->where('id_kasir', $kasirId)
    ->where('status_pembayaran', 'LUNAS')
    ->sum('total'),
   'last_week' => Transaksi::whereBetween('tanggal', [
    $lastWeekStart,
    $lastWeekStart->copy()->endOfWeek()
   ])
    ->where('id_kasir', $kasirId)
    ->where('status_pembayaran', 'LUNAS')
    ->sum('total'),
  ];

  $weekComparison['growth'] = $weekComparison['last_week'] > 0
   ? (($weekComparison['this_week'] - $weekComparison['last_week']) / $weekComparison['last_week']) * 100
   : 0;

  return Inertia::render('Kasir/Dashboard', [
   'todayStats' => $todayStats,
   'paymentMethods' => $paymentMethods,
   'recentTransactions' => $recentTransactions,
   'topProducts' => $topProducts,
   'lowStockAlerts' => $lowStockAlerts,
   'salesByHour' => $salesByHour,
   'weekComparison' => $weekComparison,
  ]);
 }
}
