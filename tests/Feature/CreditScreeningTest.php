<?php

use App\Models\KontrakKredit;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Credit Screening (Cicilan Pintar)', function () {
    beforeEach(function () {
        // Create a Kasir user for authentication
        $this->kasir = User::factory()->create([
            'role' => 'kasir',
            'id_pengguna' => 'K001',
        ]);

        // Create test products with sufficient stock
        $this->produk = Produk::factory()->create([
            'nama' => 'Test Product',
            'harga' => 100000,
            'stok' => 100,
            'satuan' => 'pcs',
            'isi_per_pack' => 1,
        ]);

        // Create a category
        $this->kategori = $this->produk->kategori;
    });

    it('rejects credit transaction when trust_score < 50', function () {
        // Create customer with low trust score (trust_score < 50)
        $pelanggan = Pelanggan::factory()->create([
            'trust_score' => 40,
            'credit_limit' => 5000000,
            'status_kredit' => 'aktif',
        ]);

        // Authenticate as kasir
        $this->actingAs($this->kasir);

        // Attempt credit transaction
        $response = $this->postJson('/kasir/pos', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'items' => [
                [
                    'id_produk' => $this->produk->id_produk,
                    'nama' => $this->produk->nama,
                    'harga_satuan' => 100000,
                    'jumlah' => 1,
                    'mode_qty' => 'unit',
                    'subtotal' => 100000,
                    'stok' => 100,
                    'satuan' => 'pcs',
                    'isi_per_pack' => 1,
                ],
            ],
            'metode_bayar' => 'KREDIT',
            'subtotal' => 100000,
            'diskon' => 0,
            'pajak' => 0,
            'total' => 100000,
            'dp' => 50000,
            'tenor_bulan' => 12,
            'bunga_persen' => 10,
            'cicilan_bulanan' => 5000,
            'mulai_kontrak' => now()->toDateString(),
        ]);

        // Should be rejected
        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Pengajuan cicilan tidak diperbolehkan karena trust score terlalu rendah.']);
    });

    it('rejects credit transaction when trust_score 50-70 without sufficient DP', function () {
        // Create customer with medium trust score (50-70)
        $pelanggan = Pelanggan::factory()->create([
            'trust_score' => 65,
            'credit_limit' => 5000000,
            'status_kredit' => 'aktif',
        ]);

        $this->actingAs($this->kasir);

        // Attempt credit transaction with DP < 20% (total 100k, DP should be >= 20k)
        $response = $this->postJson('/kasir/pos', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'items' => [
                [
                    'id_produk' => $this->produk->id_produk,
                    'nama' => $this->produk->nama,
                    'harga_satuan' => 100000,
                    'jumlah' => 1,
                    'mode_qty' => 'unit',
                    'subtotal' => 100000,
                    'stok' => 100,
                    'satuan' => 'pcs',
                    'isi_per_pack' => 1,
                ],
            ],
            'metode_bayar' => 'KREDIT',
            'subtotal' => 100000,
            'diskon' => 0,
            'pajak' => 0,
            'total' => 100000,
            'dp' => 10000, // Only 10k DP (< 20% of 100k)
            'tenor_bulan' => 12,
            'bunga_persen' => 10,
            'cicilan_bulanan' => 5000,
            'mulai_kontrak' => now()->toDateString(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'DP minimal 20% dari total cicilan adalah Rp 20.000.']);
    });

    it('accepts credit transaction when trust_score 50-70 with >= 20% DP', function () {
        $pelanggan = Pelanggan::factory()->create([
            'trust_score' => 65,
            'credit_limit' => 5000000,
            'status_kredit' => 'aktif',
        ]);

        $this->actingAs($this->kasir);

        // Attempt credit transaction with DP >= 20%
        $response = $this->postJson('/kasir/pos', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'items' => [
                [
                    'id_produk' => $this->produk->id_produk,
                    'nama' => $this->produk->nama,
                    'harga_satuan' => 100000,
                    'jumlah' => 1,
                    'mode_qty' => 'unit',
                    'subtotal' => 100000,
                    'stok' => 100,
                    'satuan' => 'pcs',
                    'isi_per_pack' => 1,
                ],
            ],
            'metode_bayar' => 'KREDIT',
            'subtotal' => 100000,
            'diskon' => 0,
            'pajak' => 0,
            'total' => 100000,
            'dp' => 20000, // Exactly 20% DP
            'tenor_bulan' => 12,
            'bunga_persen' => 10,
            'cicilan_bulanan' => 5000,
            'mulai_kontrak' => now()->toDateString(),
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Verify transaction was created
        expect(Transaksi::where('id_pelanggan', $pelanggan->id_pelanggan)->exists())->toBeTrue();
        expect(KontrakKredit::exists())->toBeTrue();
    });

    it('accepts credit transaction when trust_score >= 71', function () {
        $pelanggan = Pelanggan::factory()->create([
            'trust_score' => 75,
            'credit_limit' => 5000000,
            'status_kredit' => 'aktif',
        ]);

        $this->actingAs($this->kasir);

        // Transaction with low DP should still be accepted (no 20% minimum required)
        $response = $this->postJson('/kasir/pos', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'items' => [
                [
                    'id_produk' => $this->produk->id_produk,
                    'nama' => $this->produk->nama,
                    'harga_satuan' => 100000,
                    'jumlah' => 1,
                    'mode_qty' => 'unit',
                    'subtotal' => 100000,
                    'stok' => 100,
                    'satuan' => 'pcs',
                    'isi_per_pack' => 1,
                ],
            ],
            'metode_bayar' => 'KREDIT',
            'subtotal' => 100000,
            'diskon' => 0,
            'pajak' => 0,
            'total' => 100000,
            'dp' => 5000, // Low DP is allowed for approved tier
            'tenor_bulan' => 12,
            'bunga_persen' => 10,
            'cicilan_bulanan' => 5000,
            'mulai_kontrak' => now()->toDateString(),
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        // Verify transaction was created
        expect(Transaksi::where('id_pelanggan', $pelanggan->id_pelanggan)->exists())->toBeTrue();
    });

    it('validates screening at trust_score boundary (49, 50, 70, 71)', function () {
        $testCases = [
            ['trust_score' => 49, 'status' => 422], // REJECTED
            ['trust_score' => 50, 'status' => 422], // MANUAL_REVIEW (no DP)
            ['trust_score' => 70, 'status' => 422], // MANUAL_REVIEW (no DP)
            ['trust_score' => 71, 'status' => 200], // APPROVED
        ];

        foreach ($testCases as $testCase) {
            $pelanggan = Pelanggan::factory()->create([
                'trust_score' => $testCase['trust_score'],
                'credit_limit' => 5000000,
                'status_kredit' => 'aktif',
            ]);

            $this->actingAs($this->kasir);

            $response = $this->postJson('/kasir/pos', [
                'id_pelanggan' => $pelanggan->id_pelanggan,
                'items' => [
                    [
                        'id_produk' => $this->produk->id_produk,
                        'nama' => $this->produk->nama,
                        'harga_satuan' => 100000,
                        'jumlah' => 1,
                        'mode_qty' => 'unit',
                        'subtotal' => 100000,
                        'stok' => 100,
                        'satuan' => 'pcs',
                        'isi_per_pack' => 1,
                    ],
                ],
                'metode_bayar' => 'KREDIT',
                'subtotal' => 100000,
                'diskon' => 0,
                'pajak' => 0,
                'total' => 100000,
                'dp' => 0, // No DP for testing
                'tenor_bulan' => 12,
                'bunga_persen' => 10,
                'cicilan_bulanan' => 5000,
                'mulai_kontrak' => now()->toDateString(),
            ]);

            $response->assertStatus($testCase['status']);

            // Verify the response message based on status
            if ($testCase['status'] === 422) {
                // Should have error message
                expect($response->json('message'))->not->toBeEmpty();
            } else {
                // Should be successful
                expect($response->json('success'))->toBeTrue();
            }
        }
    });

    it('allows manual review tier with exactly 20% DP', function () {
        $pelanggan = Pelanggan::factory()->create([
            'trust_score' => 60,
            'credit_limit' => 5000000,
            'status_kredit' => 'aktif',
        ]);

        $this->actingAs($this->kasir);

        // Test with various amounts to confirm 20% calculation
        $totalAmount = 500000;
        $minDp20Percent = $totalAmount * 0.2; // Should be 100000

        $response = $this->postJson('/kasir/pos', [
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'items' => [
                [
                    'id_produk' => $this->produk->id_produk,
                    'nama' => $this->produk->nama,
                    'harga_satuan' => $totalAmount,
                    'jumlah' => 5,
                    'mode_qty' => 'unit',
                    'subtotal' => $totalAmount,
                    'stok' => 100,
                    'satuan' => 'pcs',
                    'isi_per_pack' => 1,
                ],
            ],
            'metode_bayar' => 'KREDIT',
            'subtotal' => $totalAmount,
            'diskon' => 0,
            'pajak' => 0,
            'total' => $totalAmount,
            'dp' => $minDp20Percent, // Exactly 20%
            'tenor_bulan' => 12,
            'bunga_persen' => 10,
            'cicilan_bulanan' => 5000,
            'mulai_kontrak' => now()->toDateString(),
        ]);

        // Should succeed with exactly 20% DP
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    });
});
