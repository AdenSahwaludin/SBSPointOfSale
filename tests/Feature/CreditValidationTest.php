<?php

namespace Tests\Feature;

use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\CreditSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a kasir user for all tests
        $this->actingAs(User::factory()->create([
            'role' => 'kasir',
        ]));
    }

    /**
     * Test: Pelanggan dengan status_kredit = nonaktif tidak dapat melakukan transaksi kredit
     */
    public function test_inactive_credit_customer_cannot_create_credit_transaction(): void
    {
        $pelanggan = Pelanggan::factory()->create([
            'status_kredit' => 'nonaktif',
            'credit_limit' => 5000000,
            'saldo_kredit' => 0,
        ]);

        $produk = Produk::factory()->create([
            'stok' => 100,
            'harga' => 100000,
        ]);

        $response = $this->post('/kasir/pos', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'items' => [
                [
                    'id_produk' => $produk->id_produk,
                    'jumlah' => 1,
                    'jenis_satuan' => 'unit',
                    'harga_satuan' => 100000,
                ],
            ],
            'subtotal' => 100000,
            'diskon' => 0,
            'pajak' => 0,
            'total' => 100000,
            'metode_bayar' => 'KREDIT',
            'dp' => 0,
            'tenor_bulan' => 12,
            'bunga_persen' => 5,
            'cicilan_bulanan' => 10000,
            'mulai_kontrak' => now()->toDateString(),
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('nonaktif', $response['message']);
    }

    /**
     * Test: Pelanggan dengan status_kredit = aktif dapat melakukan transaksi kredit
     */
    public function test_active_credit_customer_can_create_credit_transaction(): void
    {
        $pelanggan = Pelanggan::factory()->create([
            'status_kredit' => 'aktif',
            'credit_limit' => 5000000,
            'saldo_kredit' => 0,
        ]);

        $produk = Produk::factory()->create([
            'satuan' => 'pcs',
            'stok' => 100,
            'harga' => 100000,
        ]);

        $response = $this->post('/kasir/pos', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'items' => [
                [
                    'id_produk' => $produk->id_produk,
                    'jumlah' => 1,
                    'jenis_satuan' => 'unit',
                    'harga_satuan' => 100000,
                ],
            ],
            'subtotal' => 100000,
            'diskon' => 0,
            'pajak' => 0,
            'total' => 100000,
            'metode_bayar' => 'KREDIT',
            'dp' => 0,
            'tenor_bulan' => 12,
            'bunga_persen' => 5,
            'cicilan_bulanan' => 10000,
            'mulai_kontrak' => now()->toDateString(),
        ]);

        $response->assertSuccessful();
        $this->assertTrue($response['success']);
    }

    /**
     * Test: Sinkronisasi credit_limit dan saldo_kredit saat transaksi baru
     */
    public function test_credit_balance_sync_on_new_transaction(): void
    {
        $pelanggan = Pelanggan::factory()->create([
            'status_kredit' => 'aktif',
            'credit_limit' => 5000000,
            'saldo_kredit' => 0,
        ]);

        $produk = Produk::factory()->create([
            'satuan' => 'pcs',
            'stok' => 100,
            'harga' => 1000000,
        ]);

        $response = $this->post('/kasir/pos', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'items' => [
                [
                    'id_produk' => $produk->id_produk,
                    'jumlah' => 1,
                    'jenis_satuan' => 'unit',
                    'harga_satuan' => 1000000,
                ],
            ],
            'subtotal' => 1000000,
            'diskon' => 0,
            'pajak' => 0,
            'total' => 1000000,
            'metode_bayar' => 'KREDIT',
            'dp' => 0,
            'tenor_bulan' => 12,
            'bunga_persen' => 5,
            'cicilan_bulanan' => 100000,
            'mulai_kontrak' => now()->toDateString(),
        ]);

        $response->assertSuccessful();

        $pelanggan->refresh();

        // Kredit yang digunakan = 1000000 - 0 (dp) = 1000000
        $this->assertEquals(1000000, (float) $pelanggan->saldo_kredit);

        // Available limit = 5000000 - 1000000 = 4000000
        $this->assertEquals(4000000, (float) $pelanggan->credit_limit);
    }

    /**
     * Test: Sinkronisasi saldo saat transaksi LUNAS
     */
    public function test_credit_balance_sync_on_transaction_settled(): void
    {
        $pelanggan = Pelanggan::factory()->create([
            'status_kredit' => 'aktif',
            'credit_limit' => 4000000,
            'saldo_kredit' => 1000000,
        ]);

        $transaksi = Transaksi::factory()->create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'jenis_transaksi' => Transaksi::JENIS_KREDIT,
            'status_pembayaran' => Transaksi::STATUS_MENUNGGU,
            'total' => 1000000,
            'dp' => 0,
        ]);

        // Mark transaksi as LUNAS
        $this->patch("/kasir/transactions/{$transaksi->nomor_transaksi}/status", [
            'status_pembayaran' => Transaksi::STATUS_LUNAS,
        ]);

        $pelanggan->refresh();

        // Credit limit should be restored
        $this->assertEquals(5000000, (float) $pelanggan->credit_limit);

        // Saldo kredit should be reduced
        $this->assertEquals(0, (float) $pelanggan->saldo_kredit);
    }

    /**
     * Test: CreditSyncService validateCreditEligibility
     */
    public function test_credit_sync_service_validate_eligibility(): void
    {
        $activeCustomer = Pelanggan::factory()->create([
            'status_kredit' => 'aktif',
        ]);

        $inactiveCustomer = Pelanggan::factory()->create([
            'status_kredit' => 'nonaktif',
        ]);

        $service = new CreditSyncService;

        // Test aktif
        $result = $service->validateCreditEligibility($activeCustomer->id_pelanggan);
        $this->assertTrue($result['valid']);

        // Test nonaktif
        $result = $service->validateCreditEligibility($inactiveCustomer->id_pelanggan);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('nonaktif', $result['message']);
    }

    /**
     * Test: CreditSyncService validateCreditConsistency
     */
    public function test_credit_sync_service_validate_consistency(): void
    {
        $service = new CreditSyncService;

        // Test valid pelanggan
        $validPelanggan = Pelanggan::factory()->create([
            'status_kredit' => 'aktif',
            'credit_limit' => 5000000,
            'saldo_kredit' => 1000000,
        ]);

        $result = $service->validateCreditConsistency($validPelanggan->id_pelanggan);
        $this->assertTrue($result['consistent']);
        $this->assertEmpty($result['issues']);

        // Test invalid pelanggan - negative saldo
        $invalidPelanggan = Pelanggan::factory()->create([
            'status_kredit' => 'aktif',
            'credit_limit' => 5000000,
            'saldo_kredit' => -1000000, // negative
        ]);

        $result = $service->validateCreditConsistency($invalidPelanggan->id_pelanggan);
        $this->assertFalse($result['consistent']);
        $this->assertNotEmpty($result['issues']);
    }

    /**
     * Test: Credit tidak bisa melebihi limit
     */
    public function test_credit_transaction_cannot_exceed_limit(): void
    {
        $pelanggan = Pelanggan::factory()->create([
            'status_kredit' => 'aktif',
            'credit_limit' => 1000000,
            'saldo_kredit' => 0,
        ]);

        $produk = Produk::factory()->create([
            'satuan' => 'pcs',
            'stok' => 100,
            'harga' => 1500000,
        ]);

        $response = $this->post('/kasir/pos', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'items' => [
                [
                    'id_produk' => $produk->id_produk,
                    'jumlah' => 1,
                    'jenis_satuan' => 'unit',
                    'harga_satuan' => 1500000,
                ],
            ],
            'subtotal' => 1500000,
            'diskon' => 0,
            'pajak' => 0,
            'total' => 1500000,
            'metode_bayar' => 'KREDIT',
            'dp' => 0,
            'tenor_bulan' => 12,
            'bunga_persen' => 5,
            'cicilan_bulanan' => 150000,
            'mulai_kontrak' => now()->toDateString(),
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('melebihi', $response['message']);
    }

    /**
     * Test: DP mengurangi kredit yang digunakan
     */
    public function test_dp_reduces_credit_usage(): void
    {
        $pelanggan = Pelanggan::factory()->create([
            'status_kredit' => 'aktif',
            'credit_limit' => 5000000,
            'saldo_kredit' => 0,
        ]);

        $produk = Produk::factory()->create([
            'satuan' => 'pcs',
            'stok' => 100,
            'harga' => 1000000,
        ]);

        // Transaksi: 1000000 total, 300000 DP, jadi kredit = 700000
        $response = $this->post('/kasir/pos', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'items' => [
                [
                    'id_produk' => $produk->id_produk,
                    'jumlah' => 1,
                    'jenis_satuan' => 'unit',
                    'harga_satuan' => 1000000,
                ],
            ],
            'subtotal' => 1000000,
            'diskon' => 0,
            'pajak' => 0,
            'total' => 1000000,
            'metode_bayar' => 'KREDIT',
            'dp' => 300000,
            'tenor_bulan' => 12,
            'bunga_persen' => 5,
            'cicilan_bulanan' => 70000,
            'mulai_kontrak' => now()->toDateString(),
        ]);

        $response->assertSuccessful();

        $pelanggan->refresh();

        // Kredit yang digunakan = 1000000 - 300000 = 700000
        $this->assertEquals(700000, (float) $pelanggan->saldo_kredit);

        // Available limit = 5000000 - 700000 = 4300000
        $this->assertEquals(4300000, (float) $pelanggan->credit_limit);
    }
}
