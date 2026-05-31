<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\KontrakKredit;
use App\Models\Pembayaran;
use App\Models\Transaksi;
use Carbon\Carbon;

// Simulate a logged-in kasir (pick first kasir user)
$kasir = App\Models\User::where('role', 'kasir')->first();
if (!$kasir) {
    echo "No kasir user found!" . PHP_EOL;
    exit(1);
}
Auth::login($kasir);
echo "Logged in as: " . $kasir->nama . PHP_EOL;

$kontrak = KontrakKredit::with([
    'jadwalAngsuran' => fn($q) => $q->orderBy('periode_ke'),
    'pelanggan',
    'transaksi',
])->findOrFail(34);

DB::beginTransaction();
try {
    $remaining = 2000.0;
    $kasirId = $kasir->id_pengguna;

    foreach ($kontrak->jadwalAngsuran as $angsuran) {
        if ($remaining <= 0) break;
        if ($angsuran->status === 'PAID') continue;

        $sisa = floatval($angsuran->jumlah_tagihan) - floatval($angsuran->jumlah_dibayar);
        if ($sisa <= 0) {
            $angsuran->status = 'PAID';
            $angsuran->paid_at = now();
            $angsuran->save();
            continue;
        }

        $bayar = min($remaining, $sisa);

        $pembayaran = Pembayaran::create([
            'id_pembayaran' => Pembayaran::generateIdPembayaran(),
            'id_transaksi' => $kontrak->nomor_transaksi,
            'id_angsuran' => $angsuran->id_angsuran,
            'id_pelanggan' => $kontrak->id_pelanggan,
            'id_kasir' => $kasirId,
            'metode' => 'TUNAI',
            'tipe_pembayaran' => 'kredit',
            'jumlah' => $bayar,
            'tanggal' => now(),
            'keterangan' => 'Test bayar kontrak GAGAL',
        ]);

        echo "Created pembayaran: " . $pembayaran->id_pembayaran . " bayar=" . $bayar . PHP_EOL;

        $angsuran->jumlah_dibayar = floatval($angsuran->jumlah_dibayar) + $bayar;
        if ($angsuran->jumlah_dibayar >= $angsuran->jumlah_tagihan) {
            $angsuran->status = 'PAID';
            $angsuran->paid_at = now();
        } elseif ($angsuran->jatuh_tempo < Carbon::today()) {
            $angsuran->status = 'LATE';
        } else {
            $angsuran->status = 'DUE';
        }
        $angsuran->save();
        echo "Updated angsuran #" . $angsuran->periode_ke . " status=" . $angsuran->status . PHP_EOL;

        $remaining -= $bayar;
    }

    // Reactivate GAGAL contract
    if ($kontrak->status === 'GAGAL') {
        $kontrak->status = 'AKTIF';
        $kontrak->save();
        echo "Contract reactivated to AKTIF" . PHP_EOL;
    }

    DB::rollBack(); // ROLLBACK so we don't actually change data
    echo PHP_EOL . "TEST PASSED - rolling back changes" . PHP_EOL;

} catch (\Throwable $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
