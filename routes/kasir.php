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
  Route::get('/', [\App\Http\Controllers\Kasir\PelangganController::class, 'index'])->name('index');
  Route::get('/create', [\App\Http\Controllers\Kasir\PelangganController::class, 'create'])->name('create');
  Route::post('/', [\App\Http\Controllers\Kasir\PelangganController::class, 'store'])->name('store');
  Route::get('/{id}', [\App\Http\Controllers\Kasir\PelangganController::class, 'show'])->name('show');
  Route::get('/{id}/edit', [\App\Http\Controllers\Kasir\PelangganController::class, 'edit'])->name('edit');
  Route::patch('/{id}', [\App\Http\Controllers\Kasir\PelangganController::class, 'update'])->name('update');
  Route::delete('/{id}', [\App\Http\Controllers\Kasir\PelangganController::class, 'destroy'])->name('destroy');
 });

 // ==========================================
 // CREDIT PAYMENT MANAGEMENT
 // ==========================================
 Route::prefix('pembayaran-kredit')->name('pembayaran-kredit.')->group(function () {
  Route::get('/', [\App\Http\Controllers\Kasir\PembayaranKreditController::class, 'index'])->name('index');
  Route::get('/create/{id_pelanggan}', [\App\Http\Controllers\Kasir\PembayaranKreditController::class, 'create'])->name('create');
  Route::post('/', [\App\Http\Controllers\Kasir\PembayaranKreditController::class, 'store'])->name('store');
  Route::get('/{id}', [\App\Http\Controllers\Kasir\PembayaranKreditController::class, 'show'])->name('show');
  Route::get('/{id_pelanggan}/history', [\App\Http\Controllers\Kasir\PembayaranKreditController::class, 'history'])->name('history');
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
