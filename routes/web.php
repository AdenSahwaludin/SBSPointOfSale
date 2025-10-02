<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Kasir\POSController;

// Root redirect - jika user sudah login redirect ke dashboard sesuai role, jika belum ke login
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        return redirect(match ($user->role) {
            'admin' => '/admin',
            'kasir' => '/kasir',
            default => '/dashboard'
        });
    }
    return redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::inertia('/login', 'Auth/Login')->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    // Dashboard routes
    Route::inertia('/dashboard', 'Dashboard')->name('dashboard');

    // Admin routes
    Route::prefix('admin')->middleware('auth')->group(function () {
        Route::inertia('/', 'Admin/Dashboard')->name('admin.dashboard');
        Route::inertia('/users', 'Admin/Users')->name('admin.users');
        Route::inertia('/products', 'Admin/Products')->name('admin.products');
        Route::inertia('/reports', 'Admin/Reports')->name('admin.reports');
        Route::inertia('/settings', 'Admin/Settings')->name('admin.settings');

        // Manajemen Pengguna
        Route::resource('pengguna', PenggunaController::class)->names([
            'index' => 'admin.pengguna.index',
            'create' => 'admin.pengguna.create',
            'store' => 'admin.pengguna.store',
            'show' => 'admin.pengguna.show',
            'edit' => 'admin.pengguna.edit',
            'update' => 'admin.pengguna.update',
            'destroy' => 'admin.pengguna.destroy',
        ]);

        // Reset password pengguna
        Route::post('pengguna/{id}/reset-password', [PenggunaController::class, 'resetPassword'])
            ->name('admin.pengguna.reset-password');
    });

    // Kasir routes
    Route::prefix('kasir')->middleware('auth')->group(function () {
        Route::inertia('/', 'Kasir/Dashboard')->name('kasir.dashboard');

        // Point of Sale
        Route::get('/pos', [POSController::class, 'index'])->name('kasir.pos');
        Route::post('/pos', [POSController::class, 'store'])->name('kasir.pos.store');

        Route::inertia('/transactions', 'Kasir/Transactions')->name('kasir.transactions');
        Route::get('/profile', [ProfileController::class, 'show'])->name('kasir.profile');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('kasir.profile.update');
        Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('kasir.profile.password');
    });

    // Midtrans callback
    Route::post('/midtrans/callback', [POSController::class, 'callback'])->name('midtrans.callback');

    // Logout
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::delete('/logout', [LoginController::class, 'destroy'])->name('logout.delete');
});