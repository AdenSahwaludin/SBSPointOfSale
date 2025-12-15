<?php

use App\GoodsInStatus;
use App\Models\GoodsIn;
use App\Models\Produk;
use App\Models\User;
use App\Services\GoodsInService;

beforeEach(function () {
    $this->service = new GoodsInService;
});

test('can create PO request', function () {
    $kasir = User::create([
        'id_pengguna' => 'KSR001',
        'nama' => 'Test Kasir',
        'email' => 'kasir@test.com',
        'password' => 'password',
        'role' => 'kasir',
    ]);
    $produk = Produk::factory()->create();

    $items = [
        ['id_produk' => $produk->id_produk, 'jumlah_dipesan' => 10],
    ];

    $po = $this->service->createPORequest($kasir->id_pengguna, $items);

    expect($po)->toBeInstanceOf(GoodsIn::class)
        ->and($po->status)->toBe(GoodsInStatus::Draft->value)
        ->and($po->id_kasir)->toBe($kasir->id_pengguna)
        ->and($po->details)->toHaveCount(1)
        ->and($po->details->first()->jumlah_dipesan)->toBe(10);
});

test('can approve PO', function () {
    $kasir = User::create([
        'id_pengguna' => 'KSR002',
        'nama' => 'Test Kasir',
        'email' => 'kasir2@test.com',
        'password' => 'password',
        'role' => 'kasir',
    ]);
    $admin = User::create([
        'id_pengguna' => 'ADM001',
        'nama' => 'Test Admin',
        'email' => 'admin@test.com',
        'password' => 'password',
        'role' => 'admin',
    ]);
    $produk = Produk::factory()->create();

    $items = [
        ['id_produk' => $produk->id_produk, 'jumlah_dipesan' => 10],
    ];

    $po = $this->service->createPORequest($kasir->id_pengguna, $items);
    $submittedPO = $this->service->submitGoodsIn($po);
    $approvedPO = $this->service->approvePO($submittedPO->id_goods_in, $admin->id_pengguna, 'Approved');

    expect($approvedPO->status)->toBe(GoodsInStatus::Approved->value)
        ->and($approvedPO->id_admin)->toBe($admin->id_pengguna)
        ->and($approvedPO->catatan_approval)->toBe('Approved')
        ->and($approvedPO->tanggal_approval)->not->toBeNull();
});

test('can reject PO', function () {
    $kasir = User::create([
        'id_pengguna' => 'KSR003',
        'nama' => 'Test Kasir',
        'email' => 'kasir3@test.com',
        'password' => 'password',
        'role' => 'kasir',
    ]);
    $admin = User::create([
        'id_pengguna' => 'ADM002',
        'nama' => 'Test Admin',
        'email' => 'admin2@test.com',
        'password' => 'password',
        'role' => 'admin',
    ]);
    $produk = Produk::factory()->create();

    $items = [
        ['id_produk' => $produk->id_produk, 'jumlah_dipesan' => 10],
    ];

    $po = $this->service->createPORequest($kasir->id_pengguna, $items);
    $submittedPO = $this->service->submitGoodsIn($po);
    $rejectedPO = $this->service->rejectPO($submittedPO->id_goods_in, $admin->id_pengguna, 'Out of budget');

    expect($rejectedPO->status)->toBe(GoodsInStatus::Rejected->value)
        ->and($rejectedPO->id_admin)->toBe($admin->id_pengguna)
        ->and($rejectedPO->catatan_approval)->toBe('Out of budget');
});

test('can get pending POs', function () {
    $kasir = User::create([
        'id_pengguna' => 'KSR004',
        'nama' => 'Test Kasir',
        'email' => 'kasir4@test.com',
        'password' => 'password',
        'role' => 'kasir',
    ]);
    $produk = Produk::factory()->create();

    $items = [
        ['id_produk' => $produk->id_produk, 'jumlah_dipesan' => 10],
    ];

    $po1 = $this->service->createPORequest($kasir->id_pengguna, $items);
    $po2 = $this->service->createPORequest($kasir->id_pengguna, $items);
    $this->service->submitGoodsIn($po1);
    $this->service->submitGoodsIn($po2);

    $pendingPOs = $this->service->getPendingPOs();

    expect($pendingPOs)->toHaveCount(2)
        ->and($pendingPOs->first()->status)->toBe(GoodsInStatus::Submitted->value);
});

test('can get POs by kasir', function () {
    $kasir1 = User::create([
        'id_pengguna' => 'KSR005',
        'nama' => 'Test Kasir 1',
        'email' => 'kasir5@test.com',
        'password' => 'password',
        'role' => 'kasir',
    ]);
    $kasir2 = User::create([
        'id_pengguna' => 'KSR006',
        'nama' => 'Test Kasir 2',
        'email' => 'kasir6@test.com',
        'password' => 'password',
        'role' => 'kasir',
    ]);
    $produk = Produk::factory()->create();

    $items = [
        ['id_produk' => $produk->id_produk, 'jumlah_dipesan' => 10],
    ];

    $this->service->createPORequest($kasir1->id_pengguna, $items);
    $this->service->createPORequest($kasir1->id_pengguna, $items);
    $this->service->createPORequest($kasir2->id_pengguna, $items);

    $kasir1POs = $this->service->getPOsByKasir($kasir1->id_pengguna);

    expect($kasir1POs)->toHaveCount(2)
        ->and($kasir1POs->first()->id_kasir)->toBe($kasir1->id_pengguna);
});
