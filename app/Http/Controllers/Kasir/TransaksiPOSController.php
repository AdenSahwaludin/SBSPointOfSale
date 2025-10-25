<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\Pembayaran;
use App\Models\KontrakKredit;
use App\Models\JadwalAngsuran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class TransaksiPOSController extends Controller
{
    /**
     * Display POS interface
     */
    public function index()
    {
        return Inertia::render('Kasir/POS/Index', [
            'produk' => Produk::with('kategori')
                ->inStock()
                ->get()
                ->map(function ($p) {
                    return [
                        'id_produk' => $p->id_produk,
                        'barcode' => $p->barcode,
                        'nama' => $p->nama,
                        'harga' => $p->harga,
                        'harga_pack' => $p->harga_pack,
                        'stok' => $p->stok,
                        'satuan' => $p->satuan,
                        'isi_per_pack' => $p->isi_per_pack,
                        'kategori' => $p->kategori,
                    ];
                }),
            'kategori' => Kategori::all(),
            'pelanggan' => Pelanggan::active()->get(),
            'metodeBayar' => [
                'TUNAI' => 'Tunai',
                'QRIS' => 'QRIS',
                'TRANSFER BCA' => 'Transfer BCA',
                'KREDIT' => 'Kredit',

            ],
        ]);
    }

    /**
     * Search produk with advanced algorithm
     * Features: Multi-field search, relevance scoring, fuzzy matching
     */
    public function searchProduk(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return response()->json([]);
        }

        $searchTerm = strtolower(trim($query));

        // Ambil semua produk in stock
        $produk = Produk::with('kategori')
            ->inStock()
            ->get()
            ->map(function ($p) use ($searchTerm) {
                $score = 0;
                $matched = false;

                // Field values untuk scoring
                $nama = strtolower($p->nama);
                $sku = strtolower($p->sku ?? $p->id_produk);
                $barcode = strtolower($p->barcode ?? '');
                $kategori = strtolower($p->kategori->nama ?? '');

                // 1. Exact match (highest priority)
                if ($barcode === $searchTerm) {
                    $score += 1000; // Barcode exact match
                    $matched = true;
                }
                if ($sku === $searchTerm) {
                    $score += 900; // SKU exact match
                    $matched = true;
                }
                if ($nama === $searchTerm) {
                    $score += 800; // Nama exact match
                    $matched = true;
                }

                // 2. Starts with (high priority)
                if (str_starts_with($barcode, $searchTerm)) {
                    $score += 700;
                    $matched = true;
                }
                if (str_starts_with($sku, $searchTerm)) {
                    $score += 600;
                    $matched = true;
                }
                if (str_starts_with($nama, $searchTerm)) {
                    $score += 500;
                    $matched = true;
                }

                // 3. Contains (medium priority)
                if (str_contains($barcode, $searchTerm)) {
                    $score += 400;
                    $matched = true;
                }
                if (str_contains($sku, $searchTerm)) {
                    $score += 300;
                    $matched = true;
                }
                if (str_contains($nama, $searchTerm)) {
                    $score += 200;
                    $matched = true;
                }
                if (str_contains($kategori, $searchTerm)) {
                    $score += 50; // kategori menambah skor tapi tidak memicu match
                }

                // 4. Word-by-word search (untuk query multi-kata)
                $searchWords = array_filter(explode(' ', $searchTerm));
                foreach ($searchWords as $word) {
                    if (strlen($word) > 2) { // Skip kata pendek
                        if (str_contains($nama, $word)) { $score += 60; $matched = true; }
                        if (str_contains($sku, $word)) { $score += 40; $matched = true; }
                    }
                }

                // 5. Fuzzy matching kuat (aktif hanya jika belum match)
                if (!$matched && strlen($searchTerm) > 3) {
                    $nameWords = array_filter(explode(' ', $nama));
                    foreach ($nameWords as $nameWord) {
                        if (strlen($nameWord) > 3) {
                            $percent = 0.0;
                            similar_text($searchTerm, $nameWord, $percent);
                            if ($percent >= 70) {
                                $score += (int) round($percent);
                                $matched = true;
                                break;
                            }
                        }
                    }
                }

                return [
                    'id_produk' => $p->id_produk,
                    'sku' => $p->sku ?? $p->id_produk,
                    'barcode' => $p->barcode,
                    'nama' => $p->nama,
                    'harga' => $p->harga,
                    'harga_pack' => $p->harga_pack,
                    'stok' => $p->stok,
                    'satuan' => $p->satuan,
                    'isi_per_pack' => $p->isi_per_pack,
                    'kategori' => $p->kategori,
                    '_score' => $score, // Internal scoring
                    '_match' => $matched,
                ];
            })
            ->filter(function ($item) {
                return ($item['_score'] > 0) && !empty($item['_match']);
            })
            ->sortByDesc('_score') // Sort by relevance
            ->take(10) // Limit hasil
            ->values()
            ->map(function ($item) {
                unset($item['_score'], $item['_match']);
                return $item;
            });

        return response()->json($produk);
    }

    /**
     * Get produk by barcode (gunakan kolom barcode)
     */
    public function getProdukByBarcode(Request $request)
    {
        $barcode = $request->get('barcode');

        $produk = Produk::with('kategori')
            ->where('barcode', $barcode)
            ->inStock()
            ->first();

        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json([
            'id_produk' => $produk->id_produk,
            'barcode' => $produk->barcode,
            'nama' => $produk->nama,
            'harga' => $produk->harga,
            'harga_pack' => $produk->harga_pack,
            'stok' => $produk->stok,
            'satuan' => $produk->satuan,
            'isi_per_pack' => $produk->isi_per_pack,
            'kategori' => $produk->kategori,
        ]);
    }

    /**
     * Store new transaction
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'items' => 'required|array|min:1',
            'items.*.id_produk' => 'required|exists:produk,id_produk',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.mode_qty' => 'required|in:unit,pack',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'metode_bayar' => 'required|in:TUNAI,QRIS,TRANSFER BCA,KREDIT',
            'subtotal' => 'required|numeric|min:0',
            'diskon' => 'nullable|numeric|min:0',
            'pajak' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            // Exclude from validation when non-cash to avoid gte:total failing on 0
            'jumlah_bayar' => 'exclude_unless:metode_bayar,TUNAI|required_if:metode_bayar,TUNAI|nullable|numeric|min:0|gte:total',
            // For credit transactions, require DP
            'dp' => 'required_if:metode_bayar,KREDIT|nullable|numeric|min:0|lt:total',
            // Optional credit contract fields
            'tenor_bulan' => 'exclude_unless:metode_bayar,KREDIT|nullable|integer|min:1|max:24',
            'bunga_persen' => 'exclude_unless:metode_bayar,KREDIT|nullable|numeric|min:0|max:100',
            'cicilan_bulanan' => 'exclude_unless:metode_bayar,KREDIT|nullable|numeric|min:0',
            'mulai_kontrak' => 'exclude_unless:metode_bayar,KREDIT|nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Generate nomor transaksi
            $nomorTransaksi = Transaksi::generateNomorTransaksi($request->id_pelanggan);

            $isCashPayment = $request->metode_bayar === 'TUNAI';
            $isCredit = $request->metode_bayar === 'KREDIT';

            // Untuk non-tunai, set jumlah_bayar = total (tidak ada kembalian)
            $jumlahBayar = $isCashPayment ? $request->jumlah_bayar : $request->total;

            // Enforce credit limit rules for credit transactions
            if ($isCredit) {
                $dp = (float)($request->dp ?? 0);
                $total = (float)$request->total;
                $creditPortion = max(0.0, $total - $dp);

                // Ambil available limit dari kolom credit_limit (diperlakukan sebagai available)
                $customer = \App\Models\Pelanggan::lockForUpdate()->find($request->id_pelanggan);
                $available = (float)($customer->credit_limit ?? 0);

                if ($creditPortion > $available) {
                    $minDp = max(0.0, $total - $available);
                    return response()->json([
                        'success' => false,
                        'message' => 'Transaksi melebihi kredit limit. Tambahkan DP minimal: Rp ' . number_format($minDp, 0, ',', '.'),
                        'errors' => [ 'dp' => ['DP minimal yang dibutuhkan: ' . $minDp] ],
                    ], 422);
                }

                // Update credit: kurangi available limit, tambah saldo piutang, pastikan status aktif
                $customer->credit_limit = $available - $creditPortion;
                $customer->saldo_kredit = (float)($customer->saldo_kredit ?? 0) + $creditPortion;
                $customer->status_kredit = 'aktif';
                $customer->save();
            }

            // Create transaksi (siapkan field Cicilan Pintar untuk implementasi nanti)
            $transaksi = Transaksi::create([
                'nomor_transaksi' => $nomorTransaksi,
                'id_pelanggan' => $request->id_pelanggan,
                'id_kasir' => Auth::user()->id_pengguna,
                'tanggal' => now(),
                'total_item' => count($request->items),
                'subtotal' => $request->subtotal,
                'diskon' => $request->diskon ?? 0,
                'pajak' => $request->pajak ?? 0,
                'biaya_pengiriman' => 0,
                'total' => $request->total,
                'metode_bayar' => $request->metode_bayar,
                // Non-tunai selain kredit dianggap selesai (lunas)
                'status_pembayaran' => $isCredit ? Transaksi::STATUS_MENUNGGU : Transaksi::STATUS_LUNAS,
                'paid_at' => $isCredit ? null : now(),
                // Jenis transaksi
                'jenis_transaksi' => $isCredit ? Transaksi::JENIS_KREDIT : Transaksi::JENIS_TUNAI,
                'dp' => $isCredit ? ($request->dp ?? 0) : 0,
                'tenor_bulan' => $isCredit ? ($request->tenor_bulan ?? null) : null,
                'bunga_persen' => $isCredit ? ($request->bunga_persen ?? 0) : 0,
                'cicilan_bulanan' => $isCredit ? ($request->cicilan_bulanan ?? null) : null,
                'ar_status' => $isCredit ? 'AKTIF' : 'NA',
                'id_kontrak' => null,
            ]);

            // Jika KREDIT: buat kontrak kredit dan jadwal angsuran
            if ($isCredit) {
                $customer = \App\Models\Pelanggan::find($request->id_pelanggan);
                $principal = max(0.0, (float)$request->total - (float)($request->dp ?? 0));
                $tenorBulan = (int)($request->tenor_bulan ?? 12);
                $bungaPersen = (float)($request->bunga_persen ?? 0);
                $mulai = $request->mulai_kontrak ? Carbon::parse($request->mulai_kontrak) : Carbon::today();
                // Hitung cicilan flat jika belum diberikan klien
                $cicilanBulanan = $request->cicilan_bulanan ?? (int)ceil(($principal * (1 + ($bungaPersen / 100))) / max(1, $tenorBulan));

                $nomorKontrak = KontrakKredit::generateNomorKontrak();
                $kontrak = KontrakKredit::create([
                    'nomor_kontrak' => $nomorKontrak,
                    'id_pelanggan' => $request->id_pelanggan,
                    'nomor_transaksi' => $nomorTransaksi,
                    'mulai_kontrak' => $mulai->toDateString(),
                    'tenor_bulan' => $tenorBulan,
                    'pokok_pinjaman' => $principal,
                    'dp' => (float)($request->dp ?? 0),
                    'bunga_persen' => $bungaPersen,
                    'cicilan_bulanan' => $cicilanBulanan,
                    'status' => 'AKTIF',
                    'score_snapshot' => (int)($customer->trust_score ?? 50),
                    'alasan_eligibilitas' => null,
                ]);

                // Buat jadwal angsuran bulanan
                for ($i = 1; $i <= $tenorBulan; $i++) {
                    $due = (clone $mulai)->addMonths($i)->toDateString();
                    JadwalAngsuran::create([
                        'id_kontrak' => $kontrak->id_kontrak,
                        'periode_ke' => $i,
                        'jatuh_tempo' => $due,
                        'jumlah_tagihan' => $cicilanBulanan,
                        'jumlah_dibayar' => 0,
                        'status' => 'DUE',
                        'paid_at' => null,
                    ]);
                }

                // Tautkan kontrak ke transaksi
                $transaksi->id_kontrak = $kontrak->id_kontrak;
                $transaksi->save();
            }

            foreach ($request->items as $item) {
                $produk = Produk::find($item['id_produk']);

                // Tentukan satuan stok yang dikurangi berdasarkan satuan produk
                $isiPerPack = max(1, (int)($produk->isi_per_pack ?? 1));

                // Jika produk kemasan besar (karton/pack), stok disimpan dalam unit kemasan tsb
                if (in_array($produk->satuan, ['karton', 'pack'], true)) {
                    // Penjualan hanya boleh per kemasan (mode_qty harus 'pack')
                    if (($item['mode_qty'] ?? 'pack') !== 'pack') {
                        throw new \RuntimeException("Produk {$produk->nama} hanya bisa dijual per {$produk->satuan}");
                    }

                    $deductUnits = (int)$item['jumlah']; // kurangi stok per karton/pack

                    if ($produk->stok < $deductUnits) {
                        throw new \RuntimeException("Stok {$produk->nama} tidak mencukupi");
                    }

                    $isiPackSaatTransaksi = $isiPerPack; // snapshot isi per kemasan saat transaksi
                } else {
                    // Produk satuan pcs: jika mode pack, konversi ke pcs; jika unit, langsung pcs
                    $deductUnits = (int)($item['mode_qty'] === 'pack'
                        ? ((int)$item['jumlah']) * $isiPerPack
                        : (int)$item['jumlah']);

                    if ($produk->stok < $deductUnits) {
                        throw new \RuntimeException("Stok {$produk->nama} tidak mencukupi");
                    }

                    $isiPackSaatTransaksi = $item['mode_qty'] === 'pack' ? $isiPerPack : 1;
                }

                TransaksiDetail::create([
                    'nomor_transaksi' => $nomorTransaksi,
                    'id_produk' => $produk->id_produk,
                    'nama_produk' => $produk->nama,
                    'harga_satuan' => $item['harga_satuan'],
                    'jumlah' => $item['jumlah'],
                    'mode_qty' => $item['mode_qty'],
                    'isi_pack_saat_transaksi' => $isiPackSaatTransaksi,
                    'diskon_item' => 0,
                    // PENTING: subtotal = jumlah × harga_satuan (harga_satuan sudah termasuk konversi pack)
                    'subtotal' => $item['harga_satuan'] * $item['jumlah'],
                ]);

                // Update stok sesuai satuan yang benar
                $produk->decrement('stok', $deductUnits);
            }

            // Create payment record
            $idPembayaran = Pembayaran::generateIdPembayaran();

            if ($isCashPayment) {
                // Pembayaran tunai - ada kembalian
                $kembalian = $jumlahBayar - $request->total;

                Pembayaran::create([
                    'id_pembayaran' => $idPembayaran,
                    'id_transaksi' => $nomorTransaksi,
                    'metode' => $request->metode_bayar,
                    'jumlah' => $request->total,
                    'tanggal' => now(),
                    'keterangan' => 'Pembayaran tunai - Kembalian: Rp ' . number_format($kembalian, 0, ',', '.'),
                ]);
            } elseif ($isCredit) {
                // Kredit: catat DP (jika ada)
                $dp = (float)($request->dp ?? 0);
                if ($dp > 0) {
                    Pembayaran::create([
                        'id_pembayaran' => $idPembayaran,
                        'id_transaksi' => $nomorTransaksi,
                        'metode' => $request->metode_bayar,
                        'tipe_pembayaran' => 'kredit',
                        'id_pelanggan' => $request->id_pelanggan,
                        'id_kasir' => Auth::user()->id_pengguna,
                        'jumlah' => $dp,
                        'tanggal' => now(),
                        'keterangan' => 'DP Kredit',
                    ]);
                }
            } else {
                // Pembayaran non-tunai (QRIS, TRANSFER BCA) dianggap lunas
                $metodeBayarLabels = [
                    'QRIS' => 'QRIS',
                    'TRANSFER BCA' => 'Transfer BCA',
                ];

                $label = $metodeBayarLabels[$request->metode_bayar] ?? $request->metode_bayar;

                Pembayaran::create([
                    'id_pembayaran' => $idPembayaran,
                    'id_transaksi' => $nomorTransaksi,
                    'metode' => $request->metode_bayar,
                    'jumlah' => $request->total,
                    'tanggal' => now(),
                    'keterangan' => 'Pembayaran diterima via ' . $label,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan',
                'data' => [
                    'nomor_transaksi' => $nomorTransaksi,
                    'total' => $request->total,
                    'kembalian' => $isCashPayment ? ($jumlahBayar - $request->total) : 0,
                    'metode_bayar' => $request->metode_bayar,
                ]
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            $status = ($e instanceof \RuntimeException || $e instanceof \InvalidArgumentException) ? 422 : 500;

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
            ], $status);
        }
    }

    /**
     * Get transaction details for receipt
     */
    public function getTransactionReceipt($nomorTransaksi)
    {
        $transaksi = Transaksi::with([
            'pelanggan',
            'kasir',
            'detail.produk',
            'pembayaran'
        ])->find($nomorTransaksi);

        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        return response()->json($transaksi);
    }

    /**
     * Get today's transactions
     */
    public function getTodayTransactions()
    {
        $transactions = Transaksi::with(['pelanggan', 'kasir'])
            ->today()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($transactions);
    }

    /**
     * Cancel/void transaction
     */
    public function cancelTransaction(Request $request, $nomorTransaksi)
    {
        try {
            DB::beginTransaction();

            $transaksi = Transaksi::with('detail.produk')->find($nomorTransaksi);

            if (!$transaksi) {
                return response()->json(['message' => 'Transaksi tidak ditemukan'], 404);
            }

            if ($transaksi->status_pembayaran === Transaksi::STATUS_LUNAS) {
                return response()->json(['message' => 'Transaksi yang sudah dibayar tidak dapat dibatalkan'], 400);
            }

            // Restore stock
            foreach ($transaksi->detail as $detail) {
                $qtyToRestore = $detail->jumlah;
                if ($detail->mode_qty === 'pack') {
                    // Jika produk pcs dijual per pack, kembalikan ke pcs dengan konversi;
                    // jika produk kemasan besar, kembalikan per kemasan (tanpa konversi)
                    if (($detail->produk->satuan ?? 'pcs') === 'pcs') {
                        $qtyToRestore *= max(1, (int)$detail->isi_pack_saat_transaksi);
                    }
                }

                $detail->produk->increment('stok', $qtyToRestore);
            }

            // Update status
            $transaksi->update([
                'status_pembayaran' => Transaksi::STATUS_BATAL,
                'paid_at' => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibatalkan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan transaksi: ' . $e->getMessage()
            ], 500);
        }
    }
}
