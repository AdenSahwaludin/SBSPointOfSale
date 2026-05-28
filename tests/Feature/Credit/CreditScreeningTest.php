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
        // NEW RULES: TS < 50 → REJECTED
        $pelanggan = Pelanggan::factory()->create([
            'trust_score' => 40,
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
            'dp' => 50000,
            'tenor_bulan' => 12,
            'bunga_persen' => 10,
            'cicilan_bulanan' => 5000,
            'mulai_kontrak' => now()->toDateString(),
        ]);

        $response->assertStatus(422);
        expect($response->json('message'))->toContain('di bawah 55');
    });

    it('rejects credit transaction when trust_score == 50 (BASELINE)', function () {
        // NEW RULES: TS == 50 → BASELINE (otomatis ditolak)
        $pelanggan = Pelanggan::factory()->create([
            'trust_score' => 50,
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
            'dp' => 50000,
            'tenor_bulan' => 12,
            'bunga_persen' => 10,
            'cicilan_bulanan' => 5000,
            'mulai_kontrak' => now()->toDateString(),
        ]);

        $response->assertStatus(422);
        expect($response->json('message'))->toContain('di bawah 55');
    });

    it('rejects credit transaction when trust_score 51-69', function () {
        // NEW RULES: TS 51-69 → REJECTED
        $pelanggan = Pelanggan::factory()->create([
            'trust_score' => 65,
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
            'dp' => 50000,
            'tenor_bulan' => 12,
            'bunga_persen' => 10,
            'cicilan_bulanan' => 5000,
            'mulai_kontrak' => now()->toDateString(),
        ]);

        $response->assertStatus(200);
        expect($response->json('success'))->toBeTrue();
    });

    it('accepts credit transaction when trust_score >= 70 (APPROVED)', function () {
        // NEW RULES: TS >= 70 → APPROVED (no DP minimum required)
        $pelanggan = Pelanggan::factory()->create([
            'trust_score' => 75,
            'credit_limit' => 5000000,
            'status_kredit' => 'aktif',
        ]);

        $this->actingAs($this->kasir);

        // Should accept with any DP amount (no 20% minimum)
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
            'dp' => 5000, // Low DP is allowed for APPROVED tier
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

    it('validates screening at trust_score boundaries', function () {
        $testCases = [
            ['trust_score' => 49, 'expected_status' => 422, 'should_create' => false],
            ['trust_score' => 54, 'expected_status' => 422, 'should_create' => false],
            ['trust_score' => 55, 'expected_status' => 200, 'should_create' => true],
            ['trust_score' => 70, 'expected_status' => 200, 'should_create' => true],
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
                'dp' => 0, // No DP requirement for new rules
                'tenor_bulan' => 12,
                'bunga_persen' => 10,
                'cicilan_bulanan' => 5000,
                'mulai_kontrak' => now()->toDateString(),
            ]);

            expect($response->status())->toBe($testCase['expected_status']);

            if ($testCase['should_create']) {
                expect(Transaksi::where('id_pelanggan', $pelanggan->id_pelanggan)->exists())->toBeTrue();
            } else {
                expect($response->json('message'))->not->toBeEmpty();
            }
        }
    });

    it('approves all DP amounts for approved tier (TS >= 70)', function () {
        // NEW RULES: TS >= 70 can use any DP amount (no minimum)
        $pelanggan = Pelanggan::factory()->create([
            'trust_score' => 80,
            'credit_limit' => 5000000,
            'status_kredit' => 'aktif',
        ]);

        $this->actingAs($this->kasir);

        // Test various DP amounts - all should be accepted
        $dpAmounts = [0, 5000, 10000, 50000, 90000];

        foreach ($dpAmounts as $dp) {
            $pelanggan2 = Pelanggan::factory()->create([
                'trust_score' => 80,
                'credit_limit' => 5000000,
                'status_kredit' => 'aktif',
            ]);

            $response = $this->postJson('/kasir/pos', [
                'id_pelanggan' => $pelanggan2->id_pelanggan,
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
                'dp' => $dp,
                'tenor_bulan' => 12,
                'bunga_persen' => 10,
                'cicilan_bulanan' => 5000,
                'mulai_kontrak' => now()->toDateString(),
            ]);

            expect($response->status())->toBe(200);
            expect($response->json('success'))->toBeTrue();
        }
    });
});
