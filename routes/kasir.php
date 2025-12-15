<?php

use App\Http\Controllers\Kasir\AngsuranController;
use App\Http\Controllers\Kasir\DashboardController;
use App\Http\Controllers\Kasir\KonversiStokController;
use App\Http\Controllers\Kasir\ProdukController;
use App\Http\Controllers\Kasir\TransaksiController;
use App\Http\Controllers\Kasir\TransaksiPOSController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Kasir routes (Kasir role only)
Route::prefix('kasir')->middleware(['auth', 'role:kasir'])->name('kasir.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ==========================================
    // POINT OF SALE (POS) SYSTEM
    // ==========================================
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [TransaksiPOSController::class, 'index'])->name('index');
        Route::post('/', [TransaksiPOSController::class, 'store'])->name('store');

        // Product search and lookup
        Route::get('/search-produk', [TransaksiPOSController::class, 'searchProduk'])->name('search-produk');
        Route::get('/produk', [TransaksiPOSController::class, 'getProdukByBarcode'])->name('produk');

        // Transaction management
        Route::get('/receipt/{nomorTransaksi}', [TransaksiPOSController::class, 'getTransactionReceipt'])->name('receipt');
        Route::get('/today-transactions', [TransaksiPOSController::class, 'getTodayTransactions'])->name('today');
        Route::post('/cancel/{nomorTransaksi}', [TransaksiPOSController::class, 'cancelTransaction'])->name('cancel');
    });

    // ==========================================
    // TRANSACTION HISTORY
    // ==========================================
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [TransaksiController::class, 'index'])->name('index');
        Route::get('/today', [TransaksiController::class, 'today'])->name('today');
        Route::get('/{nomorTransaksi}', [TransaksiController::class, 'show'])->name('show');
        Route::patch('/{nomorTransaksi}/status', [TransaksiController::class, 'updateStatus'])->name('update-status');
    });

    // ==========================================
    // PRODUCTS (View Only)
    // ==========================================
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProdukController::class, 'index'])->name('index');
        Route::get('/{id}', [ProdukController::class, 'show'])->name('show');
    });

    // ==========================================
    // CUSTOMERS (Limited CRUD - Kasir specific)
    // ==========================================
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [PelangganController::class, 'index'])->name('index');
        Route::get('/create', [PelangganController::class, 'create'])->name('create');
        Route::post('/', [PelangganController::class, 'store'])->name('store');
        Route::get('/{id}', [PelangganController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PelangganController::class, 'edit'])->name('edit');
        Route::patch('/{id}', [PelangganController::class, 'update'])->name('update');
        Route::delete('/{id}', [PelangganController::class, 'destroy'])->name('destroy');
        // API endpoint untuk mendapatkan alasan blocking penghapusan
        Route::get('/{id}/deletion-block-reasons', [PelangganController::class, 'getDeletionBlockReasons'])->name('deletion-block-reasons');
    });

    // ==========================================
    // CREDIT INSTALLMENTS (Angsuran)
    // ==========================================
    Route::prefix('angsuran')->name('angsuran.')->group(function () {
        Route::get('/', [AngsuranController::class, 'index'])->name('index');
        Route::get('/{id}', [AngsuranController::class, 'show'])->name('show');
        Route::post('/{id}/pay', [AngsuranController::class, 'pay'])->name('pay');
    });

    // ==========================================
    // STOCK CONVERSION (Konversi Stok)
    // ==========================================
    Route::resource('konversi-stok', KonversiStokController::class)->names([
        'index' => 'konversi-stok.index',
        'create' => 'konversi-stok.create',
        'store' => 'konversi-stok.store',
        'show' => 'konversi-stok.show',
        'edit' => 'konversi-stok.edit',
        'update' => 'konversi-stok.update',
        'destroy' => 'konversi-stok.destroy',
    ]);

    // Bulk delete konversi stok
    Route::post('konversi-stok/bulk-delete', [KonversiStokController::class, 'bulkDelete'])
        ->name('konversi-stok.bulk-delete');

    // ==========================================
    // GOODS IN (Barang Masuk / PO Request)
    // ==========================================
    Route::resource('goods-in', \App\Http\Controllers\Kasir\GoodsInController::class)->names([
        'index' => 'goods-in.index',
        'create' => 'goods-in.create',
        'store' => 'goods-in.store',
        'show' => 'goods-in.show',
    ])->only(['index', 'create', 'store', 'show']);

    // PO Item management routes
    Route::post('goods-in/{goodsIn}/items', [\App\Http\Controllers\Kasir\GoodsInController::class, 'addItem'])
        ->name('goods-in.items.add');
    Route::patch('goods-in/{goodsIn}/items/{id_detail}', [\App\Http\Controllers\Kasir\GoodsInController::class, 'updateItem'])
        ->name('goods-in.items.update');
    Route::delete('goods-in/{goodsIn}/items/{id_detail}', [\App\Http\Controllers\Kasir\GoodsInController::class, 'removeItem'])
        ->name('goods-in.items.remove');
    Route::post('goods-in/{goodsIn}/submit', [\App\Http\Controllers\Kasir\GoodsInController::class, 'submit'])
        ->name('goods-in.submit');
    Route::delete('goods-in/{goodsIn}', [\App\Http\Controllers\Kasir\GoodsInController::class, 'destroy'])
        ->name('goods-in.destroy');

    // Receiving goods from approved POs
    Route::get('goods-in-receiving', [\App\Http\Controllers\Kasir\GoodsInController::class, 'receivingIndex'])
        ->name('goods-in-receiving.index');
    Route::get('goods-in/{goodsIn}/receiving', [\App\Http\Controllers\Kasir\GoodsInController::class, 'receivingShow'])
        ->name('goods-in.receiving-show');
    Route::post('goods-in/{goodsIn}/record-received', [\App\Http\Controllers\Kasir\GoodsInController::class, 'recordReceived'])
        ->name('goods-in.record-received');

    // ==========================================
    // STOCK ADJUSTMENT (Penyesuaian Stok & Retur)
    // ==========================================
    Route::resource('stock-adjustment', \App\Http\Controllers\Kasir\StockAdjustmentController::class)->names([
        'index' => 'stock-adjustment.index',
        'create' => 'stock-adjustment.create',
        'store' => 'stock-adjustment.store',
    ])->only(['index', 'create', 'store']);

    // ==========================================
    // PROFILE MANAGEMENT (Kasir specific)
    // ==========================================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('password');
    });

    // ==========================================
    // SHIFT MANAGEMENT (untuk masa depan)
    // ==========================================
    // Route::post('/shift/start', [ShiftController::class, 'start'])->name('shift.start');
    // Route::post('/shift/end', [ShiftController::class, 'end'])->name('shift.end');

    // ==========================================
    // QUICK REPORTS (untuk masa depan)
    // ==========================================
    // Route::get('/daily-summary', [ReportController::class, 'dailySummary'])->name('daily-summary');
});
