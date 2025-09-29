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
                'login' => 'Kredensial yang diberikan tidak cocok dengan catatan kami.',
            ]);
        }

        // Update terakhir login
        $user->update(['terakhir_login' => now()]);

        Auth::login($user, $request->boolean('remember'));

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        Log::info('Logout attempt', [
            'user' => Auth::user()?->id_pengguna,
            'session_id' => session()->getId(),
            'csrf_token' => $request->header('X-CSRF-TOKEN'),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('Logout successful');

        return redirect()->route('home');
    }
}
