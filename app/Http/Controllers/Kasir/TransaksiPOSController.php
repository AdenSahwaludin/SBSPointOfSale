<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
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

                // Field values untuk scoring
                $nama = strtolower($p->nama);
                $sku = strtolower($p->sku ?? $p->id_produk);
                $barcode = strtolower($p->barcode ?? '');
                $kategori = strtolower($p->kategori->nama ?? '');

                // 1. Exact match (highest priority)
                if ($barcode === $searchTerm) {
                    $score += 1000; // Barcode exact match
                }
                if ($sku === $searchTerm) {
                    $score += 900; // SKU exact match
                }
                if ($nama === $searchTerm) {
                    $score += 800; // Nama exact match
                }

                // 2. Starts with (high priority)
                if (str_starts_with($barcode, $searchTerm)) {
                    $score += 700;
                }
                if (str_starts_with($sku, $searchTerm)) {
                    $score += 600;
                }
                if (str_starts_with($nama, $searchTerm)) {
                    $score += 500;
                }

                // 3. Contains (medium priority)
                if (str_contains($barcode, $searchTerm)) {
                    $score += 400;
                }
                if (str_contains($sku, $searchTerm)) {
                    $score += 300;
                }
                if (str_contains($nama, $searchTerm)) {
                    $score += 200;
                }
                if (str_contains($kategori, $searchTerm)) {
                    $score += 100;
                }

                // 4. Word-by-word search (untuk query multi-kata)
                $searchWords = explode(' ', $searchTerm);
                foreach ($searchWords as $word) {
                    if (strlen($word) > 2) { // Skip kata pendek
                        if (str_contains($nama, $word)) {
                            $score += 50;
                        }
                        if (str_contains($sku, $word)) {
                            $score += 40;
                        }
                    }
                }

                // 5. Fuzzy matching untuk typo tolerance
                // Hitung similarity menggunakan levenshtein untuk nama
                $nameWords = explode(' ', $nama);
                foreach ($nameWords as $nameWord) {
                    if (strlen($nameWord) > 3 && strlen($searchTerm) > 3) {
                        $similarity = similar_text($searchTerm, $nameWord);
                        if ($similarity > 2) {
                            $score += $similarity * 10;
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
                ];
            })
            ->filter(function ($item) {
                return $item['_score'] > 0; // Hanya ambil yang match
            })
            ->sortByDesc('_score') // Sort by relevance
            ->take(10) // Limit hasil
            ->values()
            ->map(function ($item) {
                unset($item['_score']); // Remove internal score dari response
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
        $request->validate([
            'id_pelanggan' => 'required|exists:pelanggan,id_pelanggan',
            'items' => 'required|array|min:1',
            'items.*.id_produk' => 'required|exists:produk,id_produk',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.mode_qty' => 'required|in:unit,pack',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'metode_bayar' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'diskon' => 'nullable|numeric|min:0',
            'pajak' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'jumlah_bayar' => 'required_if:metode_bayar,TUNAI|nullable|numeric|min:0|gte:total',
        ]);

        try {
            DB::beginTransaction();

            // Generate nomor transaksi
            $nomorTransaksi = Transaksi::generateNomorTransaksi($request->id_pelanggan);

            $isCashPayment = $request->metode_bayar === 'TUNAI';
            
            // Untuk non-tunai, set jumlah_bayar = total (tidak ada kembalian)
            $jumlahBayar = $isCashPayment ? $request->jumlah_bayar : $request->total;

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
                'status_pembayaran' => $isCashPayment ? Transaksi::STATUS_LUNAS : Transaksi::STATUS_MENUNGGU,
                'paid_at' => $isCashPayment ? now() : null,
                // Field Cicilan Pintar (akan diaktifkan nanti)
                'jenis_transaksi' => 'TUNAI',
                'dp' => 0,
                'tenor_bulan' => null,
                'bunga_persen' => 0,
                'cicilan_bulanan' => null,
                'ar_status' => 'NA',
                'id_kontrak' => null,
            ]);

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
            } else {
                // Pembayaran non-tunai (QRIS, TRANSFER BCA, KREDIT)
                $metodeBayarLabels = [
                    'QRIS' => 'QRIS',
                    'TRANSFER BCA' => 'Transfer BCA',
                    'KREDIT' => 'Kredit',
                ];
                
                $label = $metodeBayarLabels[$request->metode_bayar] ?? $request->metode_bayar;
                
                Pembayaran::create([
                    'id_pembayaran' => $idPembayaran,
                    'id_transaksi' => $nomorTransaksi,
                    'metode' => $request->metode_bayar,
                    'jumlah' => $request->total,
                    'tanggal' => now(),
                    'keterangan' => 'Menunggu konfirmasi pembayaran via ' . $label,
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
