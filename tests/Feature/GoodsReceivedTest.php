<?php

use App\Models\GoodsIn;
use App\Models\GoodsInDetail;
use App\Models\Produk;
use App\Models\User;
use App\Services\GoodsInService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->kasir = User::factory()->create(['role' => 'kasir']);
    $this->admin = User::factory()->create(['role' => 'admin']);

    $this->produk1 = Produk::factory()->create(['nama' => 'Produk A', 'sku' => 'SKU-001']);
    $this->produk2 = Produk::factory()->create(['nama' => 'Produk B', 'sku' => 'SKU-002']);

    $this->service = app(GoodsInService::class);
});

it('can show list of approved POs for receiving', function () {
    // Create approved PO
    $po = GoodsIn::factory()
        ->for($this->kasir, 'kasir')
        ->for($this->admin, 'admin')
        ->state(['status' => 'approved'])
        ->create();

    GoodsInDetail::factory()
        ->for($po)
        ->for($this->produk1, 'produk')
        ->create(['jumlah_dipesan' => 10, 'jumlah_diterima' => 0]);

    $response = $this->actingAs($this->kasir)
        ->get(route('kasir.goods-in-receiving.index'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Kasir/GoodsIn/ReceivingIndex')
        ->has('approvedPOs', 1)
    );
});

it('can show receiving form for approved PO', function () {
    $po = GoodsIn::factory()
        ->for($this->kasir, 'kasir')
        ->for($this->admin, 'admin')
        ->state(['status' => 'approved'])
        ->create();

    GoodsInDetail::factory()
        ->for($po)
        ->for($this->produk1, 'produk')
        ->create(['jumlah_dipesan' => 10, 'jumlah_diterima' => 0]);

    $response = $this->actingAs($this->kasir)
        ->get(route('kasir.goods-in.receiving-show', $po->id_goods_in));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Kasir/GoodsIn/ReceivingShow')
        ->has('goodsIn')
        ->has('pendingItems', 1)
    );
});

it('cannot show receiving form if PO is not approved', function () {
    $po = GoodsIn::factory()
        ->for($this->kasir, 'kasir')
        ->state(['status' => 'draft'])
        ->create();

    $response = $this->actingAs($this->kasir)
        ->get(route('kasir.goods-in.receiving-show', $po->id_goods_in));

    $response->assertStatus(403);
});

it('can record received goods for approved PO', function () {
    $po = GoodsIn::factory()
        ->for($this->kasir, 'kasir')
        ->for($this->admin, 'admin')
        ->state(['status' => 'approved'])
        ->create();

    $detail = GoodsInDetail::factory()
        ->for($po)
        ->for($this->produk1, 'produk')
        ->create(['jumlah_dipesan' => 10, 'jumlah_diterima' => 0]);

    $response = $this->actingAs($this->kasir)
        ->post(route('kasir.goods-in.record-received', $po->id_goods_in), [
            'items' => [
                [
                    'id_goods_in_detail' => $detail->id_goods_in_detail,
                    'jumlah_diterima' => 5,
                    'catatan' => 'Sebagian barang diterima',
                ],
            ],
        ]);

    $response->assertRedirect(route('kasir.goods-in.receiving-show', $po->id_goods_in));

    // Check goods received was created
    $this->assertDatabaseHas('penerimaan_barang', [
        'id_goods_in' => $po->id_goods_in,
        'id_goods_in_detail' => $detail->id_goods_in_detail,
        'jumlah_diterima' => 5,
        'id_kasir' => $this->kasir->id_pengguna,
    ]);

    // Check jumlah_diterima was updated
    $detail->refresh();
    $this->assertEquals(5, $detail->jumlah_diterima);
});

