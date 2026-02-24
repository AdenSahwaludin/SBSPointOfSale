<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function show()
    {
        return Inertia::render('Admin/Settings', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pengguna,email,'.$user->id_pengguna.',id_pengguna',
            'telepon' => 'nullable|string|max:20',
        ]);

        // Update data user
        $userData = [
            'nama' => $request->nama,
            'email' => $request->email,
        ];

        // Tambah telepon jika ada
        if ($request->filled('telepon')) {
            $userData['telepon'] = $request->telepon;
        }

        $user->update($userData);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Verify current password
        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini tidak cocok.',
            ]);
        }

        // Update password
        $user->update(['password' => $request->password]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
