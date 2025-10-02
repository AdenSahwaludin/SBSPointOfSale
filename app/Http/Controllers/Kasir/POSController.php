<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Midtrans\Config;
use Midtrans\Snap;

class POSController extends Controller
{
    public function __construct()
    {
        // Configure Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function index()
    {
        $products = Product::active()->get();
        
        return Inertia::render('Kasir/POS/Index', [
            'products' => $products
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id_produk' => 'required|exists:products,id_produk',
            'items.*.jumlah' => 'required|integer|min:1',
            'nama_pelanggan' => 'nullable|string|max:255',
            'nomor_hp_pelanggan' => 'nullable|numeric|digits_between:1,15',
            'metode_pembayaran' => 'required|in:tunai,midtrans',
            'bayar' => 'required_if:metode_pembayaran,tunai|nullable|integer|min:0',
        ]);

        return DB::transaction(function () use ($validated) {
            // Generate transaction ID
            $transactionId = $this->generateTransactionId();
            
            // Calculate totals
            $subtotal = 0;
            $totalItem = 0;
            $transactionItems = [];

            foreach ($validated['items'] as $item) {
                $product = Product::find($item['id_produk']);
                
                // Check stock
                if ($product->stok < $item['jumlah']) {
                    throw new \Exception("Stok {$product->nama_produk} tidak mencukupi");
                }

                $itemSubtotal = $product->harga * $item['jumlah'];
                $subtotal += $itemSubtotal;
                $totalItem += $item['jumlah'];

                $transactionItems[] = [
                    'id_transaksi' => $transactionId,
                    'id_produk' => $product->id_produk,
                    'nama_produk' => $product->nama_produk,
                    'harga' => $product->harga,
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $itemSubtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Update stock
                $product->decrement('stok', $item['jumlah']);
            }

            // Calculate tax (11%)
            $pajak = round($subtotal * 0.11);
            $total = $subtotal + $pajak;

            // Create transaction
            $transactionData = [
                'id_transaksi' => $transactionId,
                'id_kasir' => Auth::user()->id_pengguna,
                'nama_pelanggan' => $validated['nama_pelanggan'],
                'nomor_hp_pelanggan' => $validated['nomor_hp_pelanggan'],
                'total_item' => $totalItem,
                'subtotal' => $subtotal,
                'pajak' => $pajak,
                'total' => $total,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status' => 'pending',
            ];

            if ($validated['metode_pembayaran'] === 'tunai') {
                $transactionData['bayar'] = $validated['bayar'];
                $transactionData['kembalian'] = $validated['bayar'] - $total;
                $transactionData['status'] = 'success';
            }

            $transaction = Transaction::create($transactionData);

            // Create transaction items
            TransactionItem::insert($transactionItems);

            // Handle Midtrans payment
            if ($validated['metode_pembayaran'] === 'midtrans') {
                $midtransOrderId = $transactionId . '-' . time();
                $transaction->update(['midtrans_order_id' => $midtransOrderId]);

                $params = [
                    'transaction_details' => [
                        'order_id' => $midtransOrderId,
                        'gross_amount' => $total,
                    ],
                    'customer_details' => [
                        'first_name' => $validated['nama_pelanggan'] ?? 'Customer',
                        'phone' => $validated['nomor_hp_pelanggan'] ?? '',
                    ],
                    'item_details' => array_map(function ($item) {
                        return [
                            'id' => $item['id_produk'],
                            'price' => $item['harga'],
                            'quantity' => $item['jumlah'],
                            'name' => $item['nama_produk'],
                        ];
                    }, $transactionItems),
                ];

                // Add tax as separate item
                $params['item_details'][] = [
                    'id' => 'TAX',
                    'price' => $pajak,
                    'quantity' => 1,
                    'name' => 'Pajak (11%)',
                ];

                try {
                    $snapToken = Snap::getSnapToken($params);
                    
                    return redirect()->back()->with('response', [
                        'success' => true,
                        'transaction_id' => $transactionId,
                        'snap_token' => $snapToken,
                        'total' => $total,
                    ]);
                } catch (\Exception $e) {
                    // Rollback transaction on Midtrans error
                    $transaction->delete();
                    throw new \Exception('Gagal membuat pembayaran: ' . $e->getMessage());
                }
            }

            return redirect()->back()->with('response', [
                'success' => true,
                'transaction_id' => $transactionId,
                'total' => $total,
                'kembalian' => $transactionData['kembalian'] ?? 0,
            ]);
        });
    }

    public function callback(Request $request)
    {
        $orderId = $request->order_id;
        $statusCode = $request->status_code;
        $grossAmount = $request->gross_amount;
        $serverKey = config('services.midtrans.server_key');
        
        $hashed = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        
        if ($hashed !== $request->signature_key) {
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
        }

        $transaction = Transaction::where('midtrans_order_id', $orderId)->first();
        
        if (!$transaction) {
            return response()->json(['status' => 'error', 'message' => 'Transaction not found'], 404);
        }

        $transactionStatus = $request->transaction_status;
        $fraudStatus = $request->fraud_status ?? null;

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'challenge') {
                $transaction->update([
                    'status' => 'pending',
                    'midtrans_transaction_status' => 'challenge',
                    'midtrans_response' => $request->all(),
                ]);
            } else if ($fraudStatus == 'accept') {
                $transaction->update([
                    'status' => 'success',
                    'midtrans_transaction_status' => 'capture',
                    'midtrans_response' => $request->all(),
                ]);
            }
        } else if ($transactionStatus == 'settlement') {
            $transaction->update([
                'status' => 'success',
                'midtrans_transaction_status' => 'settlement',
                'midtrans_response' => $request->all(),
            ]);
        } else if ($transactionStatus == 'pending') {
            $transaction->update([
                'status' => 'pending',
                'midtrans_transaction_status' => 'pending',
                'midtrans_response' => $request->all(),
            ]);
        } else if ($transactionStatus == 'deny') {
            $transaction->update([
                'status' => 'failed',
                'midtrans_transaction_status' => 'deny',
                'midtrans_response' => $request->all(),
            ]);
        } else if ($transactionStatus == 'expire') {
            $transaction->update([
                'status' => 'failed',
                'midtrans_transaction_status' => 'expire',
                'midtrans_response' => $request->all(),
            ]);
        } else if ($transactionStatus == 'cancel') {
            $transaction->update([
                'status' => 'cancelled',
                'midtrans_transaction_status' => 'cancel',
                'midtrans_response' => $request->all(),
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function generateTransactionId(): string
    {
        $lastTransaction = Transaction::orderBy('created_at', 'desc')->first();
        $lastNumber = 1;
        
        if ($lastTransaction) {
            $lastId = $lastTransaction->id_transaksi;
            $lastNumber = intval(substr($lastId, 3)) + 1;
        }

        return 'TRX' . str_pad($lastNumber, 6, '0', STR_PAD_LEFT);
    }
}
