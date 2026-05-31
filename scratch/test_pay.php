<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$kontrak = App\Models\KontrakKredit::with([
    'jadwalAngsuran' => fn($q) => $q->orderBy('periode_ke'),
    'pelanggan',
    'transaksi',
])->findOrFail(34);

echo "Status kontrak: " . $kontrak->status . PHP_EOL;
echo "Total jadwal angsuran: " . $kontrak->jadwalAngsuran->count() . PHP_EOL;
echo PHP_EOL;

$remaining = 2000.0;
$processable = 0;

foreach ($kontrak->jadwalAngsuran as $angsuran) {
    $sisa = floatval($angsuran->jumlah_tagihan) - floatval($angsuran->jumlah_dibayar);
    echo "#" . $angsuran->periode_ke . " status=" . $angsuran->status . " tagihan=" . $angsuran->jumlah_tagihan . " dibayar=" . $angsuran->jumlah_dibayar . " sisa=" . $sisa . PHP_EOL;
    if ($angsuran->status !== 'PAID' && $sisa > 0) {
        $processable++;
    }
}

echo PHP_EOL;
echo "Angsuran yang bisa diproses: " . $processable . PHP_EOL;

// Cek apakah Pembayaran model bisa generate ID
try {
    $id = App\Models\Pembayaran::generateIdPembayaran();
    echo "Generated ID Pembayaran: " . $id . PHP_EOL;
} catch (Exception $e) {
    echo "ERROR generateIdPembayaran: " . $e->getMessage() . PHP_EOL;
}
