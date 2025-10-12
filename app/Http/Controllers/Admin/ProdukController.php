<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produk = Produk::with('kategori')->get();
        $kategori = Kategori::all();

        return Inertia::render('Admin/Produk/Index', [
            'produk' => $produk,
            'kategori' => $kategori
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategori = Kategori::all();

        return Inertia::render('Admin/Produk/Create', [
            'kategori' => $kategori,
            'nextPrefix' => 'PDK' // Simple prefix
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'suffix' => 'required|string|max:4',
            'nama' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id_kategori',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required|string|max:50',
        ]);

        // Generate ID
        $id_produk = 'PDK-' . $request->suffix;

        // Check if ID already exists
        if (Produk::where('id_produk', $id_produk)->exists()) {
            return back()->withErrors(['suffix' => 'ID Produk sudah ada, gunakan suffix yang lain.'])->withInput();
        }

        Produk::create([
            'id_produk' => $id_produk,
            'nama' => $request->nama,
            'id_kategori' => $request->kategori_id,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'satuan' => $request->satuan,
        ]);

        return redirect()->route('admin.produk.index')
            ->with('message', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $produk = Produk::with('kategori')->where('id_produk', $id)->firstOrFail();

        return Inertia::render('Admin/Produk/Show', [
            'produk' => $produk
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $produk = Produk::where('id_produk', $id)->firstOrFail();
        $kategori = Kategori::all();

        return Inertia::render('Admin/Produk/Edit', [
            'produk' => $produk,
            'kategori' => $kategori
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $produk = Produk::where('id_produk', $id)->firstOrFail();

        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id_kategori',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required|string|max:50',
        ]);

        $produk->update([
            'nama' => $request->nama,
            'id_kategori' => $request->kategori_id,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'satuan' => $request->satuan,
        ]);

        return redirect()->route('admin.produk.index')
            ->with('message', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $produk = Produk::where('id_produk', $id)->firstOrFail();
        $produk->delete();

        return redirect()->route('admin.produk.index')
            ->with('message', 'Produk berhasil dihapus.');
    }
}