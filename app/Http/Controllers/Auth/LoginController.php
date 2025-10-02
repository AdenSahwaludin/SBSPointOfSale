<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cari user berdasarkan email atau id_pengguna
        $user = User::where('email', $request->login)
            ->orWhere('id_pengguna', $request->login)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => 'Email atau Password anda salah.',
            ]);
        }

        // Update terakhir login
        $loginTime = now();
        $updated = $user->update(['terakhir_login' => $loginTime]);

        Log::info('Login time update', [
            'user_id' => $user->id_pengguna,
            'login_time' => $loginTime,
            'update_success' => $updated,
            'user_terakhir_login' => $user->fresh()->terakhir_login
        ]);

        // Set remember duration: 1 day default, 7 weeks if remember me
        $remember = $request->boolean('remember');
        Auth::login($user, $remember);

        // Role-based redirect
        $redirectRoute = match ($user->role) {
            'admin' => '/admin',
            'kasir' => '/kasir',
            default => '/dashboard'
        };

        return redirect()->intended($redirectRoute);
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
