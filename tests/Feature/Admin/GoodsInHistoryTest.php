<?php

use App\Models\GoodsIn;
use App\Models\GoodsInDetail;
use App\Models\Produk;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('displays goods in history index with pagination', function () {
    $admin = User::factory()->admin()->create();
    $produk = Produk::factory()->create();

    $goodsIn = GoodsIn::factory()
        ->create(['id_kasir' => User::factory()->create()->id_pengguna, 'status' => 'approved']);

    GoodsInDetail::factory()
        ->create(['id_goods_in' => $goodsIn->id_goods_in, 'id_produk' => $produk->id_produk]);

    $response = $this->actingAs($admin)->get('/admin/goods-in-history');

    $response->assertSuccessful();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Admin/GoodsInHistory/Index')
        ->has('poHistory')
        ->has('availableStatuses')
    );
});

it('filters goods in history by search query', function () {
    $admin = User::factory()->admin()->create();
    $produk = Produk::factory()->create();

    $goodsIn = GoodsIn::factory()
        ->create(['id_kasir' => User::factory()->create()->id_pengguna, 'nomor_po' => 'PO-001', 'status' => 'approved']);

    GoodsInDetail::factory()
        ->create(['id_goods_in' => $goodsIn->id_goods_in, 'id_produk' => $produk->id_produk]);

    $response = $this->actingAs($admin)
        ->get('/admin/goods-in-history?search=PO-001');

    $response->assertSuccessful();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->where('filters.search', 'PO-001')
    );
});

it('filters goods in history by status', function () {
    $admin = User::factory()->admin()->create();
    $produk = Produk::factory()->create();

    $goodsIn = GoodsIn::factory()
        ->create(['id_kasir' => User::factory()->create()->id_pengguna, 'status' => 'received']);

    GoodsInDetail::factory()
        ->create(['id_goods_in' => $goodsIn->id_goods_in, 'id_produk' => $produk->id_produk]);

    $response = $this->actingAs($admin)
        ->get('/admin/goods-in-history?status=received');

    $response->assertSuccessful();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->where('filters.status', 'received')
    );
});

it('filters goods in history by date range', function () {
    $admin = User::factory()->admin()->create();
    $produk = Produk::factory()->create();

    $goodsIn = GoodsIn::factory()
        ->create(['id_kasir' => User::factory()->create()->id_pengguna, 'status' => 'approved', 'tanggal_request' => now()]);

    GoodsInDetail::factory()
        ->create(['id_goods_in' => $goodsIn->id_goods_in, 'id_produk' => $produk->id_produk]);

    $startDate = now()->subDays(5)->format('Y-m-d');
    $endDate = now()->addDays(5)->format('Y-m-d');

    $response = $this->actingAs($admin)
        ->get("/admin/goods-in-history?start_date={$startDate}&end_date={$endDate}");

    $response->assertSuccessful();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->where('filters.start_date', $startDate)
        ->where('filters.end_date', $endDate)
    );
});

it('displays goods in history show page with details', function () {
    $admin = User::factory()->admin()->create();
    $produk = Produk::factory()->create();

    $goodsIn = GoodsIn::factory()
        ->create(['id_kasir' => User::factory()->create()->id_pengguna, 'status' => 'received']);

    GoodsInDetail::factory()
        ->count(3)
        ->create(['id_goods_in' => $goodsIn->id_goods_in, 'id_produk' => $produk->id_produk]);

    $response = $this->actingAs($admin)->get("/admin/goods-in-history/{$goodsIn->id_goods_in}");

    $response->assertSuccessful();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('Admin/GoodsInHistory/Show')
        ->where('po.id_goods_in', $goodsIn->id_goods_in)
        ->where('po.nomor_po', $goodsIn->nomor_po)
        ->where('po.status', 'received')
        ->has('po.details', 3)
    );
});

it('shows 404 when goods in history not found', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/admin/goods-in-history/999');

    $response->assertNotFound();
});
