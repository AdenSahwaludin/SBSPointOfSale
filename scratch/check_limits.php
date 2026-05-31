<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pelangganList = App\Models\Pelanggan::orderBy('id_pelanggan')->get();
foreach ($pelangganList as $p) {
    $gagal = App\Models\KontrakKredit::where('id_pelanggan', $p->id_pelanggan)->where('status', 'GAGAL')->count();
    echo $p->id_pelanggan . ' ' . $p->nama . ' -> Score:' . $p->trust_score . ' Limit:Rp' . number_format($p->credit_limit) . ' GAGAL_contracts:' . $gagal . PHP_EOL;
}