it('can record received goods for multiple items', function () {
    $po = GoodsIn::factory()
        ->for($this->kasir, 'kasir')
        ->for($this->admin, 'admin')
        ->state(['status' => 'approved'])
        ->create();

    $detail1 = GoodsInDetail::factory()
        ->for($po)
        ->for($this->produk1, 'produk')
        ->create(['jumlah_dipesan' => 10, 'jumlah_diterima' => 0]);

    $detail2 = GoodsInDetail::factory()
        ->for($po)
        ->for($this->produk2, 'produk')
        ->create(['jumlah_dipesan' => 20, 'jumlah_diterima' => 0]);

    $response = $this->actingAs($this->kasir)
        ->post(route('kasir.goods-in.record-received', $po->id_goods_in), [
            'items' => [
                [
                    'id_goods_in_detail' => $detail1->id_goods_in_detail,
                    'jumlah_diterima' => 10,
                ],
                [
                    'id_goods_in_detail' => $detail2->id_goods_in_detail,
                    'jumlah_diterima' => 15,
                    'catatan' => 'Sisa 5 akan datang minggu depan',
                ],
            ],
        ]);

    $response->assertRedirect();

    // Check both were created
    $this->assertDatabaseCount('penerimaan_barang', 2);
    $detail1->refresh();
    $detail2->refresh();
    $this->assertEquals(10, $detail1->jumlah_diterima);
    $this->assertEquals(15, $detail2->jumlah_diterima);
});

