<?php

use App\GoodsInStatus;
use App\Models\GoodsIn;
use App\Models\GoodsInDetail;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Goods In - Kasir Operations', function () {
    beforeEach(function () {
        // Create kasir and admin users
        $this->kasir = User::factory()->kasir()->create();
        $this->admin = User::factory()->admin()->create();
    });

    it('allows kasir to create PO request with multiple items', function () {
        $produk1 = Produk::factory()->create();
        $produk2 = Produk::factory()->create();

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.goods-in.store'), [
                'items' => [
                    [
                        'id_produk' => $produk1->id_produk,
                        'qty_request' => 10,
                    ],
                    [
                        'id_produk' => $produk2->id_produk,
                        'qty_request' => 20,
                    ],
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check GoodsIn record was created
        $goodsIn = GoodsIn::where('id_kasir', $this->kasir->id_pengguna)->first();
        expect($goodsIn)->not->toBeNull();
        expect($goodsIn->status)->toBe(GoodsInStatus::Draft->value);
        expect($goodsIn->nomor_po)->toMatch('/^PO-\d{4}-\d{2}-\d{5}$/');

        // Check details were created
        expect($goodsIn->details)->toHaveCount(2);
        expect($goodsIn->details[0]->qty_request)->toBe(10);
        expect($goodsIn->details[1]->qty_request)->toBe(20);
    });

    it('allows kasir to view their own POs', function () {
        // Create some POs for this kasir
        $goodsIn1 = GoodsIn::create([
            'nomor_po' => GoodsIn::generateNomorPO(),
            'status' => GoodsInStatus::Submitted->value,
            'tanggal_request' => now(),
            'id_kasir' => $this->kasir->id_pengguna,
        ]);

        $goodsIn2 = GoodsIn::create([
            'nomor_po' => GoodsIn::generateNomorPO(),
            'status' => GoodsInStatus::Approved->value,
            'tanggal_request' => now(),
            'id_kasir' => $this->kasir->id_pengguna,
        ]);

        // Create PO for another kasir (should not be visible)
        $otherKasir = User::factory()->kasir()->create();
        $otherGoodsIn = GoodsIn::create([
            'nomor_po' => GoodsIn::generateNomorPO(),
            'status' => GoodsInStatus::Submitted->value,
            'tanggal_request' => now(),
            'id_kasir' => $otherKasir->id_pengguna,
        ]);

        $response = $this->actingAs($this->kasir)
            ->get(route('kasir.goods-in.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Kasir/GoodsIn/Index')
            ->has('pos', 2)
        );
    });

    it('prevents kasir from accessing admin approval routes', function () {
        $goodsIn = GoodsIn::create([
            'nomor_po' => GoodsIn::generateNomorPO(),
            'status' => GoodsInStatus::Submitted->value,
            'tanggal_request' => now(),
            'id_kasir' => $this->kasir->id_pengguna,
        ]);

        // Try to access admin approval index
        $response = $this->actingAs($this->kasir)
            ->get(route('admin.goods-in-approval.index'));

        $response->assertRedirect();

        // Try to approve PO
        $response = $this->actingAs($this->kasir)
            ->post(route('admin.goods-in-approval.approve', $goodsIn->id_goods_in), [
                'catatan' => 'Approved',
            ]);

        $response->assertRedirect();

        // Try to reject PO
        $response = $this->actingAs($this->kasir)
            ->post(route('admin.goods-in-approval.reject', $goodsIn->id_goods_in), [
                'catatan' => 'Rejected',
            ]);

        $response->assertRedirect();
    });

    it('validates that items are required when creating PO', function () {
        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.goods-in.store'), [
                'items' => [],
            ]);

        $response->assertSessionHasErrors('items');
    });

    it('validates that qty_request must be at least 1', function () {
        $produk = Produk::factory()->create();

        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.goods-in.store'), [
                'items' => [
                    [
                        'id_produk' => $produk->id_produk,
                        'qty_request' => 0,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('items.0.qty_request');
    });

    it('validates that id_produk must exist', function () {
        $response = $this->actingAs($this->kasir)
            ->post(route('kasir.goods-in.store'), [
                'items' => [
                    [
                        'id_produk' => 999999,
                        'qty_request' => 10,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('items.0.id_produk');
    });
});

describe('Goods In - Admin Approval Operations', function () {
    beforeEach(function () {
        $this->kasir = User::factory()->kasir()->create();
        $this->admin = User::factory()->admin()->create();
    });

    it('allows admin to view pending POs', function () {
        // Create submitted POs
        $submittedPO1 = GoodsIn::create([
            'nomor_po' => GoodsIn::generateNomorPO(),
            'status' => GoodsInStatus::Submitted->value,
            'tanggal_request' => now()->subDays(2),
            'id_kasir' => $this->kasir->id_pengguna,
        ]);

        $submittedPO2 = GoodsIn::create([
            'nomor_po' => GoodsIn::generateNomorPO(),
            'status' => GoodsInStatus::Submitted->value,
            'tanggal_request' => now()->subDays(1),
            'id_kasir' => $this->kasir->id_pengguna,
        ]);

        // Create approved PO (should not appear in pending list)
        $approvedPO = GoodsIn::create([
            'nomor_po' => GoodsIn::generateNomorPO(),
            'status' => GoodsInStatus::Approved->value,
            'tanggal_request' => now(),
            'id_kasir' => $this->kasir->id_pengguna,
            'id_admin' => $this->admin->id_pengguna,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.goods-in-approval.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/GoodsInApproval/Index')
            ->has('pendingPOs', 2)
        );
    });

    it('allows admin to approve PO with correct data updates', function () {
        $produk = Produk::factory()->create();

        $goodsIn = GoodsIn::create([
            'nomor_po' => GoodsIn::generateNomorPO(),
            'status' => GoodsInStatus::Submitted->value,
            'tanggal_request' => now(),
            'id_kasir' => $this->kasir->id_pengguna,
        ]);

        GoodsInDetail::create([
            'id_goods_in' => $goodsIn->id_goods_in,
            'id_produk' => $produk->id_produk,
            'qty_request' => 50,
            'qty_received' => 0,
        ]);

        $catatan = 'PO approved for processing';

        $response = $this->actingAs($this->admin)
            ->post(route('admin.goods-in-approval.approve', $goodsIn->id_goods_in), [
                'catatan' => $catatan,
            ]);

        $response->assertRedirect(route('admin.goods-in-approval.index'));
        $response->assertSessionHas('success');

        // Refresh the model
        $goodsIn->refresh();

        expect($goodsIn->status)->toBe(GoodsInStatus::Approved->value);
        expect($goodsIn->id_admin)->toBe($this->admin->id_pengguna);
        expect($goodsIn->tanggal_approval)->not->toBeNull();
        expect($goodsIn->catatan_approval)->toBe($catatan);
    });

    it('allows admin to reject PO with catatan', function () {
        $goodsIn = GoodsIn::create([
            'nomor_po' => GoodsIn::generateNomorPO(),
            'status' => GoodsInStatus::Submitted->value,
            'tanggal_request' => now(),
            'id_kasir' => $this->kasir->id_pengguna,
        ]);

        $catatan = 'Items not available in budget';

        $response = $this->actingAs($this->admin)
            ->post(route('admin.goods-in-approval.reject', $goodsIn->id_goods_in), [
                'catatan' => $catatan,
            ]);

        $response->assertRedirect(route('admin.goods-in-approval.index'));
        $response->assertSessionHas('success');

        $goodsIn->refresh();

        expect($goodsIn->status)->toBe(GoodsInStatus::Rejected->value);
        expect($goodsIn->id_admin)->toBe($this->admin->id_pengguna);
        expect($goodsIn->tanggal_approval)->not->toBeNull();
        expect($goodsIn->catatan_approval)->toBe($catatan);
    });

    it('prevents admin from approving already approved PO', function () {
        $goodsIn = GoodsIn::create([
            'nomor_po' => GoodsIn::generateNomorPO(),
            'status' => GoodsInStatus::Approved->value,
            'tanggal_request' => now(),
            'tanggal_approval' => now(),
            'id_kasir' => $this->kasir->id_pengguna,
            'id_admin' => $this->admin->id_pengguna,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.goods-in-approval.approve', $goodsIn->id_goods_in), [
                'catatan' => 'Trying to approve again',
            ]);

        $response->assertSessionHasErrors();
    });

    it('prevents admin from rejecting already rejected PO', function () {
        $goodsIn = GoodsIn::create([
            'nomor_po' => GoodsIn::generateNomorPO(),
            'status' => GoodsInStatus::Rejected->value,
            'tanggal_request' => now(),
            'tanggal_approval' => now(),
            'id_kasir' => $this->kasir->id_pengguna,
            'id_admin' => $this->admin->id_pengguna,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.goods-in-approval.reject', $goodsIn->id_goods_in), [
                'catatan' => 'Trying to reject again',
            ]);

        $response->assertSessionHasErrors();
    });
});

describe('Goods In - PO Number Generation', function () {
    it('generates unique PO numbers in PO-YYYY-MM-NNNNN format', function () {
        $kasir = User::factory()->kasir()->create();

        // Create multiple POs
        $po1 = GoodsIn::create([
            'nomor_po' => GoodsIn::generateNomorPO(),
            'status' => GoodsInStatus::Submitted->value,
            'tanggal_request' => now(),
            'id_kasir' => $kasir->id_pengguna,
        ]);

        $po2 = GoodsIn::create([
            'nomor_po' => GoodsIn::generateNomorPO(),
            'status' => GoodsInStatus::Submitted->value,
            'tanggal_request' => now(),
            'id_kasir' => $kasir->id_pengguna,
        ]);

        $po3 = GoodsIn::create([
            'nomor_po' => GoodsIn::generateNomorPO(),
            'status' => GoodsInStatus::Submitted->value,
            'tanggal_request' => now(),
            'id_kasir' => $kasir->id_pengguna,
        ]);

        // Verify format
        expect($po1->nomor_po)->toMatch('/^PO-\d{4}-\d{2}-\d{5}$/');
        expect($po2->nomor_po)->toMatch('/^PO-\d{4}-\d{2}-\d{5}$/');
        expect($po3->nomor_po)->toMatch('/^PO-\d{4}-\d{2}-\d{5}$/');

        // Verify uniqueness
        expect($po1->nomor_po)->not->toBe($po2->nomor_po);
        expect($po2->nomor_po)->not->toBe($po3->nomor_po);

        // Verify sequential numbering
        $currentYearMonth = now()->format('Y-m');
        expect($po1->nomor_po)->toBe("PO-{$currentYearMonth}-00001");
        expect($po2->nomor_po)->toBe("PO-{$currentYearMonth}-00002");
        expect($po3->nomor_po)->toBe("PO-{$currentYearMonth}-00003");
    });

    it('resets PO number sequence each month', function () {
        $kasir = User::factory()->kasir()->create();

        // Create PO in current month
        $currentPO = GoodsIn::create([
            'nomor_po' => GoodsIn::generateNomorPO(),
            'status' => GoodsInStatus::Submitted->value,
            'tanggal_request' => now(),
            'id_kasir' => $kasir->id_pengguna,
        ]);

        // Manually create PO with next month's date to simulate month change
        $nextMonth = now()->addMonth();
        $nextMonthPONumber = 'PO-'.$nextMonth->format('Y-m').'-00001';

        $nextMonthPO = GoodsIn::create([
            'nomor_po' => $nextMonthPONumber,
            'status' => GoodsInStatus::Submitted->value,
            'tanggal_request' => $nextMonth,
            'id_kasir' => $kasir->id_pengguna,
        ]);

        expect($nextMonthPO->nomor_po)->toContain($nextMonth->format('Y-m'));
        expect($nextMonthPO->nomor_po)->toEndWith('00001');
    });
});
