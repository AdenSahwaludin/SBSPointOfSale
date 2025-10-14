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
            'kategori' => Kategori::all(),
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
            ],
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
            'jumlah_bayar' => 'required_if:metode_bayar,TUNAI|nullable|numeric|min:0|gte:total',
        ]);

        try {
            DB::beginTransaction();

            // Generate nomor transaksi
            $nomorTransaksi = Transaksi::generateNomorTransaksi($request->id_pelanggan);

            $isCashPayment = $request->metode_bayar === 'TUNAI';

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
                'status_pembayaran' => $isCashPayment ? Transaksi::STATUS_LUNAS : Transaksi::STATUS_MENUNGGU,
                'paid_at' => $isCashPayment ? now() : null,
            ]);

            foreach ($request->items as $item) {
                $produk = Produk::find($item['id_produk']);

                // Check stock - untuk mengurangi stok fisik
                $packSize = max(1, (int)($produk->isi_per_pack ?? 1));
                $requestedStock = $item['mode_qty'] === 'pack'
                    ? $item['jumlah'] * $packSize  // pack: qty × pack_size untuk stok fisik
                    : $item['jumlah'];             // unit: qty langsung

                if ($produk->stok < $requestedStock) {
                    throw new \Exception("Stok {$produk->nama} tidak mencukupi");
                }

                $packSizeSnapshot = $item['mode_qty'] === 'pack' ? $packSize : 1;

                TransaksiDetail::create([
                    'nomor_transaksi' => $nomorTransaksi,
                    'id_produk' => $produk->id_produk,
                    'nama_produk' => $produk->nama,
                    'harga_satuan' => $item['harga_satuan'], // sudah benar dari frontend
                    'jumlah' => $item['jumlah'],
                    'mode_qty' => $item['mode_qty'],
                    'pack_size_snapshot' => $packSizeSnapshot,
                    'diskon_item' => 0,
                    // PENTING: subtotal = jumlah × harga_satuan (harga_satuan sudah termasuk konversi pack)
                    'subtotal' => $item['harga_satuan'] * $item['jumlah'],
                ]);

                // Update stock - kurangi stok fisik unit
                $stockDeduction = $requestedStock;
                $produk->decrement('stok', $stockDeduction);
            }

            // Create payment record
            if ($isCashPayment) {
                $idPembayaran = Pembayaran::generateIdPembayaran();

                Pembayaran::create([
                    'id_pembayaran' => $idPembayaran,
                    'id_transaksi' => $nomorTransaksi,
                    'metode' => $request->metode_bayar,
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

            if ($transaksi->status_pembayaran === Transaksi::STATUS_LUNAS) {
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
