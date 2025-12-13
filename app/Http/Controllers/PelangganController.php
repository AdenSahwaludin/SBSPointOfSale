<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Services\TrustScoreService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PelangganController extends Controller
{
    protected string $userRole;

    protected string $viewPrefix;

    protected string $routePrefix;

    public function __construct()
    {
        $user = auth()->user();
        if ($user && $user->role === 'admin') {
            $this->userRole = 'admin';
            $this->viewPrefix = 'Admin/Pelanggan/';
            $this->routePrefix = 'admin.pelanggan';
        } else {
            $this->userRole = 'kasir';
            $this->viewPrefix = 'Kasir/Pelanggan/';
            $this->routePrefix = 'kasir.customers';
        }
    }

    /**
     * Display a listing of the resource.
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

        // Auto-apply account age rule so trust_score increases after 30/180 days
        $pelanggan->getCollection()->each(function (Pelanggan $p) {
            TrustScoreService::applyAccountAgeRule($p);
        });

        return Inertia::render($this->viewPrefix.'Index', [
            'pelanggan' => $pelanggan,
            'filters' => $request->only(['search', 'sort_by', 'sort_order']),
            'userRole' => $this->userRole,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render($this->viewPrefix.'Create', [
            'nextId' => Pelanggan::generateNextId(),
            'userRole' => $this->userRole,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Determine validation rules based on role
        $rules = [
            'id_pelanggan' => 'required|string|max:7|unique:pelanggan,id_pelanggan|regex:/^P[0-9]{3,6}$/',
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|max:100|unique:pelanggan,email',
            'telepon' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
        ];

        // Admin can set additional fields
        if ($this->userRole === 'admin') {
            $rules['kota'] = 'nullable|string|max:50';
            $rules['aktif'] = 'boolean';
            $rules['trust_score'] = 'integer|min:0|max:100';
            $rules['credit_limit'] = 'numeric|min:0';
        }

        $validated = $request->validate($rules);

        // Filter data based on role
        $data = $this->filterFieldsByRole($validated);

        Pelanggan::create($data);

        return redirect()->route("{$this->routePrefix}.index")
            ->with('success', 'Pelanggan berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        TrustScoreService::applyAccountAgeRule($pelanggan);

        // Get customer transactions
        $transaksi = $pelanggan->transaksi()
            ->with('detail')
            ->orderBy('tanggal', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render($this->viewPrefix.'Show', [
            'pelanggan' => $pelanggan,
            'transaksi' => $transaksi,
            'userRole' => $this->userRole,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);
        TrustScoreService::applyAccountAgeRule($pelanggan);

        return Inertia::render($this->viewPrefix.'Edit', [
            'pelanggan' => $pelanggan,
            'userRole' => $this->userRole,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        // Determine validation rules based on role
        $rules = [
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|max:100|unique:pelanggan,email,'.$id.',id_pelanggan',
            'telepon' => 'nullable|string|max:15',
            'alamat' => 'nullable|string',
        ];

        // Admin can update additional fields
        if ($this->userRole === 'admin') {
            $rules['kota'] = 'nullable|string|max:50';
            $rules['aktif'] = 'boolean';
            $rules['trust_score'] = 'integer|min:0|max:100';
            $rules['credit_limit'] = 'numeric|min:0';
        }

        $validated = $request->validate($rules);

        // Filter data based on role
        $data = $this->filterFieldsByRole($validated);

        $pelanggan->update($data);

        return redirect()->route("{$this->routePrefix}.index")
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

        return redirect()->route("{$this->routePrefix}.index")
            ->with('success', 'Pelanggan berhasil dihapus');
    }

    /**
     * Filter fields based on user role
     * Admin: all fields
     * Kasir: restricted fields only
     */
    protected function filterFieldsByRole(array $data): array
    {
        if ($this->userRole === 'kasir') {
            return [
                'id_pelanggan' => $data['id_pelanggan'] ?? null,
                'nama' => $data['nama'] ?? null,
                'email' => $data['email'] ?? null,
                'telepon' => $data['telepon'] ?? null,
                'alamat' => $data['alamat'] ?? null,
            ];
        }

        return $data;
    }
}
