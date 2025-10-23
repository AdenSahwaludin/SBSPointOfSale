<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class PembayaranKreditController extends Controller
{
  /**
   * Display list of customers with credit balance
   */
  public function index(Request $request)
  {
    $pelanggan = Pelanggan::query()
      ->where('status_kredit', '=', 'aktif')
      ->where('saldo_kredit', '>', 0)
      ->when($request->search, function ($query, $search) {
        $query->where('nama', 'like', "%{$search}%")
          ->orWhere('id_pelanggan', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%");
      })
      ->orderBy('created_at', 'desc')
      ->paginate(10)
      ->withQueryString();

    return Inertia::render('Kasir/PembayaranKredit/Index', [
      'pelanggan' => $pelanggan,
      'filters' => $request->only(['search'])
    ]);
  }

  /**
   * Show payment form for specific customer
   */
  public function create(string $id_pelanggan)
  {
    $pelanggan = Pelanggan::findOrFail($id_pelanggan);

    return Inertia::render('Kasir/PembayaranKredit/Create', [
      'pelanggan' => $pelanggan
    ]);
  }

  /**
   * Process credit payment
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'id_pelanggan' => 'required|string|exists:pelanggan,id_pelanggan',
      'jumlah_pembayaran' => 'required|numeric|min:1',
      'metode_pembayaran' => 'required|in:TUNAI,QRIS,TRANSFER BCA',
      'keterangan' => 'nullable|string|max:500',
    ]);

    $pelanggan = Pelanggan::findOrFail($validated['id_pelanggan']);

    // Validate payment amount doesn't exceed credit balance
    if ($pelanggan->saldo_kredit <= 0) {
      return back()->with('error', 'Pelanggan tidak memiliki saldo kredit');
    }

    if ($validated['jumlah_pembayaran'] > $pelanggan->saldo_kredit) {
      return back()->with('error', 'Jumlah pembayaran melebihi saldo kredit');
    }

    // Get kasir ID
    $kasirId = null;
    if (Auth::check()) {
      $kasirId = Auth::user()->id_pengguna;
    }

    // Generate pembayaran ID
    $idPembayaran = Pembayaran::generateIdPembayaran();

    // Create payment record in pembayaran table with tipe_pembayaran='kredit'
    $pembayaran = Pembayaran::create([
      'id_pembayaran' => $idPembayaran,
      'id_transaksi' => null,
      'id_angsuran' => null,
      'id_pelanggan' => $validated['id_pelanggan'],
      'id_kasir' => $kasirId,
      'metode' => $validated['metode_pembayaran'],
      'tipe_pembayaran' => 'kredit',
      'jumlah' => $validated['jumlah_pembayaran'],
      'tanggal' => now(),
      'keterangan' => $validated['keterangan'],
    ]);

    // Decrease customer's credit balance and increase available credit limit
    $pelanggan->saldo_kredit = max(0, (float)$pelanggan->saldo_kredit - (float)$validated['jumlah_pembayaran']);
    $pelanggan->credit_limit = (float)$pelanggan->credit_limit + (float)$validated['jumlah_pembayaran'];
    $pelanggan->status_kredit = $pelanggan->saldo_kredit > 0 ? 'aktif' : 'aktif';
    $pelanggan->save();

    // If all credit is fully paid, mark pending credit transactions as LUNAS
    if ((float)$pelanggan->saldo_kredit <= 0) {
      Transaksi::where('id_pelanggan', $pelanggan->id_pelanggan)
        ->where('jenis_transaksi', Transaksi::JENIS_KREDIT)
        ->where('status_pembayaran', Transaksi::STATUS_MENUNGGU)
        ->update([
          'status_pembayaran' => Transaksi::STATUS_LUNAS,
          'paid_at' => now(),
          'ar_status' => 'LUNAS',
        ]);
    }

    return redirect()->route('kasir.pembayaran-kredit.show', $pembayaran->id_pembayaran)
      ->with('success', 'Pembayaran kredit berhasil dicatat');
  }

  /**
   * Show payment receipt/details
   */
  public function show(string $id_pembayaran)
  {
    $pembayaran = Pembayaran::where('id_pembayaran', $id_pembayaran)
      ->where('tipe_pembayaran', 'kredit')
      ->with('pelanggan', 'kasir')
      ->firstOrFail();

    return Inertia::render('Kasir/PembayaranKredit/Show', [
      'pembayaran' => $pembayaran
    ]);
  }

  /**
   * Show customer credit history
   */
  public function history(string $id_pelanggan)
  {
    $pelanggan = Pelanggan::findOrFail($id_pelanggan);
    $pembayaran = Pembayaran::where('id_pelanggan', $id_pelanggan)
      ->where('tipe_pembayaran', 'kredit')
      ->orderBy('tanggal', 'desc')
      ->paginate(10);

    return Inertia::render('Kasir/PembayaranKredit/History', [
      'pelanggan' => $pelanggan,
      'pembayaran' => $pembayaran
    ]);
  }
}
