<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Guest routes (login/register)
Route::middleware('guest')->group(function () {
 Route::inertia('/login', 'Auth/Login')->name('login');
 Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

// Authenticated routes (available for all authenticated users)
Route::middleware('auth')->group(function () {
 // Profile management
 Route::prefix('auth')->name('auth.')->group(function () {
  Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
 });

 // Logout routes
 Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
 Route::delete('/logout', [LoginController::class, 'destroy'])->name('logout.delete');
});