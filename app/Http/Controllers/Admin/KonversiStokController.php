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
      'mode' => 'required|in:penuh,parsial',
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
      'mode.required' => 'Mode konversi harus dipilih',
      'mode.in' => 'Mode konversi tidak valid',
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

      // Calculate stok changes berdasarkan mode
      if ($validated['mode'] === 'penuh') {
        // Mode PENUH: stok karton berkurang sesuai qty_from, stok pcs bertambah qty_from * rasio
        $stokKartonBerkurang = $validated['qty_from'];
        $stokPcsBertambah = $validated['qty_from'] * $validated['rasio'];
      } else {
        // Mode PARSIAL: stok karton berkurang proporsional, stok pcs bertambah qty_to
        // Contoh: 1 karton = 144 pcs (isi_per_pack), ambil 10 pcs => berkurang 10/144 = 0.069 karton
        $isiPerPack = $produkAsal->isi_per_pack;
        $stokKartonBerkurang = round($validated['qty_to'] / $isiPerPack, 3);
        $stokPcsBertambah = $validated['qty_to'];
      }

      // Update stok produk asal (kurangi)
      $produkAsal->stok = max(0, $produkAsal->stok - $stokKartonBerkurang);
      $produkAsal->save();

      // Update stok produk tujuan (tambah)
      $produkTujuan->increment('stok', $stokPcsBertambah);

      DB::commit();

      $modeLabel = $validated['mode'] === 'penuh' ? 'penuh' : 'parsial';
      return redirect()
        ->route('admin.konversi-stok.index')
        ->with('success', "Konversi stok ({$modeLabel}) berhasil! {$stokKartonBerkurang} {$produkAsal->satuan} {$produkAsal->nama} → {$stokPcsBertambah} {$produkTujuan->satuan} {$produkTujuan->nama}");
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

      // Get produk asal dan tujuan
      $produkAsal = Produk::findOrFail($konversi->from_produk_id);
      $produkTujuan = Produk::findOrFail($konversi->to_produk_id);

      DB::beginTransaction();

      // Reverse stok changes berdasarkan mode
      if ($konversi->mode === 'penuh') {
        // Mode PENUH: restore stok karton sesuai qty_from, kurangi stok pcs qty_from * rasio
        $stokKartonBertambah = $konversi->qty_from;
        $stokPcsBerkurang = $konversi->qty_from * $konversi->rasio;
      } else {
        // Mode PARSIAL: restore stok karton proporsional, kurangi stok pcs qty_to
        $isiPerPack = $produkAsal->isi_per_pack;
        $stokKartonBertambah = round($konversi->qty_to / $isiPerPack, 3);
        $stokPcsBerkurang = $konversi->qty_to;
      }

      // Update stok produk asal (tambah balik)
      $produkAsal->increment('stok', $stokKartonBertambah);

      // Update stok produk tujuan (kurangi)
      $produkTujuan->stok = max(0, $produkTujuan->stok - $stokPcsBerkurang);
      $produkTujuan->save();

      // Delete konversi record
      $konversi->delete();

      DB::commit();

      $modeLabel = $konversi->mode === 'penuh' ? 'penuh' : 'parsial';
      return redirect()
        ->route('admin.konversi-stok.index')
        ->with('success', "Konversi stok ({$modeLabel}) berhasil dihapus! Stok sudah dikembalikan: {$stokKartonBertambah} {$produkAsal->satuan} {$produkAsal->nama} ↔ {$stokPcsBerkurang} {$produkTujuan->satuan} {$produkTujuan->nama}");
    } catch (\Exception $e) {
      DB::rollBack();

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
      DB::beginTransaction();

      // Get all konversi records yang akan dihapus
      $konversis = KonversiStok::whereIn('id_konversi', $request->ids)->get();

      // Reverse stock untuk setiap konversi
      foreach ($konversis as $konversi) {
        $produkAsal = Produk::findOrFail($konversi->from_produk_id);
        $produkTujuan = Produk::findOrFail($konversi->to_produk_id);

        // Reverse stok changes berdasarkan mode
        if ($konversi->mode === 'penuh') {
          $stokKartonBertambah = $konversi->qty_from;
          $stokPcsBerkurang = $konversi->qty_from * $konversi->rasio;
        } else {
          $isiPerPack = $produkAsal->isi_per_pack;
          $stokKartonBertambah = round($konversi->qty_to / $isiPerPack, 3);
          $stokPcsBerkurang = $konversi->qty_to;
        }

        // Update stok produk asal (tambah balik)
        $produkAsal->increment('stok', $stokKartonBertambah);

        // Update stok produk tujuan (kurangi)
        $produkTujuan->stok = max(0, $produkTujuan->stok - $stokPcsBerkurang);
        $produkTujuan->save();
      }

      // Delete semua konversi records
      KonversiStok::whereIn('id_konversi', $request->ids)->delete();

      DB::commit();

      return redirect()
        ->route('admin.konversi-stok.index')
        ->with('success', count($request->ids) . ' konversi stok berhasil dihapus dan stok sudah dikembalikan');
    } catch (\Exception $e) {
      DB::rollBack();

      return redirect()
        ->back()
        ->with('error', 'Gagal menghapus konversi stok: ' . $e->getMessage());
    }
  }
}
