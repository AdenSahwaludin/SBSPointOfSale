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

 public function create()
 {
  return Inertia::render('Admin/Pengguna/Create');
 }

 public function store(Request $request)
 {
  $validated = $request->validate([
   'id_pengguna' => 'required|string|unique:pengguna,id_pengguna',
   'nama' => 'required|string|max:255',
   'email' => 'required|email|unique:pengguna,email',
   'telepon' => 'nullable|string|max:20',
   'password' => 'required|string|min:6',
   'role' => 'required|in:admin,kasir'
  ]);

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
   'telepon' => 'nullable|string|max:20',
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