it('cannot record received goods for draft PO', function () {
    $po = GoodsIn::factory()
        ->for($this->kasir, 'kasir')
        ->state(['status' => 'draft'])
        ->create();

    $detail = GoodsInDetail::factory()
        ->for($po)
        ->for($this->produk1, 'produk')
        ->create();

    $response = $this->actingAs($this->kasir)
        ->post(route('kasir.goods-in.record-received', $po->id_goods_in), [
            'items' => [
                [
                    'id_goods_in_detail' => $detail->id_goods_in_detail,
                    'jumlah_diterima' => 5,
                ],
            ],
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors('error');
});

it('validates required jumlah_diterima field', function () {
    $po = GoodsIn::factory()
        ->for($this->kasir, 'kasir')
        ->for($this->admin, 'admin')
        ->state(['status' => 'approved'])
        ->create();

    $detail = GoodsInDetail::factory()
        ->for($po)
        ->for($this->produk1, 'produk')
        ->create();

    $response = $this->actingAs($this->kasir)
        ->post(route('kasir.goods-in.record-received', $po->id_goods_in), [
            'items' => [
                [
                    'id_goods_in_detail' => $detail->id_goods_in_detail,
                    // jumlah_diterima missing
                ],
            ],
        ]);

    $response->assertInvalid('items.0.jumlah_diterima');
});

it('validates jumlah_diterima is at least 1', function () {
    $po = GoodsIn::factory()
        ->for($this->kasir, 'kasir')
        ->for($this->admin, 'admin')
        ->state(['status' => 'approved'])
        ->create();

    $detail = GoodsInDetail::factory()
        ->for($po)
        ->for($this->produk1, 'produk')
        ->create();

    $response = $this->actingAs($this->kasir)
        ->post(route('kasir.goods-in.record-received', $po->id_goods_in), [
            'items' => [
                [
                    'id_goods_in_detail' => $detail->id_goods_in_detail,
                    'jumlah_diterima' => 0,
                ],
            ],
        ]);

    $response->assertInvalid('items.0.jumlah_diterima');
});

it('requires at least one item to record', function () {
    $po = GoodsIn::factory()
        ->for($this->kasir, 'kasir')
        ->for($this->admin, 'admin')
        ->state(['status' => 'approved'])
        ->create();

    $response = $this->actingAs($this->kasir)
        ->post(route('kasir.goods-in.record-received', $po->id_goods_in), [
            'items' => [],
        ]);

    $response->assertInvalid('items');
});

it('can retrieve received goods for a PO', function () {
    $po = GoodsIn::factory()
        ->for($this->kasir, 'kasir')
        ->for($this->admin, 'admin')
        ->state(['status' => 'approved'])
        ->create();

    $detail = GoodsInDetail::factory()
        ->for($po)
        ->for($this->produk1, 'produk')
        ->create(['jumlah_dipesan' => 10]);

    $this->service->recordReceivedGoods($po, [
        [
            'id_goods_in_detail' => $detail->id_goods_in_detail,
            'jumlah_diterima' => 5,
        ],
    ], $this->kasir->id_pengguna);

    $receivedGoods = $this->service->getReceivedGoodsByPO($po);

    $this->assertCount(1, $receivedGoods);
    $this->assertEquals(5, $receivedGoods[0]->jumlah_diterima);
});

it('can track multiple receives for same item', function () {
    $po = GoodsIn::factory()
        ->for($this->kasir, 'kasir')
        ->for($this->admin, 'admin')
        ->state(['status' => 'approved'])
        ->create();

    $detail = GoodsInDetail::factory()
        ->for($po)
        ->for($this->produk1, 'produk')
        ->create(['jumlah_dipesan' => 30, 'jumlah_diterima' => 0]);

    // First receive
    $this->service->recordReceivedGoods($po, [
        [
            'id_goods_in_detail' => $detail->id_goods_in_detail,
            'jumlah_diterima' => 10,
        ],
    ], $this->kasir->id_pengguna);

    $detail->refresh();
    $this->assertEquals(10, $detail->jumlah_diterima);

    // Second receive
    $this->service->recordReceivedGoods($po, [
        [
            'id_goods_in_detail' => $detail->id_goods_in_detail,
            'jumlah_diterima' => 15,
        ],
    ], $this->kasir->id_pengguna);

    $detail->refresh();
    $this->assertEquals(25, $detail->jumlah_diterima);

    // Should have 2 penerimaan_barang records
    $receivedGoods = $this->service->getReceivedGoodsByPO($po);
    $this->assertCount(2, $receivedGoods);
});

it('increments product stock when recording received goods', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);
    $admin = User::factory()->create(['role' => 'admin']);
    $produk = Produk::factory()->create(['nama' => 'Produk Test', 'sku' => 'SKU-TEST', 'stok' => 100]);
    $service = app(GoodsInService::class);

    $po = GoodsIn::factory()
        ->for($kasir, 'kasir')
        ->for($admin, 'admin')
        ->state(['status' => 'approved'])
        ->create();

    $detail = GoodsInDetail::factory()
        ->for($po)
        ->for($produk, 'produk')
        ->create(['jumlah_dipesan' => 20, 'jumlah_diterima' => 0]);

    $initialStock = $produk->stok;

    // Record received goods
    $service->recordReceivedGoods($po, [
        [
            'id_goods_in_detail' => $detail->id_goods_in_detail,
            'jumlah_diterima' => 20,
        ],
    ], $kasir->id_pengguna);

    // Verify product stock increased
    $produk->refresh();
    expect($produk->stok)->toBe($initialStock + 20);
});

it('can record damaged goods and only increments stock with good items', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);
    $admin = User::factory()->create(['role' => 'admin']);
    $produk = Produk::factory()->create(['nama' => 'Produk Test', 'sku' => 'SKU-TEST', 'stok' => 100]);
    $service = app(GoodsInService::class);

    $po = GoodsIn::factory()
        ->for($kasir, 'kasir')
        ->for($admin, 'admin')
        ->state(['status' => 'approved'])
        ->create();

    $detail = GoodsInDetail::factory()
        ->for($po)
        ->for($produk, 'produk')
        ->create(['jumlah_dipesan' => 20, 'jumlah_diterima' => 0]);

    $initialStock = $produk->stok;

    // Record received goods with damaged items
    $service->recordReceivedGoods($po, [
        [
            'id_goods_in_detail' => $detail->id_goods_in_detail,
            'jumlah_diterima' => 20,
            'jumlah_rusak' => 3, // 3 damaged
        ],
    ], $kasir->id_pengguna);

    // Verify product stock only increased by good items (20 - 3 = 17)
    $produk->refresh();
    expect($produk->stok)->toBe($initialStock + 17);

    // Verify penerimaan_barang record has jumlah_rusak
    $this->assertDatabaseHas('penerimaan_barang', [
        'id_goods_in_detail' => $detail->id_goods_in_detail,
        'jumlah_diterima' => 20,
        'jumlah_rusak' => 3,
    ]);
});

