<?php

use App\AdjustmentType;
use App\Models\Produk;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Stock Adjustment - Positive Adjustments', function () {
    beforeEach(function () {
        $this->kasir = User::factory()->kasir()->create();
    });

    it('allows kasir to create retur_pelanggan adjustment that increases stock', function () {
        $initialStock = 50;
        $produk = Produk::factory()->create(['stok' => $initialStock]);
        $qtyAdjustment = 10;

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => AdjustmentType::ReturPelanggan->value,
                'qty_adjustment' => $qtyAdjustment,
                'alasan' => 'Customer returned unused items',
            ]);

        $response->assertRedirect(route('kasir.stock-adjustment.index'));
        $response->assertSessionHas('success');

        // Verify adjustment record was created
        $adjustment = StockAdjustment::where('id_produk', $produk->id_produk)->first();
        expect($adjustment)->not->toBeNull();
        expect($adjustment->tipe)->toBe(AdjustmentType::ReturPelanggan->value);
        expect($adjustment->qty_adjustment)->toBe($qtyAdjustment);

        // Verify stock was increased
        $produk->refresh();
        expect($produk->stok)->toBe($initialStock + $qtyAdjustment);
    });

    it('allows kasir to create retur_gudang adjustment that increases stock', function () {
        $initialStock = 30;
        $produk = Produk::factory()->create(['stok' => $initialStock]);
        $qtyAdjustment = 25;

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => AdjustmentType::ReturGudang->value,
                'qty_adjustment' => $qtyAdjustment,
                'alasan' => 'Returned from warehouse',
            ]);

        $response->assertRedirect(route('kasir.stock-adjustment.index'));

        $produk->refresh();
        expect($produk->stok)->toBe($initialStock + $qtyAdjustment);
    });

    it('allows kasir to create koreksi_plus adjustment that increases stock', function () {
        $initialStock = 100;
        $produk = Produk::factory()->create(['stok' => $initialStock]);
        $qtyAdjustment = 15;

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => AdjustmentType::KoreksiPlus->value,
                'qty_adjustment' => $qtyAdjustment,
                'alasan' => 'Found additional stock during inventory check',
            ]);

        $response->assertRedirect(route('kasir.stock-adjustment.index'));

        $produk->refresh();
        expect($produk->stok)->toBe($initialStock + $qtyAdjustment);
    });
});

describe('Stock Adjustment - Negative Adjustments', function () {
    beforeEach(function () {
        $this->kasir = User::factory()->kasir()->create();
    });

    it('allows kasir to create expired adjustment that decreases stock', function () {
        $initialStock = 50;
        $produk = Produk::factory()->create(['stok' => $initialStock]);
        $qtyAdjustment = 5;

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => AdjustmentType::Expired->value,
                'qty_adjustment' => $qtyAdjustment,
                'alasan' => 'Products expired',
            ]);

        $response->assertRedirect(route('kasir.stock-adjustment.index'));
        $response->assertSessionHas('success');

        // Verify stock was decreased
        $produk->refresh();
        expect($produk->stok)->toBe($initialStock - $qtyAdjustment);
    });

    it('allows kasir to create rusak adjustment that decreases stock', function () {
        $initialStock = 80;
        $produk = Produk::factory()->create(['stok' => $initialStock]);
        $qtyAdjustment = 3;

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => AdjustmentType::Rusak->value,
                'qty_adjustment' => $qtyAdjustment,
                'alasan' => 'Damaged packaging',
            ]);

        $response->assertRedirect(route('kasir.stock-adjustment.index'));

        $produk->refresh();
        expect($produk->stok)->toBe($initialStock - $qtyAdjustment);
    });

    it('allows kasir to create koreksi_minus adjustment that decreases stock', function () {
        $initialStock = 100;
        $produk = Produk::factory()->create(['stok' => $initialStock]);
        $qtyAdjustment = 10;

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => AdjustmentType::KoreksiMinus->value,
                'qty_adjustment' => $qtyAdjustment,
                'alasan' => 'Inventory correction - missing items',
            ]);

        $response->assertRedirect(route('kasir.stock-adjustment.index'));

        $produk->refresh();
        expect($produk->stok)->toBe($initialStock - $qtyAdjustment);
    });

    it('prevents negative adjustment that would result in negative stock', function () {
        $initialStock = 10;
        $produk = Produk::factory()->create(['stok' => $initialStock]);
        $qtyAdjustment = 20; // More than current stock

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => AdjustmentType::Expired->value,
                'qty_adjustment' => $qtyAdjustment,
                'alasan' => 'Too many expired items',
            ]);

        $response->assertSessionHasErrors();

        // Verify stock was not changed
        $produk->refresh();
        expect($produk->stok)->toBe($initialStock);
    });

    it('allows negative adjustment equal to current stock', function () {
        $initialStock = 15;
        $produk = Produk::factory()->create(['stok' => $initialStock]);
        $qtyAdjustment = 15; // Exactly the current stock

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => AdjustmentType::Rusak->value,
                'qty_adjustment' => $qtyAdjustment,
                'alasan' => 'All items damaged',
            ]);

        $response->assertRedirect(route('kasir.stock-adjustment.index'));

        $produk->refresh();
        expect($produk->stok)->toBe(0);
    });
});

