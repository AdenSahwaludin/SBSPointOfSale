<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$contracts = App\Models\KontrakKredit::where('id_pelanggan', 'P002')->get();
foreach ($contracts as $k) {
    echo $k->nomor_kontrak . ' ' . $k->status . PHP_EOL;
}

$p = App\Models\Pelanggan::find('P002');
echo "Score: " . $p->trust_score . " Limit: " . $p->credit_limit . PHP_EOL;

$calculation = App\Services\CreditLimitService::calculateCreditLimit($p);
print_r($calculation);
