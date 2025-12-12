<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class KategoriController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Kategori::withCount('produk');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('nama', 'like', "%{$search}%");
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'nama');
        $sortOrder = $request->get('sort_order', 'asc');

        if (in_array($sortBy, ['nama', 'created_at', 'produk_count'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $kategori = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/Kategori/Index', [
            'kategori' => $kategori,
            'filters' => [
                'search' => $request->search,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Kategori/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_kategori' => 'required|string|max:4|unique:kategori,id_kategori|regex:/^[A-Z0-9]{2,4}$/',
            'nama' => 'required|string|max:50|unique:kategori,nama',
        ], [
            'id_kategori.required' => 'ID kategori wajib diisi.',
            'id_kategori.max' => 'ID kategori maksimal 4 karakter.',
            'id_kategori.unique' => 'ID kategori sudah ada.',
            'id_kategori.regex' => 'ID kategori harus terdiri dari 2-4 karakter huruf besar dan angka.',
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.max' => 'Nama kategori maksimal 50 karakter.',
            'nama.unique' => 'Nama kategori sudah ada.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            Kategori::create([
                'id_kategori' => strtoupper($request->id_kategori),
                'nama' => $request->nama,
            ]);

            return redirect()->route('admin.kategori.index')
                ->with('success', 'Kategori berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan kategori.'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $kategori = Kategori::with([
            'produk' => function ($query) {
                $query->select('id_produk', 'nama', 'sku', 'harga', 'stok', 'id_kategori')
                    ->orderBy('nama');
            },
        ])->findOrFail($id);

        return Inertia::render('Admin/Kategori/Show', [
            'kategori' => $kategori,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $kategori = Kategori::findOrFail($id);

        return Inertia::render('Admin/Kategori/Edit', [
            'kategori' => $kategori,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $kategori = Kategori::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama' => [
                'required',
                'string',
                'max:50',
                Rule::unique('kategori', 'nama')->ignore($kategori->id_kategori, 'id_kategori'),
            ],
        ], [
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.max' => 'Nama kategori maksimal 50 karakter.',
            'nama.unique' => 'Nama kategori sudah ada.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $kategori->update([
                'nama' => $request->nama,
            ]);

            return redirect()->route('admin.kategori.index')
                ->with('success', 'Kategori berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan saat memperbarui kategori.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $kategori = Kategori::findOrFail($id);

        // Check if category has products
        if ($kategori->produk()->exists()) {
            return back()->withErrors([
                'delete' => 'Kategori tidak dapat dihapus karena masih memiliki produk.',
            ]);
        }

        try {
            $kategori->delete();

            return redirect()->route('admin.kategori.index')
                ->with('success', 'Kategori berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menghapus kategori.']);
        }
    }

    /**
     * Handle bulk actions for categories
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:kategori,id_kategori',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $action = $request->action;
        $ids = $request->ids;

        try {
            switch ($action) {
                case 'delete':
                    // Check if any category has products
                    $kategoriesWithProducts = Kategori::whereIn('id_kategori', $ids)
                        ->whereHas('produk')
                        ->count();

                    if ($kategoriesWithProducts > 0) {
                        return back()->withErrors([
                            'bulk' => 'Beberapa kategori tidak dapat dihapus karena masih memiliki produk.',
                        ]);
                    }

                    Kategori::whereIn('id_kategori', $ids)->delete();
                    $message = 'Kategori terpilih berhasil dihapus.';
                    break;
            }

            return redirect()->route('admin.kategori.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan saat memproses aksi.']);
        }
    }
}
