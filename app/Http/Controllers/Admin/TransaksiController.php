<?php

namespace App\Http\Controllers\Admin;

use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class TransaksiController extends Controller
{
    /**
     * Display a listing of all transactions
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

        return Inertia::render('Admin/Transactions/Index', [
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
     * Display a listing of credit transactions (jenis_transaksi = KREDIT)
     */
    public function creditTransactions(Request $request): Response
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $status = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Transaksi::with(['pelanggan', 'kasir', 'detail', 'kontrakKredit.jadwalAngsuran'])
            ->where('jenis_transaksi', Transaksi::JENIS_KREDIT)
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

        // Filter by date range
        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        $transaksi = $query->paginate($perPage)->withQueryString();

        // Calculate stats for credit transactions only
        $creditStats = [
            'total_transaksi' => Transaksi::where('jenis_transaksi', Transaksi::JENIS_KREDIT)->count(),
            'total_lunas' => Transaksi::where('jenis_transaksi', Transaksi::JENIS_KREDIT)
                ->where('status_pembayaran', 'LUNAS')->count(),
            'total_menunggu' => Transaksi::where('jenis_transaksi', Transaksi::JENIS_KREDIT)
                ->where('status_pembayaran', 'MENUNGGU')->count(),
            'total_batal' => Transaksi::where('jenis_transaksi', Transaksi::JENIS_KREDIT)
                ->where('status_pembayaran', 'BATAL')->count(),
            'total_nilai' => Transaksi::where('jenis_transaksi', Transaksi::JENIS_KREDIT)
                ->where('status_pembayaran', 'LUNAS')->sum('total'),
        ];

        return Inertia::render('Admin/Transactions/CreditIndex', [
            'transaksi' => $transaksi,
            'stats' => $creditStats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Display daily report
     */
    public function dailyReport(Request $request): Response
    {
        $tanggal = $request->get('tanggal') ? Carbon::parse($request->get('tanggal')) : Carbon::today();

        // Get all transactions for the selected day
        $dayTransactions = Transaksi::with(['pelanggan', 'kasir'])
            ->whereDate('tanggal', $tanggal)
            ->orderBy('tanggal', 'desc')
            ->get();

        // Calculate stats
        $stats = [
            'total_transaksi' => $dayTransactions->count(),
            'total_lunas' => $dayTransactions->where('status_pembayaran', 'LUNAS')->count(),
            'total_menunggu' => $dayTransactions->where('status_pembayaran', 'MENUNGGU')->count(),
            'total_batal' => $dayTransactions->where('status_pembayaran', 'BATAL')->count(),
            'total_nilai' => $dayTransactions->where('status_pembayaran', 'LUNAS')->sum('total'),
            'total_item' => $dayTransactions->where('status_pembayaran', 'LUNAS')->sum('total_item'),
        ];

        // Get payment method breakdown
        $paymentMethods = $dayTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy('metode_bayar')
            ->map(function ($group) {
                return [
                    'metode' => $group->first()->metode_bayar,
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->values();

        // Get hourly breakdown for chart
        $hourlyRaw = $dayTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy(function ($transaction) {
                return (int) Carbon::parse($transaction->tanggal)->format('H');
            })
            ->map(function ($group) {
                return [
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            });

        // Fill missing hours with 0 and filter
        $hourlyData = [];
        for ($h = 0; $h < 24; $h++) {
            $data = $hourlyRaw->get($h);
            if ($data && ($data['total'] > 0 || $data['count'] > 0)) {
                $hourlyData[] = [
                    'jam' => str_pad($h, 2, '0', STR_PAD_LEFT).':00',
                    'total' => $data['total'],
                    'count' => $data['count'],
                ];
            }
        }

        return Inertia::render('Admin/Transactions/DailyReport', [
            'tanggal' => $tanggal->format('Y-m-d'),
            'tanggal_display' => $tanggal->translatedFormat('l, d F Y'),
            'stats' => $stats,
            'paymentMethods' => $paymentMethods,
            'hourlyData' => $hourlyData,
            'transaksi' => $dayTransactions,
        ]);
    }

    /**
     * Display monthly report
     */
    public function monthlyReport(Request $request): Response
    {
        $bulan = $request->get('bulan') ? (int) $request->get('bulan') : now()->month;
        $tahun = $request->get('tahun') ? (int) $request->get('tahun') : now()->year;

        // Get all transactions for the selected month
        $monthTransactions = Transaksi::with(['pelanggan', 'kasir'])
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('tanggal', 'desc')
            ->get();

        // Calculate stats
        $stats = [
            'total_transaksi' => $monthTransactions->count(),
            'total_lunas' => $monthTransactions->where('status_pembayaran', 'LUNAS')->count(),
            'total_menunggu' => $monthTransactions->where('status_pembayaran', 'MENUNGGU')->count(),
            'total_batal' => $monthTransactions->where('status_pembayaran', 'BATAL')->count(),
            'total_nilai' => $monthTransactions->where('status_pembayaran', 'LUNAS')->sum('total'),
            'total_item' => $monthTransactions->where('status_pembayaran', 'LUNAS')->sum('total_item'),
        ];

        // Get payment method breakdown
        $paymentMethods = $monthTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy('metode_bayar')
            ->map(function ($group) {
                return [
                    'metode' => $group->first()->metode_bayar,
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->values();

        // Get daily breakdown for chart
        $dailyData = $monthTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy(function ($transaction) {
                return Carbon::parse($transaction->tanggal)->format('Y-m-d');
            })
            ->map(function ($group, $key) {
                return [
                    'tanggal' => $key,
                    'hari' => Carbon::parse($key)->translatedFormat('d M'),
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->sortBy('tanggal');

        // Get top kasir
        $topKasir = $monthTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy('id_kasir')
            ->map(function ($group) {
                $kasir = $group->first()->kasir;

                return [
                    'nama' => $kasir?->nama ?? 'Unknown',
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->sortByDesc('total')
            ->take(5);

        // Get top customers
        $topPelanggan = $monthTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy('id_pelanggan')
            ->map(function ($group) {
                $pelanggan = $group->first()->pelanggan;

                return [
                    'nama' => $pelanggan?->nama ?? 'Umum',
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->sortByDesc('total')
            ->take(5);

        $bulanDisplay = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');

        return Inertia::render('Admin/Transactions/MonthlyReport', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'bulan_display' => $bulanDisplay,
            'stats' => $stats,
            'paymentMethods' => $paymentMethods,
            'dailyData' => $dailyData,
            'topKasir' => $topKasir,
            'topPelanggan' => $topPelanggan,
        ]);
    }

    /**
     * Display weekly report
     */
    public function weeklyReport(Request $request): Response
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->startOfWeek();
        $endDate = $startDate->copy()->endOfWeek();

        // Get all transactions for the selected week
        $weekTransactions = Transaksi::with(['pelanggan', 'kasir'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal', 'desc')
            ->get();

        // Calculate stats
        $stats = [
            'total_transaksi' => $weekTransactions->count(),
            'total_lunas' => $weekTransactions->where('status_pembayaran', 'LUNAS')->count(),
            'total_menunggu' => $weekTransactions->where('status_pembayaran', 'MENUNGGU')->count(),
            'total_batal' => $weekTransactions->where('status_pembayaran', 'BATAL')->count(),
            'total_nilai' => $weekTransactions->where('status_pembayaran', 'LUNAS')->sum('total'),
            'total_item' => $weekTransactions->where('status_pembayaran', 'LUNAS')->sum('total_item'),
        ];

        // Get payment method breakdown
        $paymentMethods = $weekTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy('metode_bayar')
            ->map(function ($group) {
                return [
                    'metode' => $group->first()->metode_bayar,
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->values();

        // Get daily breakdown for chart
        $dailyData = $weekTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy(function ($transaction) {
                return Carbon::parse($transaction->tanggal)->format('Y-m-d');
            })
            ->map(function ($group, $key) {
                return [
                    'tanggal' => $key,
                    'hari' => Carbon::parse($key)->translatedFormat('l'),
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->sortBy('tanggal');

        // Get top kasir
        $topKasir = $weekTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy('id_kasir')
            ->map(function ($group) {
                $kasir = $group->first()->kasir;

                return [
                    'nama' => $kasir?->nama ?? 'Unknown',
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->sortByDesc('total')
            ->take(5);

        // Get top customers
        $topPelanggan = $weekTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy('id_pelanggan')
            ->map(function ($group) {
                $pelanggan = $group->first()->pelanggan;

                return [
                    'nama' => $pelanggan?->nama ?? 'Umum',
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->sortByDesc('total')
            ->take(5);

        $weekDisplay = $startDate->translatedFormat('d M').' - '.$endDate->translatedFormat('d M Y');

        return Inertia::render('Admin/Transactions/WeeklyReport', [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'week_display' => $weekDisplay,
            'stats' => $stats,
            'paymentMethods' => $paymentMethods,
            'dailyData' => $dailyData,
            'topKasir' => $topKasir,
            'topPelanggan' => $topPelanggan,
            'transaksi' => $weekTransactions,
        ]);
    }

    /**
     * Display transaction detail
     */
    public function show(string $nomorTransaksi): Response
    {
        $transaksi = Transaksi::with(['pelanggan', 'kasir', 'detail.produk', 'kontrakKredit.jadwalAngsuran'])
            ->findOrFail($nomorTransaksi);

        return Inertia::render('Admin/Transactions/Show', [
            'transaksi' => $transaksi,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
