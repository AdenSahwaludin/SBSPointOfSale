<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
  public function index()
  {
    $pengguna = User::orderBy('nama')->get();

    return Inertia::render('Admin/Pengguna/Index', [
      'pengguna' => $pengguna
    ]);
  }

  /**
   * Generate next 3-digit numeric prefix for id_pengguna
   */
  private function generateNextPrefix(): string
  {
    // Get last id_pengguna (e.g., '001-ABCD')
    $lastId = User::orderBy('id_pengguna', 'desc')->value('id_pengguna');
    if (!$lastId) {
      return '001';
    }
    // Extract numeric prefix (first 3 chars)
    $lastNum = intval(substr($lastId, 0, 3));
    // Increment and pad
    $next = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
    return $next;
  }

  public function create()
  {
    // Generate next numeric prefix (3 digits)
    $prefix = $this->generateNextPrefix();
    return Inertia::render('Admin/Pengguna/Create', [
      'nextPrefix' => $prefix,
    ]);
  }

  public function store(Request $request)
  {
    // Validate 4-character suffix (alphanumeric)
    $validated = $request->validate([
      'suffix' => 'required|alpha_num|size:4',
      'nama' => 'required|string|max:255',
      'email' => 'required|email|unique:pengguna,email',
      'telepon' => 'nullable|numeric|digits_between:1,15',
      'password' => 'required|string|min:6',
      'role' => 'required|in:admin,kasir'
    ]);

    // Build full ID: prefix and uppercase suffix
    $prefix = $this->generateNextPrefix();
    $idPengguna = $prefix . '-' . strtoupper($validated['suffix']);
    // Ensure unique
    if (User::where('id_pengguna', $idPengguna)->exists()) {
      return back()->withErrors(['suffix' => 'ID pengguna sudah ada, coba ulangi.'])->withInput();
    }
    // Prepare data for creation
    unset($validated['suffix']);
    $validated['id_pengguna'] = $idPengguna;
    User::create($validated);

    return redirect()->route('admin.pengguna.index')
      ->with('success', 'Pengguna berhasil ditambahkan');
  }

  public function show($id)
  {
    $pengguna = User::where('id_pengguna', $id)->firstOrFail();

    return Inertia::render('Admin/Pengguna/Show', [
      'pengguna' => $pengguna
    ]);
  }

  public function edit($id)
  {
    $pengguna = User::where('id_pengguna', $id)->firstOrFail();

    return Inertia::render('Admin/Pengguna/Edit', [
      'pengguna' => $pengguna
    ]);
  }

  public function update(Request $request, $id)
  {
    $pengguna = User::where('id_pengguna', $id)->firstOrFail();

    $validated = $request->validate([
      'nama' => 'required|string|max:255',
      'email' => ['required', 'email', Rule::unique('pengguna', 'email')->ignore($pengguna->id_pengguna, 'id_pengguna')],
      'telepon' => 'nullable|numeric|digits_between:1,15',
      'password' => 'nullable|string|min:6',
      'role' => 'required|in:admin,kasir'
    ]);

    // Hapus password dari validated jika kosong
    if (empty($validated['password'])) {
      unset($validated['password']);
    }

    $pengguna->update($validated);

    return redirect()->route('admin.pengguna.index')
      ->with('success', 'Pengguna berhasil diperbarui');
  }

  public function destroy($id)
  {
    $pengguna = User::where('id_pengguna', $id)->firstOrFail();

    // Tidak bisa hapus diri sendiri
    if ($pengguna->id_pengguna === FacadesAuth::user()->id_pengguna) {
      return back()->with('error', 'Tidak dapat menghapus akun sendiri');
    }

    $pengguna->delete();

    return redirect()->route('admin.pengguna.index')
      ->with('success', 'Pengguna berhasil dihapus');
  }

  public function resetPassword($id)
  {
    $pengguna = User::where('id_pengguna', $id)->firstOrFail();

    // Tidak bisa reset password diri sendiri
    if ($pengguna->id_pengguna === FacadesAuth::user()->id_pengguna) {
      return back()->with('error', 'Tidak dapat mereset password akun sendiri');
    }

    // Reset password ke default
    $pengguna->update([
      'password' => '123456' // Password default
    ]);

    return back()->with('success', 'Password berhasil direset ke default (123456)');
  }
}