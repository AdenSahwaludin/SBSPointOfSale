<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ==========================================
// ROOT REDIRECT
// ==========================================
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

// ==========================================
// PUBLIC ROUTES
// ==========================================
// Add any public routes here if needed
// Route::get('/about', function () {
//     return inertia('About');
// });

// ==========================================
// INCLUDE SEPARATE ROUTE FILES
// ==========================================
// Authentication routes (login, logout, profile)
require __DIR__.'/auth.php';

// Admin routes (admin dashboard, user management, product management, etc.)
require __DIR__.'/admin.php';

// Kasir routes (POS system, transactions, kasir profile, etc.)
require __DIR__.'/kasir.php';
