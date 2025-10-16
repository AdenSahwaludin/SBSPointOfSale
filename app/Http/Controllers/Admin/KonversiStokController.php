<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KonversiStok;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class KonversiStokController extends Controller
{
 /**
  * Display a listing of the resource.
  */
 public function index(Request $request): Response
 {
  $query = KonversiStok::with(['fromProduk', 'toProduk']);

  // Search functionality
  if ($request->has('search') && $request->search) {
   $search = $request->search;
   $query->where(function ($q) use ($search) {
    $q->whereHas('fromProduk', function ($q) use ($search) {
     $q->where('nama', 'like', "%{$search}%")
      ->orWhere('sku', 'like', "%{$search}%");
    })
     ->orWhereHas('toProduk', function ($q) use ($search) {
      $q->where('nama', 'like', "%{$search}%")
       ->orWhere('sku', 'like', "%{$search}%");
     })
     ->orWhere('keterangan', 'like', "%{$search}%");
   });
  }

  // Sorting
  $sortField = $request->get('sort_field', 'created_at');
  $sortDirection = $request->get('sort_direction', 'desc');
  $query->orderBy($sortField, $sortDirection);

  $konversiStok = $query->paginate(10)->withQueryString();

  return Inertia::render('Admin/KonversiStok/Index', [
   'konversiStok' => $konversiStok,
   'filters' => $request->only(['search', 'sort_field', 'sort_direction']),
  ]);
 }

 /**
  * Show the form for creating a new resource.
  */
 public function create(): Response
 {
  // Produk asal: hanya yang bukan pcs (karton, pack, dll)
  $produkAsal = Produk::with('kategori')
   ->where('satuan', '!=', 'pcs')
   ->orderBy('nama')
   ->get()
   ->map(function ($item) {
    return [
     'id_produk' => $item->id_produk,
     'nama' => $item->nama,
     'sku' => $item->sku,
     'satuan' => $item->satuan,
     'isi_per_pack' => $item->isi_per_pack,
     'stok' => $item->stok,
     'kategori' => $item->kategori?->nama,
    ];
   });

  // Produk tujuan: hanya yang pcs
  $produkTujuan = Produk::with('kategori')
   ->where('satuan', 'pcs')
   ->orderBy('nama')
   ->get()
   ->map(function ($item) {
    return [
     'id_produk' => $item->id_produk,
     'nama' => $item->nama,
     'sku' => $item->sku,
     'satuan' => $item->satuan,
     'isi_per_pack' => $item->isi_per_pack,
     'stok' => $item->stok,
     'kategori' => $item->kategori?->nama,
    ];
   });

  return Inertia::render('Admin/KonversiStok/Create', [
   'produkAsal' => $produkAsal,
   'produkTujuan' => $produkTujuan,
  ]);
 }

 /**
  * Store a newly created resource in storage.
  */
 public function store(Request $request): RedirectResponse
 {
  $validated = $request->validate([
   'from_produk_id' => 'required|exists:produk,id_produk',
   'to_produk_id' => 'required|exists:produk,id_produk|different:from_produk_id',
   'rasio' => 'required|integer|min:1',
   'qty_from' => 'required|integer|min:1',
   'qty_to' => 'required|integer|min:1',
   'keterangan' => 'nullable|string|max:200',
  ], [
   'from_produk_id.required' => 'Produk asal harus dipilih',
   'from_produk_id.exists' => 'Produk asal tidak valid',
   'to_produk_id.required' => 'Produk tujuan harus dipilih',
   'to_produk_id.exists' => 'Produk tujuan tidak valid',
   'to_produk_id.different' => 'Produk tujuan harus berbeda dengan produk asal',
   'rasio.required' => 'Rasio konversi harus diisi',
   'rasio.min' => 'Rasio konversi minimal 1',
   'qty_from.required' => 'Jumlah produk asal harus diisi',
   'qty_from.min' => 'Jumlah produk asal minimal 1',
   'qty_to.required' => 'Jumlah produk tujuan harus diisi',
   'qty_to.min' => 'Jumlah produk tujuan minimal 1',
  ]);

  try {
   // Get produk asal dan tujuan
   $produkAsal = Produk::findOrFail($validated['from_produk_id']);
   $produkTujuan = Produk::findOrFail($validated['to_produk_id']);

   // Cek stok produk asal mencukupi
   if ($produkAsal->stok < $validated['qty_from']) {
    return redirect()
     ->back()
     ->withInput()
     ->with('error', "Stok {$produkAsal->nama} tidak mencukupi. Stok tersedia: {$produkAsal->stok} {$produkAsal->satuan}");
   }

   // Start transaction
   DB::beginTransaction();

   // Create konversi record
   KonversiStok::create($validated);

   // Update stok produk asal (kurangi)
   $produkAsal->decrement('stok', $validated['qty_from']);

   // Update stok produk tujuan (tambah)
   $produkTujuan->increment('stok', $validated['qty_to']);

   DB::commit();

   return redirect()
    ->route('admin.konversi-stok.index')
    ->with('success', "Konversi stok berhasil! {$validated['qty_from']} {$produkAsal->satuan} {$produkAsal->nama} → {$validated['qty_to']} {$produkTujuan->satuan} {$produkTujuan->nama}");
  } catch (\Exception $e) {
   DB::rollBack();

   return redirect()
    ->back()
    ->withInput()
    ->with('error', 'Gagal menambahkan konversi stok: ' . $e->getMessage());
  }
 }

 /**
  * Display the specified resource.
  */
 public function show(string $id): Response
 {
  $konversi = KonversiStok::with(['fromProduk.kategori', 'toProduk.kategori'])
   ->findOrFail($id);

  return Inertia::render('Admin/KonversiStok/Show', [
   'konversi' => $konversi,
  ]);
 }

 /**
  * Show the form for editing the specified resource.
  */
 public function edit(string $id): Response
 {
  $konversi = KonversiStok::with(['fromProduk', 'toProduk'])->findOrFail($id);

  // Produk asal: hanya yang bukan pcs (karton, pack, dll)
  $produkAsal = Produk::with('kategori')
   ->where('satuan', '!=', 'pcs')
   ->orderBy('nama')
   ->get()
   ->map(function ($item) {
    return [
     'id_produk' => $item->id_produk,
     'nama' => $item->nama,
     'sku' => $item->sku,
     'satuan' => $item->satuan,
     'isi_per_pack' => $item->isi_per_pack,
     'stok' => $item->stok,
     'kategori' => $item->kategori?->nama,
    ];
   });

  // Produk tujuan: hanya yang pcs
  $produkTujuan = Produk::with('kategori')
   ->where('satuan', 'pcs')
   ->orderBy('nama')
   ->get()
   ->map(function ($item) {
    return [
     'id_produk' => $item->id_produk,
     'nama' => $item->nama,
     'sku' => $item->sku,
     'satuan' => $item->satuan,
     'isi_per_pack' => $item->isi_per_pack,
     'stok' => $item->stok,
     'kategori' => $item->kategori?->nama,
    ];
   });

  return Inertia::render('Admin/KonversiStok/Edit', [
   'konversi' => $konversi,
   'produkAsal' => $produkAsal,
   'produkTujuan' => $produkTujuan,
  ]);
 }

 /**
  * Update the specified resource in storage.
  */
 public function update(Request $request, string $id): RedirectResponse
 {
  $konversi = KonversiStok::findOrFail($id);

  $validated = $request->validate([
   'from_produk_id' => 'required|exists:produk,id_produk',
   'to_produk_id' => 'required|exists:produk,id_produk|different:from_produk_id',
   'rasio' => 'required|integer|min:1',
   'qty_from' => 'required|integer|min:1',
   'qty_to' => 'required|integer|min:1',
   'keterangan' => 'nullable|string|max:200',
  ], [
   'from_produk_id.required' => 'Produk asal harus dipilih',
   'from_produk_id.exists' => 'Produk asal tidak valid',
   'to_produk_id.required' => 'Produk tujuan harus dipilih',
   'to_produk_id.exists' => 'Produk tujuan tidak valid',
   'to_produk_id.different' => 'Produk tujuan harus berbeda dengan produk asal',
   'rasio.required' => 'Rasio konversi harus diisi',
   'rasio.min' => 'Rasio konversi minimal 1',
   'qty_from.required' => 'Jumlah produk asal harus diisi',
   'qty_from.min' => 'Jumlah produk asal minimal 1',
   'qty_to.required' => 'Jumlah produk tujuan harus diisi',
   'qty_to.min' => 'Jumlah produk tujuan minimal 1',
  ]);

  try {
   $konversi->update($validated);

   return redirect()
    ->route('admin.konversi-stok.index')
    ->with('success', 'Konversi stok berhasil diperbarui');
  } catch (\Exception $e) {
   return redirect()
    ->back()
    ->withInput()
    ->with('error', 'Gagal memperbarui konversi stok: ' . $e->getMessage());
  }
 }

 /**
  * Remove the specified resource from storage.
  */
 public function destroy(string $id): RedirectResponse
 {
  try {
   $konversi = KonversiStok::findOrFail($id);
   $konversi->delete();

   return redirect()
    ->route('admin.konversi-stok.index')
    ->with('success', 'Konversi stok berhasil dihapus');
  } catch (\Exception $e) {
   return redirect()
    ->back()
    ->with('error', 'Gagal menghapus konversi stok: ' . $e->getMessage());
  }
 }

 /**
  * Bulk delete konversi stok
  */
 public function bulkDelete(Request $request): RedirectResponse
 {
  $request->validate([
   'ids' => 'required|array',
   'ids.*' => 'exists:konversi_stok,id_konversi',
  ]);

  try {
   KonversiStok::whereIn('id_konversi', $request->ids)->delete();

   return redirect()
    ->route('admin.konversi-stok.index')
    ->with('success', count($request->ids) . ' konversi stok berhasil dihapus');
  } catch (\Exception $e) {
   return redirect()
    ->back()
    ->with('error', 'Gagal menghapus konversi stok: ' . $e->getMessage());
  }
 }
}
