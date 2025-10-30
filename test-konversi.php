<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Produk;
use App\Models\KonversiStok;
use App\Services\KonversiStokService;

echo "=== Test Konversi Stok dengan MySQL ===\n\n";

// Cari produk karton dan pcs
$produkKarton = Produk::where('satuan', 'karton')->first();
$produkPcs = Produk::where('satuan', 'pcs')->first();

if (!$produkKarton || !$produkPcs) {
    echo "ERROR: Tidak ada produk karton atau pcs di database!\n";
    exit(1);
}

echo "Produk Karton: {$produkKarton->nama}\n";
echo "  - SKU: {$produkKarton->sku}\n";
echo "  - Stok: {$produkKarton->stok} karton\n";
echo "  - Buffer: {$produkKarton->sisa_pcs_terbuka} pcs\n";
echo "  - Isi per pack: {$produkKarton->isi_per_pack} pcs\n\n";

echo "Produk PCS: {$produkPcs->nama}\n";
echo "  - SKU: {$produkPcs->sku}\n";
echo "  - Stok: {$produkPcs->stok} pcs\n\n";

// Test konversi
$service = app(KonversiStokService::class);

try {
    echo "--- Test 1: Konversi 100 PCS (Parsial) ---\n";
    $konversi = $service->convert(
        $produkKarton->id_produk,
        $produkPcs->id_produk,
        100,
        'parsial',
        $produkKarton->isi_per_pack,
        'Test konversi dari script'
    );

    echo "✓ Konversi berhasil!\n";
    echo "  - Karton dipakai: {$konversi->packs_used}\n";
    echo "  - Dari buffer: {$konversi->dari_buffer} pcs\n";
    echo "  - Buffer setelahnya: {$konversi->sisa_buffer_after} pcs\n\n";

    // Refresh data
    $produkKarton->refresh();
    $produkPcs->refresh();

    echo "Setelah konversi:\n";
    echo "  - Karton stok: {$produkKarton->stok}\n";
    echo "  - Karton buffer: {$produkKarton->sisa_pcs_terbuka} pcs\n";
    echo "  - PCS stok: {$produkPcs->stok} pcs\n\n";

    echo "--- Test 2: Reverse (Undo) Konversi ---\n";
    $service->reverse($konversi->id_konversi);

    $produkKarton->refresh();
    $produkPcs->refresh();

    echo "✓ Reverse berhasil!\n";
    echo "  - Karton stok: {$produkKarton->stok}\n";
    echo "  - Karton buffer: {$produkKarton->sisa_pcs_terbuka} pcs\n";
    echo "  - PCS stok: {$produkPcs->stok} pcs\n\n";

    echo "=== SEMUA TEST BERHASIL! ===\n";

} catch (Exception $e) {
    echo "ERROR: {$e->getMessage()}\n";
    exit(1);
}
