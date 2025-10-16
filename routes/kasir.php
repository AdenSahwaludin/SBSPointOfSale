<?php

use App\Http\Controllers\Kasir\DashboardController;
use App\Http\Controllers\Kasir\TransaksiPOSController;
use App\Http\Controllers\Kasir\TransaksiController;
use App\Http\Controllers\Kasir\ProdukController;
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
 });

 // ==========================================
 // PRODUCTS (View Only)
 // ==========================================
 Route::prefix('products')->name('products.')->group(function () {
  Route::get('/', [ProdukController::class, 'index'])->name('index');
  Route::get('/{id}', [ProdukController::class, 'show'])->name('show');
 });

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
