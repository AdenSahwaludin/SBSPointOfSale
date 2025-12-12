<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Services\TrustScoreService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PelangganController extends Controller
{
    /**
     * Display a listing of the resource.
     * Kasir can view all customers, but trust_score and credit_limit are read-only.
     */
    public function index(Request $request)
    {
        $pelanggan = Pelanggan::query()
            ->when($request->search, function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('id_pelanggan', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy($request->get('sort_by', 'created_at'), $request->get('sort_order', 'desc'))
            ->paginate(10)
            ->withQueryString();

        // Auto-apply account age rule
        $pelanggan->getCollection()->each(function (Pelanggan $p) {
            TrustScoreService::applyAccountAgeRule($p);
        });

        return Inertia::render('Kasir/Pelanggan/Index', [
            'pelanggan' => $pelanggan,
            'filters' => $request->only(['search', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Kasir/Pelanggan/Create', [
            'nextId' => Pelanggan::generateNextId(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * Kasir can only set basic customer fields. trust_score and credit_limit are NOT editable.
     */
    public function store(Request $request)
    {
        // Kasir can only create customers with specific fields
        $validated = $request->validate([
            'id_pelanggan' => 'required|string|max:7|unique:pelanggan,id_pelanggan|regex:/^P[0-9]{3,6}$/',
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|max:100|unique:pelanggan,email',
            'telepon' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
        ]);

        // Only allow specific fields. Don't allow trust_score, credit_limit, kota, aktif
        Pelanggan::create($validated);

        return redirect()->route('kasir.customers.index')
            ->with('success', 'Pelanggan berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        TrustScoreService::applyAccountAgeRule($pelanggan);

        return Inertia::render('Kasir/Pelanggan/Show', [
            'pelanggan' => $pelanggan,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        TrustScoreService::applyAccountAgeRule($pelanggan);

        return Inertia::render('Kasir/Pelanggan/Edit', [
            'pelanggan' => $pelanggan,
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Kasir can only update basic customer fields.
     * trust_score and credit_limit are NOT editable by kasir.
     */
    public function update(Request $request, string $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        // Kasir can only update specific fields
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|max:100|unique:pelanggan,email,'.$id.',id_pelanggan',
            'telepon' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
        ]);

        // Only update allowed fields. Block trust_score, credit_limit, kota, aktif
        $pelanggan->update($validated);

        return redirect()->route('kasir.customers.index')
            ->with('success', 'Pelanggan berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        // Check if pelanggan has transactions
        if ($pelanggan->transaksi()->exists()) {
            return back()->with('error', 'Pelanggan tidak dapat dihapus karena memiliki transaksi');
        }

        $pelanggan->delete();

        return redirect()->route('kasir.customers.index')
            ->with('success', 'Pelanggan berhasil dihapus');
    }
}