it('updates PO status to partial_received when not all items fully received', function () {
    $po = GoodsIn::factory()
        ->for($this->kasir, 'kasir')
        ->for($this->admin, 'admin')
        ->state(['status' => 'approved'])
        ->create();

    $detail = GoodsInDetail::factory()
        ->for($po)
        ->for($this->produk1, 'produk')
        ->create(['jumlah_dipesan' => 20, 'jumlah_diterima' => 0]);

    // Receive only 10 out of 20
    $this->service->recordReceivedGoods($po, [
        [
            'id_goods_in_detail' => $detail->id_goods_in_detail,
            'jumlah_diterima' => 10,
        ],
    ], $this->kasir->id_pengguna);

    // Verify PO status updated to partial_received
    $po->refresh();
    expect($po->status)->toBe('partial_received');
});

it('updates PO status to received when all items fully received', function () {
    $po = GoodsIn::factory()
        ->for($this->kasir, 'kasir')
        ->for($this->admin, 'admin')
        ->state(['status' => 'approved'])
        ->create();

    $detail1 = GoodsInDetail::factory()
        ->for($po)
        ->for($this->produk1, 'produk')
        ->create(['jumlah_dipesan' => 20, 'jumlah_diterima' => 0]);

    $detail2 = GoodsInDetail::factory()
        ->for($po)
        ->for($this->produk2, 'produk')
        ->create(['jumlah_dipesan' => 15, 'jumlah_diterima' => 0]);

    // Receive all items
    $this->service->recordReceivedGoods($po, [
        [
            'id_goods_in_detail' => $detail1->id_goods_in_detail,
            'jumlah_diterima' => 20,
        ],
        [
            'id_goods_in_detail' => $detail2->id_goods_in_detail,
            'jumlah_diterima' => 15,
        ],
    ], $this->kasir->id_pengguna);

    // Verify PO status updated to received
    $po->refresh();
    expect($po->status)->toBe('received');
});

it('allows multiple partial receives until completion', function () {
    $po = GoodsIn::factory()
        ->for($this->kasir, 'kasir')
        ->for($this->admin, 'admin')
        ->state(['status' => 'approved'])
        ->create();

    $detail = GoodsInDetail::factory()
        ->for($po)
        ->for($this->produk1, 'produk')
        ->create(['jumlah_dipesan' => 30, 'jumlah_diterima' => 0]);

    // First partial receive - 10 items
    $this->service->recordReceivedGoods($po, [
        [
            'id_goods_in_detail' => $detail->id_goods_in_detail,
            'jumlah_diterima' => 10,
        ],
    ], $this->kasir->id_pengguna);

    $po->refresh();
    expect($po->status)->toBe('partial_received');

    // Second partial receive - 15 more items (total 25)
    $this->service->recordReceivedGoods($po, [
        [
            'id_goods_in_detail' => $detail->id_goods_in_detail,
            'jumlah_diterima' => 15,
        ],
    ], $this->kasir->id_pengguna);

    $po->refresh();
    expect($po->status)->toBe('partial_received'); // Still partial

    // Final receive - 5 more items (total 30 = complete)
    $this->service->recordReceivedGoods($po, [
        [
            'id_goods_in_detail' => $detail->id_goods_in_detail,
            'jumlah_diterima' => 5,
        ],
    ], $this->kasir->id_pengguna);

    $po->refresh();
    $detail->refresh();
    expect($po->status)->toBe('received');
    expect($detail->jumlah_diterima)->toBe(30);
});
