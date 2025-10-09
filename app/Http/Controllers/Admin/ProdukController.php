<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Produk::with('kategori');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('id_produk', 'like', "%{$search}%")
                    ->orWhere('nomor_bpom', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('kategori') && $request->kategori) {
            $query->where('id_kategori', $request->kategori);
        }

        // Filter by stock status
        if ($request->has('stock_status') && $request->stock_status) {
            switch ($request->stock_status) {
                case 'low':
                    $query->lowStock();
                    break;
                case 'out':
                    $query->where('stok', 0);
                    break;
                case 'available':
                    $query->where('stok', '>', 0);
                    break;
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'nama');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $produk = $query->paginate(15)->withQueryString();
        $kategori = Kategori::all();

        return Inertia::render('Admin/Produk/Index', [
            'produk' => $produk,
            'kategori' => $kategori,
            'filters' => $request->only(['search', 'kategori', 'stock_status', 'sort_by', 'sort_order'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kategori = Kategori::all();

        return Inertia::render('Admin/Produk/Create', [
            'kategori' => $kategori
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_produk' => [
                'required',
                'string',
                'size:13',
                'regex:/^[0-9]{13}$/',
                Rule::unique('produk', 'id_produk')
            ],
            'nama' => 'required|string|max:255',
            'nomor_bpom' => 'nullable|string|max:100',
            'harga' => 'required|numeric|min:0',
            'biaya_produk' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'batas_stok' => 'required|integer|min:0',
            'satuan' => 'required|string|max:50',
            'satuan_pack' => 'nullable|string|max:50',
            'isi_per_pack' => 'nullable|integer|min:1',
            'harga_pack' => 'nullable|numeric|min:0',
            'min_beli_diskon' => 'nullable|integer|min:0',
            'harga_diskon_unit' => 'nullable|numeric|min:0',
            'harga_diskon_pack' => 'nullable|numeric|min:0',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // Handle image upload
        if ($request->hasFile('gambar')) {
            $image = $request->file('gambar');
            $imageName = $data['id_produk'] . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('produk', $imageName, 'public');
            $data['gambar'] = $imagePath;
        }

        Produk::create($data);

        return redirect()->route('admin.produk.index')
            ->with('message', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $produk = Produk::with(['kategori', 'transaksiDetail.transaksi'])
            ->where('id_produk', $id)
            ->firstOrFail();

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

        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'nomor_bpom' => 'nullable|string|max:100',
            'harga' => 'required|numeric|min:0',
            'biaya_produk' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'batas_stok' => 'required|integer|min:0',
            'satuan' => 'required|string|max:50',
            'satuan_pack' => 'nullable|string|max:50',
            'isi_per_pack' => 'nullable|integer|min:1',
            'harga_pack' => 'nullable|numeric|min:0',
            'min_beli_diskon' => 'nullable|integer|min:0',
            'harga_diskon_unit' => 'nullable|numeric|min:0',
            'harga_diskon_pack' => 'nullable|numeric|min:0',
            'id_kategori' => 'required|exists:kategori,id_kategori',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // Handle image upload
        if ($request->hasFile('gambar')) {
            // Delete old image if exists
            if ($produk->gambar && Storage::disk('public')->exists($produk->gambar)) {
                Storage::disk('public')->delete($produk->gambar);
            }

            $image = $request->file('gambar');
            $imageName = $produk->id_produk . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('produk', $imageName, 'public');
            $data['gambar'] = $imagePath;
        }

        $produk->update($data);

        return redirect()->route('admin.produk.index')
            ->with('message', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $produk = Produk::where('id_produk', $id)->firstOrFail();

        // Check if product has transactions
        if ($produk->transaksiDetail()->exists()) {
            return back()->withErrors([
                'delete' => 'Produk tidak dapat dihapus karena sudah memiliki transaksi.'
            ]);
        }

        // Delete image if exists
        if ($produk->gambar && Storage::disk('public')->exists($produk->gambar)) {
            Storage::disk('public')->delete($produk->gambar);
        }

        $produk->delete();

        return redirect()->route('admin.produk.index')
            ->with('message', 'Produk berhasil dihapus.');
    }

    /**
     * Bulk actions for products
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:delete,update_category,update_stock',
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:produk,id_produk',
            'category_id' => 'required_if:action,update_category|exists:kategori,id_kategori',
            'stock_adjustment' => 'required_if:action,update_stock|integer'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $action = $request->action;
        $ids = $request->ids;

        switch ($action) {
            case 'delete':
                // Check if any products have transactions
                $productsWithTransactions = Produk::whereIn('id_produk', $ids)
                    ->whereHas('transaksiDetail')
                    ->count();

                if ($productsWithTransactions > 0) {
                    return back()->withErrors([
                        'bulk' => 'Beberapa produk tidak dapat dihapus karena sudah memiliki transaksi.'
                    ]);
                }

                // Delete images and products
                $products = Produk::whereIn('id_produk', $ids)->get();
                foreach ($products as $product) {
                    if ($product->gambar && Storage::disk('public')->exists($product->gambar)) {
                        Storage::disk('public')->delete($product->gambar);
                    }
                }

                Produk::whereIn('id_produk', $ids)->delete();
                $message = count($ids) . ' produk berhasil dihapus.';
                break;

            case 'update_category':
                Produk::whereIn('id_produk', $ids)->update([
                    'id_kategori' => $request->category_id
                ]);
                $message = count($ids) . ' produk berhasil dipindahkan ke kategori lain.';
                break;

            case 'update_stock':
                Produk::whereIn('id_produk', $ids)->increment('stok', $request->stock_adjustment);
                $message = 'Stok ' . count($ids) . ' produk berhasil diperbarui.';
                break;
        }

        return redirect()->route('admin.produk.index')
            ->with('message', $message);
    }
}
