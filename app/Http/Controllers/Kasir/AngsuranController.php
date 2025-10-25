<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\KontrakKredit;
use App\Models\JadwalAngsuran;
use App\Models\Pembayaran;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class AngsuranController extends Controller
{
    /**
     * List contracts with default filter = have due/late installments this month.
     */
    public function index(Request $request): Response
    {
        $search = $request->get('search');
        $onlyDueThisMonth = filter_var($request->get('due_this_month', '0'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($onlyDueThisMonth === null)
            $onlyDueThisMonth = false;

        $startMonth = Carbon::now()->startOfMonth();
        $endMonth = Carbon::now()->endOfMonth();

        $contracts = KontrakKredit::with(['pelanggan'])
            ->where('status', '!=', 'LUNAS')
            ->when($search, function ($q) use ($search) {
                $q->where('nomor_kontrak', 'like', "%{$search}%")
                    ->orWhereHas('pelanggan', function ($q2) use ($search) {
                        $q2->where('nama', 'like', "%{$search}%");
                    });
            })
            ->when($onlyDueThisMonth, function ($q) use ($startMonth, $endMonth) {
                $q->whereHas('jadwalAngsuran', function ($qq) use ($startMonth, $endMonth) {
                    $qq->whereIn('status', ['DUE', 'LATE'])
                        ->whereBetween('jatuh_tempo', [$startMonth->toDateString(), $endMonth->toDateString()]);
                });
            })
            ->orderBy('mulai_kontrak', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Kasir/PembayaranKredit/Angsuran', [
            'contracts' => $contracts,
            'filters' => [
                'search' => $search,
                'due_this_month' => $onlyDueThisMonth ? '1' : '0',
            ],
        ]);
    }

    /**
     * Show a specific contract with schedule detail
     */
    public function show(int $id): Response
    {
        $kontrak = KontrakKredit::with([
            'pelanggan',
            'transaksi',
            'jadwalAngsuran' => function ($q) {
                $q->orderBy('periode_ke');
            }
        ])->findOrFail($id);

        $summary = [
            'pokok_pinjaman' => (float)$kontrak->pokok_pinjaman,
            'dp' => (float)$kontrak->dp,
            'bunga_persen' => (float)$kontrak->bunga_persen,
            'cicilan_bulanan' => (float)$kontrak->cicilan_bulanan,
            'tenor_bulan' => (int)$kontrak->tenor_bulan,
            'total_tagihan' => (float)$kontrak->jadwalAngsuran->sum('jumlah_tagihan'),
            'total_dibayar' => (float)$kontrak->jadwalAngsuran->sum('jumlah_dibayar'),
            'total_sisa' => (float)($kontrak->jadwalAngsuran->sum('jumlah_tagihan') - $kontrak->jadwalAngsuran->sum('jumlah_dibayar')),
        ];

        // also include a sidebar contracts list (due this month by default)
        $startMonth = Carbon::now()->startOfMonth();
        $endMonth = Carbon::now()->endOfMonth();
        $contracts = KontrakKredit::with(['pelanggan'])
            ->where('status', '!=', 'LUNAS')
            ->whereHas('jadwalAngsuran', function ($qq) use ($startMonth, $endMonth) {
                $qq->whereIn('status', ['DUE', 'LATE'])
                    ->whereBetween('jatuh_tempo', [$startMonth->toDateString(), $endMonth->toDateString()]);
            })
            ->orderBy('mulai_kontrak', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Kasir/PembayaranKredit/Angsuran', [
            'contracts' => $contracts,
            'filters' => ['due_this_month' => '1'],
            'selected' => $kontrak,
            'summary' => $summary,
        ]);
    }

    /**
     * Pay installments: allocate amount across schedule from oldest not-paid.
     */
    public function pay(Request $request, int $id)
    {
        $validated = $request->validate([
            'jumlah' => 'required|numeric|min:1',
            'metode' => 'required|in:TUNAI,QRIS,TRANSFER BCA',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $kontrak = KontrakKredit::with([
            'jadwalAngsuran' => function ($q) {
                $q->orderBy('periode_ke');
            },
            'pelanggan',
            'transaksi'
        ])->findOrFail($id);

        DB::beginTransaction();
        try {
            $remaining = (float)$validated['jumlah'];
            $kasirId = Auth::user()->id_pengguna ?? null;

            foreach ($kontrak->jadwalAngsuran as $angsuran) {
                if ($remaining <= 0)
                    break;
                if ($angsuran->status === 'PAID')
                    continue;

                $sisa = (float)$angsuran->jumlah_tagihan - (float)$angsuran->jumlah_dibayar;
                if ($sisa <= 0) {
                    $angsuran->status = 'PAID';
                    $angsuran->paid_at = now();
                    $angsuran->save();
                    continue;
                }

                $bayar = min($remaining, $sisa);
                // catat pembayaran per angsuran
                Pembayaran::create([
                    'id_pembayaran' => Pembayaran::generateIdPembayaran(),
                    'id_transaksi' => $kontrak->nomor_transaksi,
                    'id_angsuran' => $angsuran->id_angsuran,
                    'id_pelanggan' => $kontrak->id_pelanggan,
                    'id_kasir' => $kasirId,
                    'metode' => $validated['metode'],
                    'tipe_pembayaran' => 'kredit',
                    'jumlah' => $bayar,
                    'tanggal' => now(),
                    'keterangan' => $validated['keterangan'] ?? 'Pembayaran angsuran',
                ]);

                // update angsuran
                $angsuran->jumlah_dibayar = (float)$angsuran->jumlah_dibayar + $bayar;
                if ($angsuran->jumlah_dibayar >= $angsuran->jumlah_tagihan) {
                    $angsuran->status = 'PAID';
                    $angsuran->paid_at = now();
                } elseif ($angsuran->jatuh_tempo < Carbon::today()) {
                    $angsuran->status = 'LATE';
                } else {
                    $angsuran->status = 'DUE';
                }
                $angsuran->save();

                // adjust customer credit balance and limit
                $pelanggan = $kontrak->pelanggan;
                if ($pelanggan) {
                    $pelanggan->saldo_kredit = max(0, (float)$pelanggan->saldo_kredit - $bayar);
                    $pelanggan->credit_limit = (float)$pelanggan->credit_limit + $bayar;
                    $pelanggan->save();
                }

                $remaining -= $bayar;
            }

            // Jika semua angsuran lunas, update kontrak dan transaksi
            $unpaid = $kontrak->jadwalAngsuran()->where('status', '!=', 'PAID')->count();
            if ($unpaid === 0) {
                $kontrak->status = 'LUNAS';
                $kontrak->save();

                // Update transaksi menjadi LUNAS
                $trx = $kontrak->transaksi;
                if ($trx && $trx->status_pembayaran !== Transaksi::STATUS_LUNAS) {
                    $trx->status_pembayaran = Transaksi::STATUS_LUNAS;
                    $trx->paid_at = now();
                    $trx->ar_status = 'LUNAS';
                    $trx->save();
                }
            }

            DB::commit();
            return back()->with('success', 'Pembayaran angsuran berhasil diproses');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }
}
