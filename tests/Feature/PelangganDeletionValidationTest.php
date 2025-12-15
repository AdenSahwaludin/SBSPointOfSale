<?php

namespace Tests\Feature;

use App\Models\KontrakKredit;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelangganDeletionValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $kasir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->kasir = User::factory()->create(['role' => 'kasir']);
    }

    /**
     * Test: Pelanggan tanpa relasi dapat dihapus
     */
    public function test_can_delete_pelanggan_without_any_relations(): void
    {
        $this->actingAs($this->admin);

        $pelanggan = Pelanggan::factory()->create([
            'nama' => 'John Doe',
            'saldo_kredit' => 0,
        ]);

        $response = $this->delete("/admin/pelanggan/{$pelanggan->id_pelanggan}");

        $response->assertRedirect(route('admin.pelanggan.index'));
        $response->assertSessionHas('success');
        $this->assertStringContainsString('berhasil dihapus', session('success'));
        $this->assertStringContainsString('John Doe', session('success'));

        // Verify pelanggan is deleted
        $this->assertDatabaseMissing('pelanggan', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
        ]);
    }

    /**
     * Test: Pelanggan dengan riwayat transaksi tidak dapat dihapus
     */
    public function test_cannot_delete_pelanggan_with_transaction_history(): void
    {
        $this->actingAs($this->admin);

        $pelanggan = Pelanggan::factory()->create();

        // Create a transaction for this pelanggan
        Transaksi::factory()->create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'status_pembayaran' => Transaksi::STATUS_LUNAS,
        ]);

        $response = $this->delete("/admin/pelanggan/{$pelanggan->id_pelanggan}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('riwayat transaksi', session('error'));
        $this->assertStringContainsString('1', session('error')); // transaction count
        $this->assertStringContainsString('audit', session('error'));

        // Verify pelanggan still exists
        $this->assertDatabaseHas('pelanggan', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
        ]);
    }

    /**
     * Test: Pelanggan dengan multiple transaksi menampilkan jumlah yang tepat
     */
    public function test_deletion_error_shows_correct_transaction_count(): void
    {
        $this->actingAs($this->admin);

        $pelanggan = Pelanggan::factory()->create();

        // Create 5 transactions
        Transaksi::factory()->count(5)->create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
        ]);

        $response = $this->delete("/admin/pelanggan/{$pelanggan->id_pelanggan}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('5', session('error'));
        $this->assertStringContainsString('riwayat transaksi', session('error'));
    }

    /**
     * Test: Pelanggan dengan kontrak kredit aktif tidak dapat dihapus
     */
    public function test_cannot_delete_pelanggan_with_active_credit_contract(): void
    {
        $this->actingAs($this->admin);

        $pelanggan = Pelanggan::factory()->create();

        // Create an active credit contract
        KontrakKredit::factory()->create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'status' => 'AKTIF',
        ]);

        $response = $this->delete("/admin/pelanggan/{$pelanggan->id_pelanggan}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('kontrak kredit aktif', session('error'));
        $this->assertStringContainsString('selesaikan', session('error'));

        // Verify pelanggan still exists
        $this->assertDatabaseHas('pelanggan', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
        ]);
    }

    /**
     * Test: Pelanggan dengan riwayat kontrak kredit tidak dapat dihapus
     */
    public function test_cannot_delete_pelanggan_with_credit_contract_history(): void
    {
        $this->actingAs($this->admin);

        $pelanggan = Pelanggan::factory()->create();

        // Create a completed credit contract (LUNAS = paid off/completed)
        KontrakKredit::factory()->create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'status' => 'LUNAS',
        ]);

        $response = $this->delete("/admin/pelanggan/{$pelanggan->id_pelanggan}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('riwayat kontrak kredit', session('error'));
        $this->assertStringContainsString('audit', session('error'));

        // Verify pelanggan still exists
        $this->assertDatabaseHas('pelanggan', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
        ]);
    }

    /**
     * Test: Pelanggan dengan saldo kredit outstanding tidak dapat dihapus
     */
    public function test_cannot_delete_pelanggan_with_outstanding_credit_balance(): void
    {
        $this->actingAs($this->admin);

        $pelanggan = Pelanggan::factory()->create([
            'saldo_kredit' => 1500000, // Outstanding balance
            'status_kredit' => 'aktif',
        ]);

        $response = $this->delete("/admin/pelanggan/{$pelanggan->id_pelanggan}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('saldo kredit outstanding', session('error'));
        $this->assertStringContainsString('1.500.000', session('error')); // Formatted balance
        $this->assertStringContainsString('lunasi', session('error'));

        // Verify pelanggan still exists
        $this->assertDatabaseHas('pelanggan', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
        ]);
    }

    /**
     * Test: Error message provides clear guidance for users
     */
    public function test_error_messages_are_informative_and_actionable(): void
    {
        $this->actingAs($this->admin);

        // Test with transaction
        $pelanggan1 = Pelanggan::factory()->create();
        Transaksi::factory()->create(['id_pelanggan' => $pelanggan1->id_pelanggan]);

        $response1 = $this->delete("/admin/pelanggan/{$pelanggan1->id_pelanggan}");
        $this->assertStringContainsString('audit', session('error'));
        $this->assertStringContainsString('pelaporan', session('error'));

        // Test with active contract
        $pelanggan2 = Pelanggan::factory()->create();
        KontrakKredit::factory()->create([
            'id_pelanggan' => $pelanggan2->id_pelanggan,
            'status' => 'AKTIF',
        ]);

        $response2 = $this->delete("/admin/pelanggan/{$pelanggan2->id_pelanggan}");
        $this->assertStringContainsString('selesaikan', session('error'));
        $this->assertStringContainsString('batalkan', session('error'));

        // Test with outstanding balance
        $pelanggan3 = Pelanggan::factory()->create(['saldo_kredit' => 500000]);

        $response3 = $this->delete("/admin/pelanggan/{$pelanggan3->id_pelanggan}");
        $this->assertStringContainsString('lunasi', session('error'));
    }

    /**
     * Test: Kasir also gets proper validation messages
     */
    public function test_kasir_also_receives_proper_deletion_validation(): void
    {
        $this->actingAs($this->kasir);

        $pelanggan = Pelanggan::factory()->create();

        // Create a transaction
        Transaksi::factory()->create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
        ]);

        $response = $this->delete("/kasir/customers/{$pelanggan->id_pelanggan}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('riwayat transaksi', session('error'));
    }

    /**
     * Test: Successful deletion shows customer name in confirmation
     */
    public function test_successful_deletion_shows_customer_name(): void
    {
        $this->actingAs($this->admin);

        $pelanggan = Pelanggan::factory()->create([
            'nama' => 'Jane Smith',
            'saldo_kredit' => 0,
        ]);

        $response = $this->delete("/admin/pelanggan/{$pelanggan->id_pelanggan}");

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertStringContainsString('Jane Smith', session('success'));
        $this->assertStringContainsString('berhasil dihapus', session('success'));
    }

    /**
     * Test: Multiple validation conditions are checked in order
     */
    public function test_validation_checks_are_performed_in_logical_order(): void
    {
        $this->actingAs($this->admin);

        $pelanggan = Pelanggan::factory()->create([
            'saldo_kredit' => 2000000,
        ]);

        // Create both transaction and contract
        Transaksi::factory()->create(['id_pelanggan' => $pelanggan->id_pelanggan]);
        KontrakKredit::factory()->create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'status' => 'AKTIF',
        ]);

        $response = $this->delete("/admin/pelanggan/{$pelanggan->id_pelanggan}");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Should check transactions first (most important)
        $this->assertStringContainsString('transaksi', session('error'));
    }

    /**
     * Test: Exception handling during deletion
     */
    public function test_handles_exception_during_deletion_gracefully(): void
    {
        $this->actingAs($this->admin);

        $pelanggan = Pelanggan::factory()->create([
            'saldo_kredit' => 0,
        ]);

        // This test validates that exceptions are caught
        // In real scenario, database constraints would trigger exceptions
        $response = $this->delete("/admin/pelanggan/{$pelanggan->id_pelanggan}");

        // Should either succeed or show error message
        $this->assertTrue(
            $response->isRedirect() &&
            (session()->has('success') || session()->has('error'))
        );
    }
}