describe('Stock Adjustment - All Adjustment Types', function () {
    beforeEach(function () {
        $this->kasir = User::factory()->kasir()->create();
    });

    it('supports all 7 adjustment types', function () {
        $adjustmentTypes = [
            ['type' => AdjustmentType::ReturPelanggan, 'initialStock' => 50, 'adjustment' => 10, 'expectedStock' => 60],
            ['type' => AdjustmentType::ReturGudang, 'initialStock' => 30, 'adjustment' => 5, 'expectedStock' => 35],
            ['type' => AdjustmentType::KoreksiPlus, 'initialStock' => 100, 'adjustment' => 15, 'expectedStock' => 115],
            ['type' => AdjustmentType::KoreksiMinus, 'initialStock' => 100, 'adjustment' => 10, 'expectedStock' => 90],
            ['type' => AdjustmentType::PenyesuaianOpname, 'initialStock' => 50, 'adjustment' => 5, 'expectedStock' => 55],
            ['type' => AdjustmentType::Expired, 'initialStock' => 80, 'adjustment' => 8, 'expectedStock' => 72],
            ['type' => AdjustmentType::Rusak, 'initialStock' => 60, 'adjustment' => 3, 'expectedStock' => 57],
        ];

        foreach ($adjustmentTypes as $testCase) {
            $produk = Produk::factory()->create(['stok' => $testCase['initialStock']]);

            $response = $this->actingAs($this->kasir)
                ->post(route('kasir.stock-adjustment.store'), [
                    'id_produk' => $produk->id_produk,
                    'tipe' => $testCase['type']->value,
                    'qty_adjustment' => $testCase['adjustment'],
                    'alasan' => "Testing {$testCase['type']->label()}",
                ]);

            $response->assertRedirect(route('kasir.stock-adjustment.index'));

            $produk->refresh();
            expect($produk->stok)->toBe($testCase['expectedStock'],
                "Failed for adjustment type: {$testCase['type']->value}");
        }
    });

    it('creates adjustment records with correct metadata', function () {
        $produk = Produk::factory()->create(['stok' => 100]);
        $alasan = 'Testing adjustment record creation';

        $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => AdjustmentType::ReturPelanggan->value,
                'qty_adjustment' => 25,
                'alasan' => $alasan,
            ]);

        $adjustment = StockAdjustment::where('id_produk', $produk->id_produk)->first();

        expect($adjustment)->not->toBeNull();
        expect($adjustment->id_produk)->toBe($produk->id_produk);
        expect($adjustment->tipe)->toBe(AdjustmentType::ReturPelanggan->value);
        expect($adjustment->qty_adjustment)->toBe(25);
        expect($adjustment->alasan)->toBe($alasan);
        expect($adjustment->id_pengguna)->toBe($this->kasir->id_pengguna);
        expect($adjustment->tanggal_adjustment)->not->toBeNull();
    });
});

