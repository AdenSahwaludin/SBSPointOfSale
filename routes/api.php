<?php

use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    // Get transaction details as JSON
    Route::get('/transactions/{nomor_transaksi}', function ($nomorTransaksi) {
        $transaksi = Transaksi::with(['pelanggan', 'kasir', 'detail', 'kontrakKredit.jadwalAngsuran'])
            ->findOrFail($nomorTransaksi);

        return response()->json([
            'data' => $transaksi,
        ]);
    })->name('transactions.show.api');
});
