<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\KontrakKredit;
use App\Models\Transaksi;

$id = 31;
$kontrak = KontrakKredit::with(['jadwalAngsuran', 'pelanggan', 'transaksi'])->findOrFail($id);
echo "Kontrak: " . $kontrak->nomor_kontrak . " Status: " . $kontrak->status . PHP_EOL;
echo "Jadwal count: " . $kontrak->jadwalAngsuran->count() . PHP_EOL;

$jadwalStatuses = $kontrak->jadwalAngsuran->groupBy('status')->map->count()->toArray();
echo "Jadwal statuses: " . json_encode($jadwalStatuses) . PHP_EOL;

$trx = $kontrak->transaksi;
echo "Transaksi: " . ($trx ? $trx->nomor_transaksi . " ar_status=" . $trx->ar_status . " status_pembayaran=" . $trx->status_pembayaran : "NULL") . PHP_EOL;

// Test reactivate
DB::beginTransaction();
try {
    foreach ($kontrak->jadwalAngsuran as $angsuran) {
        if ($angsuran->status === 'VOID') {
            $newStatus = $angsuran->jatuh_tempo < now() ? 'LATE' : 'DUE';
            echo "Angsuran #" . $angsuran->periode_ke . " VOID -> " . $newStatus . PHP_EOL;
            $angsuran->status = $newStatus;
            $angsuran->save();
        }
    }

    $kontrak->status = 'AKTIF';
    $kontrak->save();

    if ($trx) {
        $trx->status_pembayaran = Transaksi::STATUS_MENUNGGU;
        $trx->ar_status = 'AKTIF';
        $trx->save();
    }

    DB::rollBack();
    echo "TEST PASSED - rolled back" . PHP_EOL;
} catch (\Throwable $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . PHP_EOL;
}