describe('Stock Adjustment - Validation', function () {
    beforeEach(function () {
        $this->kasir = User::factory()->kasir()->create();
    });

    it('validates that tipe must be a valid enum value', function () {
        $produk = Produk::factory()->create(['stok' => 50]);

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => 'invalid_type',
                'qty_adjustment' => 10,
                'alasan' => 'Testing invalid type',
            ]);

        $response->assertSessionHasErrors('tipe');
    });

    it('validates that qty_adjustment must be at least 1', function () {
        $produk = Produk::factory()->create(['stok' => 50]);

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => AdjustmentType::ReturPelanggan->value,
                'qty_adjustment' => 0,
                'alasan' => 'Testing zero quantity',
            ]);

        $response->assertSessionHasErrors('qty_adjustment');
    });

    it('validates that qty_adjustment cannot be negative', function () {
        $produk = Produk::factory()->create(['stok' => 50]);

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => AdjustmentType::ReturPelanggan->value,
                'qty_adjustment' => -5,
                'alasan' => 'Testing negative quantity',
            ]);

        $response->assertSessionHasErrors('qty_adjustment');
    });

    it('validates that id_produk is required', function () {
        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'tipe' => AdjustmentType::ReturPelanggan->value,
                'qty_adjustment' => 10,
                'alasan' => 'Testing missing product ID',
            ]);

        $response->assertSessionHasErrors('id_produk');
    });

    it('validates that id_produk must exist in database', function () {
        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => 999999,
                'tipe' => AdjustmentType::ReturPelanggan->value,
                'qty_adjustment' => 10,
                'alasan' => 'Testing non-existent product',
            ]);

        $response->assertSessionHasErrors('id_produk');
    });

    it('validates that tipe is required', function () {
        $produk = Produk::factory()->create(['stok' => 50]);

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'qty_adjustment' => 10,
                'alasan' => 'Testing missing type',
            ]);

        $response->assertSessionHasErrors('tipe');
    });

    it('allows alasan to be optional', function () {
        $produk = Produk::factory()->create(['stok' => 50]);

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => AdjustmentType::ReturPelanggan->value,
                'qty_adjustment' => 10,
            ]);

        $response->assertRedirect(route('kasir.stock-adjustment.index'));
        $response->assertSessionHas('success');
    });
});

describe('Stock Adjustment - Stock Update Accuracy', function () {
    beforeEach(function () {
        $this->kasir = User::factory()->kasir()->create();
    });

    it('correctly updates stock for multiple sequential adjustments', function () {
        $initialStock = 100;
        $produk = Produk::factory()->create(['stok' => $initialStock]);

        // First adjustment: +20
        $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => AdjustmentType::ReturPelanggan->value,
                'qty_adjustment' => 20,
                'alasan' => 'First adjustment',
            ]);

        $produk->refresh();
        expect($produk->stok)->toBe(120);

        // Second adjustment: -10
        $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => AdjustmentType::Expired->value,
                'qty_adjustment' => 10,
                'alasan' => 'Second adjustment',
            ]);

        $produk->refresh();
        expect($produk->stok)->toBe(110);

        // Third adjustment: +5
        $this->actingAs($this->kasir)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => AdjustmentType::KoreksiPlus->value,
                'qty_adjustment' => 5,
                'alasan' => 'Third adjustment',
            ]);

        $produk->refresh();
        expect($produk->stok)->toBe(115);
    });

    it('maintains accurate stock count with concurrent adjustments', function () {
        $initialStock = 50;
        $produk = Produk::factory()->create(['stok' => $initialStock]);

        // Simulate multiple kasir making adjustments
        $kasir1 = User::factory()->kasir()->create();
        $kasir2 = User::factory()->kasir()->create();

        $this->actingAs($kasir1)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => AdjustmentType::ReturPelanggan->value,
                'qty_adjustment' => 10,
                'alasan' => 'Kasir 1 adjustment',
            ]);

        $this->actingAs($kasir2)
            ->post(route('kasir.stock-adjustment.store'), [
                'id_produk' => $produk->id_produk,
                'tipe' => AdjustmentType::ReturGudang->value,
                'qty_adjustment' => 15,
                'alasan' => 'Kasir 2 adjustment',
            ]);

        $produk->refresh();
        expect($produk->stok)->toBe(75); // 50 + 10 + 15

        // Verify both adjustments were recorded
        $adjustments = StockAdjustment::where('id_produk', $produk->id_produk)->get();
        expect($adjustments)->toHaveCount(2);
    });
});

describe('Stock Adjustment - View Operations', function () {
    beforeEach(function () {
        $this->kasir = User::factory()->kasir()->create();
    });

    it('allows kasir to view stock adjustment index', function () {
        $response = $this->actingAs($this->kasir)
            ->get(route('kasir.stock-adjustment.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Kasir/StockAdjustment/Index')
        );
    });

    it('displays all adjustment types in create form', function () {
        $response = $this->actingAs($this->kasir)
            ->get(route('kasir.stock-adjustment.create'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Kasir/StockAdjustment/Create')
            ->has('adjustmentTypes', 7)
            ->has('produk')
        );
    });
});
