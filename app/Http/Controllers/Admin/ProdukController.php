<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10); // Default 10 items per page
        $page = $request->get('page', 1);

        $produk = Produk::with('kategori')
            ->paginate($perPage)
            ->withQueryString(); // Preserve query parameters

        $kategori = Kategori::all();

        // Calculate overall stats (not just current page)
        $allProduk = Produk::all();
        $stats = [
            'total' => $allProduk->count(),
            'stokHabis' => $allProduk->where('stok', 0)->count(),
            'stokRendah' => $allProduk->where('stok', '>', 0)->where('stok', '<=', 10)->count(),
            'stokTersedia' => $allProduk->where('stok', '>', 10)->count(),
        ];

        return Inertia::render('Admin/Produk/Index', [
            'produk' => $produk,
            'kategori' => $kategori,
            'stats' => $stats,
            'filters' => [
                'per_page' => $perPage,
                'page' => $page,
            ],
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
            'nextPrefix' => 'PDK', // Simple prefix
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
        $id_produk = 'PDK-'.$request->suffix;

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
            'produk' => $produk,
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
            'kategori' => $kategori,
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

    /**
     * Search produk with advanced scoring algorithm
     */
    public function searchProduk(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return response()->json([]);
        }

        $searchTerm = strtolower(trim($query));

        // Ambil semua produk
        $produk = Produk::with('kategori')
            ->get()
            ->map(function ($p) use ($searchTerm) {
                $score = 0;

                // Field values untuk scoring
                $nama = strtolower($p->nama);
                $sku = strtolower($p->sku ?? $p->id_produk);
                $barcode = strtolower($p->barcode ?? '');
                $kategori = strtolower($p->kategori->nama ?? '');
                $idProduk = strtolower($p->id_produk);

                // 1. Exact match (highest priority)
                if ($barcode === $searchTerm) {
                    $score += 1000; // Barcode exact match
                }
                if ($sku === $searchTerm) {
                    $score += 900; // SKU exact match
                }
                if ($idProduk === $searchTerm) {
                    $score += 850; // ID Produk exact match
                }
                if ($nama === $searchTerm) {
                    $score += 800; // Nama exact match
                }

                // 2. Starts with (high priority)
                if (str_starts_with($barcode, $searchTerm)) {
                    $score += 700;
                }
                if (str_starts_with($sku, $searchTerm)) {
                    $score += 600;
                }
                if (str_starts_with($idProduk, $searchTerm)) {
                    $score += 550;
                }
                if (str_starts_with($nama, $searchTerm)) {
                    $score += 500;
                }

                // 3. Contains (medium priority)
                if (str_contains($barcode, $searchTerm)) {
                    $score += 400;
                }
                if (str_contains($sku, $searchTerm)) {
                    $score += 300;
                }
                if (str_contains($idProduk, $searchTerm)) {
                    $score += 250;
                }
                if (str_contains($nama, $searchTerm)) {
                    $score += 200;
                }
                if (str_contains($kategori, $searchTerm)) {
                    $score += 100;
                }

                // 4. Word-by-word search (untuk query multi-kata)
                $searchWords = explode(' ', $searchTerm);
                foreach ($searchWords as $word) {
                    if (strlen($word) > 2) { // Skip kata pendek
                        if (str_contains($nama, $word)) {
                            $score += 50;
                        }
                        if (str_contains($sku, $word)) {
                            $score += 40;
                        }
                        if (str_contains($idProduk, $word)) {
                            $score += 35;
                        }
                    }
                }

                // 5. Fuzzy matching untuk typo tolerance
                // Hitung similarity menggunakan similar_text untuk nama
                $nameWords = explode(' ', $nama);
                foreach ($nameWords as $nameWord) {
                    if (strlen($nameWord) > 3 && strlen($searchTerm) > 3) {
                        $similarity = similar_text($searchTerm, $nameWord);
                        if ($similarity > 2) {
                            $score += $similarity * 10;
                        }
                    }
                }

                return [
                    'id_produk' => $p->id_produk,
                    'sku' => $p->sku ?? $p->id_produk,
                    'barcode' => $p->barcode,
                    'nama' => $p->nama,
                    'harga' => $p->harga,
                    'harga_pack' => $p->harga_pack,
                    'stok' => $p->stok,
                    'satuan' => $p->satuan,
                    'isi_per_pack' => $p->isi_per_pack,
                    'kategori' => $p->kategori,
                    'created_at' => $p->created_at,
                    '_score' => $score, // Internal scoring
                ];
            })
            ->filter(function ($item) {
                return $item['_score'] > 0; // Hanya ambil yang match
            })
            ->sortByDesc('_score') // Sort by relevance
            ->take(20) // Limit hasil lebih banyak untuk admin
            ->values()
            ->map(function ($item) {
                unset($item['_score']); // Remove internal score dari response

                return $item;
            });

        return response()->json($produk);
    }
}
