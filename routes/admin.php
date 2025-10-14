<?php

use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\PelangganController;
use Illuminate\Support\Facades\Route;

// Admin routes (Admin role only)
Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
 // Dashboard
 Route::inertia('/', 'Admin/Dashboard')->name('dashboard');

 // Quick access routes (for navigation)
 Route::inertia('/users', 'Admin/Users')->name('users');
 Route::inertia('/produk', 'Admin/Produk')->name('produk');
 Route::inertia('/reports', 'Admin/Reports')->name('reports');
 Route::inertia('/settings', 'Admin/Settings')->name('settings');

 // ==========================================
 // MANAJEMEN PENGGUNA
 // ==========================================
 Route::resource('pengguna', PenggunaController::class)->names([
  'index' => 'pengguna.index',
  'create' => 'pengguna.create',
  'store' => 'pengguna.store',
  'show' => 'pengguna.show',
  'edit' => 'pengguna.edit',
  'update' => 'pengguna.update',
  'destroy' => 'pengguna.destroy',
 ]);

 // Reset password pengguna
 Route::post('pengguna/{id}/reset-password', [PenggunaController::class, 'resetPassword'])
  ->name('pengguna.reset-password');

 // ==========================================
 // MANAJEMEN PRODUK
 // ==========================================
 Route::resource('produk', ProdukController::class)->names([
  'index' => 'produk.index',
  'create' => 'produk.create',
  'store' => 'produk.store',
  'show' => 'produk.show',
  'edit' => 'produk.edit',
  'update' => 'produk.update',
  'destroy' => 'produk.destroy',
 ]);

 // Bulk actions untuk produk
 Route::post('produk/bulk-action', [ProdukController::class, 'bulkAction'])
  ->name('produk.bulk-action');

 // ==========================================
 // MANAJEMEN KATEGORI
 // ==========================================
 Route::resource('kategori', KategoriController::class);
 Route::post('kategori/bulk-action', [KategoriController::class, 'bulkAction'])
  ->name('kategori.bulk-action');

 // ==========================================
 // MANAJEMEN PELANGGAN
 // ==========================================
 Route::resource('pelanggan', PelangganController::class)->names([
  'index' => 'pelanggan.index',
  'create' => 'pelanggan.create',
  'store' => 'pelanggan.store',
  'show' => 'pelanggan.show',
  'edit' => 'pelanggan.edit',
  'update' => 'pelanggan.update',
  'destroy' => 'pelanggan.destroy',
 ]);

 // ==========================================
 // LAPORAN & ANALISIS (untuk masa depan)
 // ==========================================
 // Route::get('/laporan/penjualan', [LaporanController::class, 'penjualan'])->name('laporan.penjualan');
 // Route::get('/laporan/stok', [LaporanController::class, 'stok'])->name('laporan.stok');
});