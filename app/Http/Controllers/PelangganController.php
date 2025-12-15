<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Services\TrustScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PelangganController extends Controller
{
    protected string $userRole;

    protected string $viewPrefix;

    protected string $routePrefix;

    public function __construct()
    {

        /** @var \App\Models\User $user */
        $user = Auth::user();
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
            'telepon' => 'nullable|numeric|digits_between:1,15',
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
            'telepon' => 'nullable|numeric|digits_between:1,15',
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

        // Check if pelanggan has any transactions
        $transactionCount = $pelanggan->transaksi()->count();
        if ($transactionCount > 0) {
            return back()->with('error', "Pelanggan tidak dapat dihapus karena sudah memiliki {$transactionCount} riwayat transaksi. Data pelanggan dengan transaksi harus dipertahankan untuk keperluan audit dan pelaporan.");
        }

        // Check if pelanggan has active credit contracts
        $activeContracts = $pelanggan->kontrakKredit()->where('status', 'AKTIF')->count();
        if ($activeContracts > 0) {
            return back()->with('error', "Pelanggan tidak dapat dihapus karena memiliki {$activeContracts} kontrak kredit aktif. Silakan selesaikan atau batalkan kontrak terlebih dahulu.");
        }

        // Check if pelanggan has completed credit contracts
        $completedContracts = $pelanggan->kontrakKredit()->count();
        if ($completedContracts > 0) {
            return back()->with('error', "Pelanggan tidak dapat dihapus karena memiliki riwayat kontrak kredit ({$completedContracts} kontrak). Data pelanggan dengan riwayat kredit harus dipertahankan untuk keperluan audit.");
        }

        // Check if pelanggan has outstanding credit balance
        if ($pelanggan->saldo_kredit > 0) {
            $formattedBalance = number_format($pelanggan->saldo_kredit, 0, ',', '.');

            return back()->with('error', "Pelanggan tidak dapat dihapus karena masih memiliki saldo kredit outstanding sebesar Rp {$formattedBalance}. Silakan lunasi terlebih dahulu.");
        }

        // If all checks pass, proceed with deletion
        try {
            $pelanggan->delete();

            return redirect()->route("{$this->routePrefix}.index")
                ->with('success', "Pelanggan {$pelanggan->nama} berhasil dihapus dari sistem.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus pelanggan. Terjadi kesalahan: '.$e->getMessage());
        }
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
