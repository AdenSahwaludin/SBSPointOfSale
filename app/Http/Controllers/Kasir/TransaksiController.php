<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransaksiController extends Controller
{
    /**
     * Display a listing of all transactions (Riwayat Transaksi)
     */
    public function index(Request $request): Response
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $status = $request->get('status');
        $metodeBayar = $request->get('metode_bayar');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Transaksi::with(['pelanggan', 'kasir', 'detail', 'kontrakKredit.jadwalAngsuran'])
            ->orderBy('tanggal', 'desc');

        // Search by nomor_transaksi, pelanggan nama
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                    ->orWhereHas('pelanggan', function ($q2) use ($search) {
                        $q2->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($status && $status !== 'all') {
            $query->where('status_pembayaran', $status);
        }

        // Filter by metode bayar
        if ($metodeBayar && $metodeBayar !== 'all') {
            $query->where('metode_bayar', $metodeBayar);
        }

        // Filter by date range
        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        $transaksi = $query->paginate($perPage)->withQueryString();

        // Calculate stats
        $stats = [
            'total_transaksi' => Transaksi::count(),
            'total_lunas' => Transaksi::where('status_pembayaran', 'LUNAS')->count(),
            'total_menunggu' => Transaksi::where('status_pembayaran', 'MENUNGGU')->count(),
            'total_batal' => Transaksi::where('status_pembayaran', 'BATAL')->count(),
            'total_nilai' => Transaksi::where('status_pembayaran', 'LUNAS')->sum('total'),
        ];

        return Inertia::render('Kasir/Transactions/Index', [
            'transaksi' => $transaksi,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'metode_bayar' => $metodeBayar,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Display today's transactions
     */
    public function today(Request $request): Response
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $status = $request->get('status');
        $metodeBayar = $request->get('metode_bayar');

        $query = Transaksi::with(['pelanggan', 'kasir', 'detail', 'kontrakKredit.jadwalAngsuran'])
            ->whereDate('tanggal', Carbon::today())
            ->orderBy('tanggal', 'desc');

        // Search by nomor_transaksi, pelanggan nama
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                    ->orWhereHas('pelanggan', function ($q2) use ($search) {
                        $q2->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($status && $status !== 'all') {
            $query->where('status_pembayaran', $status);
        }

        // Filter by metode bayar
        if ($metodeBayar && $metodeBayar !== 'all') {
            $query->where('metode_bayar', $metodeBayar);
        }

        $transaksi = $query->paginate($perPage)->withQueryString();

        // Calculate today's stats
        $todayTransactions = Transaksi::whereDate('tanggal', Carbon::today());
        $stats = [
            'total_transaksi' => $todayTransactions->count(),
            'total_lunas' => $todayTransactions->where('status_pembayaran', 'LUNAS')->count(),
            'total_menunggu' => $todayTransactions->where('status_pembayaran', 'MENUNGGU')->count(),
            'total_batal' => $todayTransactions->where('status_pembayaran', 'BATAL')->count(),
            'total_nilai' => $todayTransactions->where('status_pembayaran', 'LUNAS')->sum('total'),
            'total_item_terjual' => $todayTransactions->where('status_pembayaran', 'LUNAS')->sum('total_item'),
        ];

        return Inertia::render('Kasir/Transactions/Today', [
            'transaksi' => $transaksi,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'metode_bayar' => $metodeBayar,
            ],
        ]);
    }

    /**
     * Display transaction detail
     */
    public function show(string $nomorTransaksi): Response
    {
        $transaksi = Transaksi::with(['pelanggan', 'kasir', 'detail.produk', 'kontrakKredit.jadwalAngsuran'])
            ->findOrFail($nomorTransaksi);

        return Inertia::render('Kasir/Transactions/Show', [
            'transaksi' => $transaksi,
        ]);
    }

    /**
     * Update transaction status
     */
    public function updateStatus(Request $request, string $nomorTransaksi)
    {
        $request->validate([
            'status_pembayaran' => 'required|in:LUNAS,MENUNGGU,BATAL',
        ]);

        $transaksi = Transaksi::findOrFail($nomorTransaksi);
        $previousStatus = $transaksi->status_pembayaran;
        $transaksi->status_pembayaran = $request->status_pembayaran;
        $transaksi->save(); // updated_at akan otomatis terupdate

        // Jika transaksi kredit dilunasi (dari non-LUNAS ke LUNAS), kembalikan kredit limit sesuai outstanding
        if ($transaksi->jenis_transaksi === Transaksi::JENIS_KREDIT && $previousStatus !== Transaksi::STATUS_LUNAS && $transaksi->status_pembayaran === Transaksi::STATUS_LUNAS) {
            $outstanding = max(0, (float) $transaksi->total - (float) $transaksi->dp);
            if ($outstanding > 0) {
                $pelanggan = \App\Models\Pelanggan::find($transaksi->id_pelanggan);
                if ($pelanggan) {
                    $pelanggan->credit_limit = ((float) $pelanggan->credit_limit) + $outstanding;
                    $pelanggan->save();
                }
            }
        }

        return back()->with('success', 'Status transaksi berhasil diperbarui');
    }
}
