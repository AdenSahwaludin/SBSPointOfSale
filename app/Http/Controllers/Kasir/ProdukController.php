<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProdukController extends Controller
{
    /**
     * Display a listing of products for kasir
     */
    public function index(Request $request): Response
    {
        $perPage = $request->get('per_page', 20);
        $search = $request->get('search');
        $kategoriId = $request->get('kategori');
        $stokStatus = $request->get('stok_status');

        $query = Produk::with('kategori')->orderBy('nama', 'asc');

        // Search by nama, barcode, or id_produk
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('id_produk', 'like', "%{$search}%");
            });
        }

        // Filter by kategori
        if ($kategoriId && $kategoriId !== 'all') {
            $query->where('id_kategori', $kategoriId);
        }

        // Filter by stok status
        if ($stokStatus) {
            switch ($stokStatus) {
                case 'tersedia':
                    $query->where('stok', '>', 10);
                    break;
                case 'rendah':
                    $query->whereBetween('stok', [1, 10]);
                    break;
                case 'habis':
                    $query->where('stok', '<=', 0);
                    break;
            }
        }

        $produk = $query->paginate($perPage)->withQueryString();

        // Get all categories for filter
        $kategori = \App\Models\Kategori::orderBy('nama', 'asc')->get();

        // Calculate stats
        $stats = [
            'total_produk' => Produk::count(),
            'stok_tersedia' => Produk::where('stok', '>', 10)->count(),
            'stok_rendah' => Produk::whereBetween('stok', [1, 10])->count(),
            'stok_habis' => Produk::where('stok', '<=', 0)->count(),
        ];

        return Inertia::render('Kasir/Produk/Index', [
            'produk' => $produk,
            'kategori' => $kategori,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'kategori' => $kategoriId,
                'stok_status' => $stokStatus,
            ],
        ]);
    }

    /**
     * Display the specified product
     */
    public function show(string $id): Response
    {
        $produk = Produk::with('kategori')->findOrFail($id);

        return Inertia::render('Kasir/Produk/Show', [
            'produk' => $produk,
        ]);
    }
}
