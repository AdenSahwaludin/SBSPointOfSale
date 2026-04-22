<?php

namespace App\Http\Controllers\Admin;

use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
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

        // Base query for stats (apply all filters EXCEPT status)
        $statsQuery = Transaksi::query();
        
        if ($search) {
            $statsQuery->where(function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                    ->orWhereHas('pelanggan', function ($q2) use ($search) {
                        $q2->where('nama', 'like', "%{$search}%");
                    });
            });
        }
        if ($metodeBayar && $metodeBayar !== 'all') {
            $statsQuery->where('metode_bayar', $metodeBayar);
        }
        if ($startDate) {
            $statsQuery->whereDate('tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $statsQuery->whereDate('tanggal', '<=', $endDate);
        }

        // Calculate stats
        $stats = [
            'total_transaksi' => (clone $statsQuery)->count(),
            'total_lunas' => (clone $statsQuery)->where('status_pembayaran', 'LUNAS')->count(),
            'total_menunggu' => (clone $statsQuery)->where('status_pembayaran', 'MENUNGGU')->count(),
            'total_batal' => (clone $statsQuery)->where('status_pembayaran', 'BATAL')->count(),
            'total_nilai' => (clone $statsQuery)->where('status_pembayaran', 'LUNAS')->sum('total'),
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

        // Base query for credit stats (apply all filters EXCEPT status)
        $creditStatsQuery = Transaksi::where('jenis_transaksi', Transaksi::JENIS_KREDIT);
        
        if ($search) {
            $creditStatsQuery->where(function ($q) use ($search) {
                $q->where('nomor_transaksi', 'like', "%{$search}%")
                    ->orWhereHas('pelanggan', function ($q2) use ($search) {
                        $q2->where('nama', 'like', "%{$search}%");
                    });
            });
        }
        if ($startDate) {
            $creditStatsQuery->whereDate('tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $creditStatsQuery->whereDate('tanggal', '<=', $endDate);
        }

        // Calculate stats for credit transactions only
        $creditStats = [
            'total_transaksi' => (clone $creditStatsQuery)->count(),
            'total_lunas' => (clone $creditStatsQuery)->where('status_pembayaran', 'LUNAS')->count(),
            'total_menunggu' => (clone $creditStatsQuery)->where('status_pembayaran', 'MENUNGGU')->count(),
            'total_batal' => (clone $creditStatsQuery)->where('status_pembayaran', 'BATAL')->count(),
            'total_nilai' => (clone $creditStatsQuery)->where('status_pembayaran', 'LUNAS')->sum('total'),
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

    /**
     * Export daily report as PDF
     */
    public function exportDailyPdf(Request $request): HttpResponse
    {
        $tanggal = $request->get('tanggal') ? Carbon::parse($request->get('tanggal')) : Carbon::today();

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

        $paymentMethods = $dayTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy('metode_bayar')
            ->map(function ($group) {
                return [
                    'metode' => $group->first()->metode_bayar,
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->toArray();

        $transaksi = $dayTransactions->map(function ($t) {
            return [
                'nomor_transaksi' => $t->nomor_transaksi,
                'tanggal' => $t->tanggal,
                'pelanggan' => ['nama' => $t->pelanggan ? $t->pelanggan->nama : '-'],
                'kasir' => ['nama' => $t->kasir ? $t->kasir->nama : '-'],
                'metode_bayar' => $t->metode_bayar,
                'total' => $t->total,
                'status_pembayaran' => $t->status_pembayaran,
            ];
        })->toArray();

        $pdf = Pdf::loadView('exports.reports.daily', [
            'tanggal' => $tanggal->format('Y-m-d'),
            'stats' => $stats,
            'paymentMethods' => $paymentMethods,
            'transaksi' => $transaksi,
        ])
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10);

        return $pdf->download('laporan-harian-'.$tanggal->format('Y-m-d').'.pdf');
    }

    /**
     * Export daily report as CSV
     */
    public function exportDailyCsv(Request $request): HttpResponse
    {
        $tanggal = $request->get('tanggal') ? Carbon::parse($request->get('tanggal')) : Carbon::today();

        $dayTransactions = Transaksi::with(['pelanggan', 'kasir'])
            ->whereDate('tanggal', $tanggal)
            ->orderBy('tanggal', 'desc')
            ->get();

        $filename = 'laporan-harian-'.$tanggal->format('Y-m-d').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $output = fopen('php://output', 'w');
        // BOM for Excel UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['Laporan Harian - '.$tanggal->translatedFormat('l, d F Y')]);
        fputcsv($output, ['']);

        // Stats
        fputcsv($output, ['STATISTIK']);
        fputcsv($output, [
            'Total Transaksi',
            'Total Lunas',
            'Total Menunggu',
            'Total Batal',
            'Total Nilai',
            'Total Item',
        ]);
        fputcsv($output, [
            $dayTransactions->count(),
            $dayTransactions->where('status_pembayaran', 'LUNAS')->count(),
            $dayTransactions->where('status_pembayaran', 'MENUNGGU')->count(),
            $dayTransactions->where('status_pembayaran', 'BATAL')->count(),
            $dayTransactions->where('status_pembayaran', 'LUNAS')->sum('total'),
            $dayTransactions->where('status_pembayaran', 'LUNAS')->sum('total_item'),
        ]);
        fputcsv($output, ['']);

        // Data
        fputcsv($output, [
            'No. Transaksi',
            'Jam',
            'Pelanggan',
            'Kasir',
            'Metode',
            'Total',
            'Status',
        ]);

        foreach ($dayTransactions as $t) {
            fputcsv($output, [
                $t->nomor_transaksi,
                date('H:i', strtotime($t->tanggal)),
                $t->pelanggan ? $t->pelanggan->nama : '-',
                $t->kasir ? $t->kasir->nama : '-',
                $t->metode_bayar,
                $t->total,
                $t->status_pembayaran,
            ]);
        }

        fputcsv($output, ['']);
        fputcsv($output, ['Generated at: '.date('d F Y H:i:s')]);

        fclose($output);

        return response()->streamDownload(function () {}, $filename, $headers);
    }

    /**
     * Export weekly report as PDF
     */
    public function exportWeeklyPdf(Request $request): HttpResponse
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::today()->startOfWeek();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : $startDate->copy()->endOfWeek();

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

        // Daily breakdown
        $dailyData = $weekTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy(function ($t) {
                return Carbon::parse($t->tanggal)->format('Y-m-d');
            })
            ->map(function ($group) {
                return [
                    'tanggal' => $group->first()->tanggal,
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->toArray();

        // Payment methods
        $paymentMethods = $weekTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy('metode_bayar')
            ->map(function ($group) {
                return [
                    'metode' => $group->first()->metode_bayar,
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->toArray();

        // Top kasir
        $topKasir = $weekTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy('id_kasir')
            ->map(function ($group) {
                return [
                    'nama' => $group->first()->kasir->nama ?? '-',
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values()
            ->toArray();

        // Top pelanggan
        $topPelanggan = $weekTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy('id_pelanggan')
            ->map(function ($group) {
                return [
                    'nama' => $group->first()->pelanggan->nama ?? '-',
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values()
            ->toArray();

        $pdf = Pdf::loadView('exports.reports.weekly', [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'stats' => $stats,
            'dailyData' => $dailyData,
            'paymentMethods' => $paymentMethods,
            'topKasir' => $topKasir,
            'topPelanggan' => $topPelanggan,
        ])
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10);

        return $pdf->download('laporan-mingguan-'.$startDate->format('Y-m-d').'.pdf');
    }

    /**
     * Export weekly report as CSV
     */
    public function exportWeeklyCsv(Request $request): HttpResponse
    {
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::today()->startOfWeek();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : $startDate->copy()->endOfWeek();

        $weekTransactions = Transaksi::with(['pelanggan', 'kasir'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $filename = 'laporan-mingguan-'.$startDate->format('Y-m-d').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['Laporan Mingguan - '.$startDate->format('d F Y').' hingga '.$endDate->format('d F Y')]);
        fputcsv($output, ['']);

        // Stats
        fputcsv($output, ['STATISTIK']);
        fputcsv($output, [
            'Total Transaksi',
            'Total Lunas',
            'Total Menunggu',
            'Total Batal',
            'Total Nilai',
            'Total Item',
        ]);
        fputcsv($output, [
            $weekTransactions->count(),
            $weekTransactions->where('status_pembayaran', 'LUNAS')->count(),
            $weekTransactions->where('status_pembayaran', 'MENUNGGU')->count(),
            $weekTransactions->where('status_pembayaran', 'BATAL')->count(),
            $weekTransactions->where('status_pembayaran', 'LUNAS')->sum('total'),
            $weekTransactions->where('status_pembayaran', 'LUNAS')->sum('total_item'),
        ]);
        fputcsv($output, ['']);
        fputcsv($output, ['Generated at: '.date('d F Y H:i:s')]);

        fclose($output);

        return response()->streamDownload(function () {}, $filename, $headers);
    }

    /**
     * Export monthly report as PDF
     */
    public function exportMonthlyPdf(Request $request): HttpResponse
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $startDate = Carbon::createFromDate($tahun, $bulan, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $monthTransactions = Transaksi::with(['pelanggan', 'kasir'])
            ->whereBetween('tanggal', [$startDate, $endDate])
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

        // Daily breakdown
        $dailyData = $monthTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy(function ($t) {
                return Carbon::parse($t->tanggal)->format('Y-m-d');
            })
            ->map(function ($group) {
                return [
                    'tanggal' => $group->first()->tanggal,
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->toArray();

        // Payment methods
        $paymentMethods = $monthTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy('metode_bayar')
            ->map(function ($group) {
                return [
                    'metode' => $group->first()->metode_bayar,
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->toArray();

        // Top kasir
        $topKasir = $monthTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy('id_kasir')
            ->map(function ($group) {
                return [
                    'nama' => $group->first()->kasir->nama ?? '-',
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values()
            ->toArray();

        // Top pelanggan
        $topPelanggan = $monthTransactions->where('status_pembayaran', 'LUNAS')
            ->groupBy('id_pelanggan')
            ->map(function ($group) {
                return [
                    'nama' => $group->first()->pelanggan->nama ?? '-',
                    'total' => $group->sum('total'),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values()
            ->toArray();

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $bulan_display = $months[$bulan].' '.$tahun;

        $pdf = Pdf::loadView('exports.reports.monthly', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'bulan_display' => $bulan_display,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'stats' => $stats,
            'dailyData' => $dailyData,
            'paymentMethods' => $paymentMethods,
            'topKasir' => $topKasir,
            'topPelanggan' => $topPelanggan,
        ])
            ->setPaper('a4')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10);

        return $pdf->download('laporan-bulanan-'.$startDate->format('Y-m-d').'.pdf');
    }

    /**
     * Export monthly report as CSV
     */
    public function exportMonthlyCsv(Request $request): HttpResponse
    {
        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $startDate = Carbon::createFromDate($tahun, $bulan, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $monthTransactions = Transaksi::with(['pelanggan', 'kasir'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $bulan_display = $months[$bulan].' '.$tahun;
        $filename = 'laporan-bulanan-'.$startDate->format('Y-m-d').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['Laporan Bulanan - '.$bulan_display]);
        fputcsv($output, ['']);

        // Stats
        fputcsv($output, ['STATISTIK']);
        fputcsv($output, [
            'Total Transaksi',
            'Total Lunas',
            'Total Menunggu',
            'Total Batal',
            'Total Nilai',
            'Total Item',
        ]);
        fputcsv($output, [
            $monthTransactions->count(),
            $monthTransactions->where('status_pembayaran', 'LUNAS')->count(),
            $monthTransactions->where('status_pembayaran', 'MENUNGGU')->count(),
            $monthTransactions->where('status_pembayaran', 'BATAL')->count(),
            $monthTransactions->where('status_pembayaran', 'LUNAS')->sum('total'),
            $monthTransactions->where('status_pembayaran', 'LUNAS')->sum('total_item'),
        ]);
        fputcsv($output, ['']);
        fputcsv($output, ['Generated at: '.date('d F Y H:i:s')]);

        fclose($output);

        return response()->streamDownload(function () {}, $filename, $headers);
    }
}
