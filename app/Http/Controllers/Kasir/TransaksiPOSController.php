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
        return Inertia::render('Kasir/POS/NewIndex', [
            'produk' => Produk::with('kategori')->inStock()->get(),
            'kategori' => Kategori::with('produk')->get(),
            'pelanggan' => Pelanggan::active()->get(),
            'metodeBayar' => [
                'TUNAI' => 'Tunai',
                'QRIS' => 'QRIS',
                'VA_BCA' => 'VA BCA',
                'VA_BNI' => 'VA BNI',
                'VA_BRI' => 'VA BRI',
                'VA_MANDIRI' => 'VA Mandiri',
                'GOPAY' => 'GoPay',
                'OVO' => 'OVO',
                'DANA' => 'DANA',
                'SHOPEEPAY' => 'ShopeePay',
                'CREDIT_CARD' => 'Credit Card',
            ]
        ]);
    }

    /**
     * Search produk by barcode or name
     */
    public function searchProduk(Request $request)
    {
        $query = $request->get('q');
        
        $produk = Produk::with('kategori')
            ->where(function ($q) use ($query) {
                $q->where('id_produk', 'like', "%{$query}%")
                  ->orWhere('nama', 'like', "%{$query}%");
            })
            ->inStock()
            ->limit(10)
            ->get();

        return response()->json($produk);
    }

    /**
     * Get produk by barcode
     */
    public function getProdukByBarcode(Request $request)
    {
        $barcode = $request->get('barcode');
        
        $produk = Produk::with('kategori')
            ->where('id_produk', $barcode)
            ->inStock()
            ->first();

        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json($produk);
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
            'jumlah_bayar' => 'required_if:metode_bayar,TUNAI|nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Generate nomor transaksi
            $nomorTransaksi = Transaksi::generateNomorTransaksi($request->id_pelanggan);

            // Create transaksi
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
                'status_pembayaran' => $request->metode_bayar === 'TUNAI' ? 'PAID' : 'PENDING',
                'paid_at' => $request->metode_bayar === 'TUNAI' ? now() : null,
            ]);

            // Create transaction details and update stock
            foreach ($request->items as $item) {
                $produk = Produk::find($item['id_produk']);
                
                // Calculate quantity to reduce from stock
                $qtyToReduce = $item['jumlah'];
                if ($item['mode_qty'] === 'pack') {
                    $qtyToReduce *= $produk->pack_size;
                }

                // Check stock availability
                if ($produk->stok < $qtyToReduce) {
                    throw new \Exception("Stok tidak cukup untuk produk {$produk->nama}");
                }

                // Create detail
                TransaksiDetail::create([
                    'nomor_transaksi' => $nomorTransaksi,
                    'id_produk' => $item['id_produk'],
                    'nama_produk' => $produk->nama,
                    'harga_satuan' => $item['harga_satuan'],
                    'jumlah' => $item['jumlah'],
                    'mode_qty' => $item['mode_qty'],
                    'pack_size_snapshot' => $produk->pack_size,
                    'diskon_item' => $item['diskon_item'] ?? 0,
                    'subtotal' => $item['jumlah'] * $item['harga_satuan'] - ($item['diskon_item'] ?? 0),
                ]);

                // Update stock
                $produk->decrement('stok', $qtyToReduce);
            }

            // Create payment record if cash
            if ($request->metode_bayar === 'TUNAI') {
                $idPembayaran = Pembayaran::generateIdPembayaran();
                
                Pembayaran::create([
                    'id_pembayaran' => $idPembayaran,
                    'id_transaksi' => $nomorTransaksi,
                    'metode' => 'TUNAI',
                    'jumlah' => $request->total,
                    'tanggal' => now(),
                    'keterangan' => 'Pembayaran tunai - Kembalian: Rp ' . number_format($request->jumlah_bayar - $request->total, 0, ',', '.'),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan',
                'data' => [
                    'nomor_transaksi' => $nomorTransaksi,
                    'total' => $request->total,
                    'kembalian' => $request->metode_bayar === 'TUNAI' ? $request->jumlah_bayar - $request->total : 0,
                    'metode_bayar' => $request->metode_bayar,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
            ], 500);
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

            if ($transaksi->status_pembayaran === 'PAID') {
                return response()->json(['message' => 'Transaksi yang sudah dibayar tidak dapat dibatalkan'], 400);
            }

            // Restore stock
            foreach ($transaksi->detail as $detail) {
                $qtyToRestore = $detail->jumlah;
                if ($detail->mode_qty === 'pack') {
                    $qtyToRestore *= $detail->pack_size_snapshot;
                }
                
                $detail->produk->increment('stok', $qtyToRestore);
            }

            // Update status
            $transaksi->update([
                'status_pembayaran' => 'VOID',
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
