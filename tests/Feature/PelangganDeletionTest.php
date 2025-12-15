<?php

namespace Tests\Feature;

use App\Models\KontrakKredit;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PelangganDeletionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_can_delete_pelanggan_without_transactions(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/admin/pelanggan/{$pelanggan->id_pelanggan}");

        $response->assertRedirect('/admin/pelanggan');
        $response->assertSessionHas('success', 'Pelanggan berhasil dihapus');
        $this->assertDatabaseMissing('pelanggan', ['id_pelanggan' => $pelanggan->id_pelanggan]);
    }

    public function test_cannot_delete_pelanggan_with_transactions(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        // Buat transaksi untuk pelanggan
        Transaksi::factory()->create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'total' => 1000000,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/pelanggan/{$pelanggan->id_pelanggan}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('riwayat transaksi', $response->getSession()->get('error'));
        $this->assertDatabaseHas('pelanggan', ['id_pelanggan' => $pelanggan->id_pelanggan]);
    }

    public function test_cannot_delete_pelanggan_with_credit_contracts(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        // Buat kontrak kredit untuk pelanggan
        KontrakKredit::factory()->create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/pelanggan/{$pelanggan->id_pelanggan}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('kontrak kredit', $response->getSession()->get('error'));
        $this->assertDatabaseHas('pelanggan', ['id_pelanggan' => $pelanggan->id_pelanggan]);
    }

    public function test_cannot_delete_pelanggan_with_outstanding_balance(): void
    {
        $pelanggan = Pelanggan::factory()->create([
            'saldo_kredit' => 500000,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/pelanggan/{$pelanggan->id_pelanggan}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('saldo kredit', $response->getSession()->get('error'));
        $this->assertDatabaseHas('pelanggan', ['id_pelanggan' => $pelanggan->id_pelanggan]);
    }

    public function test_deletion_block_reasons_api_returns_can_delete_true(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/pelanggan/{$pelanggan->id_pelanggan}/deletion-block-reasons");

        $response->assertSuccessful();
        $response->assertJson([
            'can_delete' => true,
            'message' => 'Pelanggan dapat dihapus',
            'reasons' => [],
        ]);
    }

    public function test_deletion_block_reasons_api_returns_can_delete_false(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        Transaksi::factory()->create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'total' => 2000000,
        ]);

        KontrakKredit::factory()->create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/pelanggan/{$pelanggan->id_pelanggan}/deletion-block-reasons");

        $response->assertSuccessful();
        $data = $response->json();

        $this->assertFalse($data['can_delete']);
        $this->assertStringContainsString('riwayat transaksi', $data['message']);
        $this->assertStringContainsString('kontrak kredit', $data['message']);

        // Verifikasi detail informasi
        $this->assertArrayHasKey('details', $data);
        $this->assertArrayHasKey('transactions', $data['details']);
        $this->assertArrayHasKey('contracts', $data['details']);
        $this->assertEquals(1, $data['details']['transactions']['count']);
        $this->assertEquals(1, $data['details']['contracts']['count']);
    }

    public function test_deletion_block_reasons_includes_all_blocking_factors(): void
    {
        $pelanggan = Pelanggan::factory()->create([
            'saldo_kredit' => 1500000,
        ]);

        Transaksi::factory()->create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'total' => 3000000,
        ]);

        KontrakKredit::factory()->create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/pelanggan/{$pelanggan->id_pelanggan}/deletion-block-reasons");

        $response->assertSuccessful();
        $data = $response->json();

        $this->assertFalse($data['can_delete']);

        // Verifikasi semua alasan ada
        $this->assertCount(3, $data['summary']);
        $this->assertArrayHasKey('transactions', $data['details']);
        $this->assertArrayHasKey('contracts', $data['details']);
        $this->assertArrayHasKey('outstanding_balance', $data['details']);
    }

    public function test_error_message_format_is_clear_and_informative(): void
    {
        $pelanggan = Pelanggan::factory()->create();

        Transaksi::factory()->count(3)->create([
            'id_pelanggan' => $pelanggan->id_pelanggan,
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/pelanggan/{$pelanggan->id_pelanggan}");

        $errorMessage = $response->getSession()->get('error');

        // Verifikasi format pesan
        $this->assertStringContainsString('tidak dapat dihapus', $errorMessage);
        $this->assertStringContainsString('riwayat transaksi', $errorMessage);
        $this->assertStringContainsString('3', $errorMessage);
    }
}
