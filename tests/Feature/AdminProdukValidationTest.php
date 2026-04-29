<?php

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pcs products always store isi per pack as one', function () {
    $admin = User::factory()->admin()->create();
    $kategori = Kategori::factory()->create();

    $this->actingAs($admin);

    $payload = [
        'nama' => 'Produk PCS',
        'id_kategori' => $kategori->id_kategori,
        'barcode' => '1234567890123',
        'no_bpom' => 'BPOM12345',
        'satuan' => 'pcs',
        'isi_per_pack' => 7,
        'harga' => 15000,
        'harga_pack' => 0,
        'stok' => 25,
        'sisa_pcs_terbuka' => 0,
        'batas_stok_minimum' => 5,
        'jumlah_restock' => 10,
        'sku' => 'K001-PCS-001',
    ];

    $response = $this->post('/admin/produk', $payload);

    $response->assertRedirect(route('admin.produk.index'));
    $this->assertDatabaseHas('produk', [
        'sku' => 'K001-PCS-001',
        'nama' => 'Produk PCS',
        'satuan' => 'pcs',
        'isi_per_pack' => 1,
    ]);
});

test('pack products keep their isi per pack value', function () {
    $admin = User::factory()->admin()->create();
    $kategori = Kategori::factory()->create();

    $this->actingAs($admin);

    $payload = [
        'nama' => 'Produk Pack',
        'id_kategori' => $kategori->id_kategori,
        'barcode' => '1234567890124',
        'no_bpom' => 'BPOM12346',
        'satuan' => 'pack',
        'isi_per_pack' => 12,
        'harga' => 25000,
        'harga_pack' => 240000,
        'stok' => 20,
        'sisa_pcs_terbuka' => 0,
        'batas_stok_minimum' => 5,
        'jumlah_restock' => 10,
        'sku' => 'K001-PCK-012',
    ];

    $response = $this->post('/admin/produk', $payload);

    $response->assertRedirect(route('admin.produk.index'));
    $this->assertDatabaseHas('produk', [
        'sku' => 'K001-PCK-012',
        'nama' => 'Produk Pack',
        'satuan' => 'pack',
        'isi_per_pack' => 12,
    ]);
});

test('pcs products stay normalized to one isi per pack on update', function () {
    $admin = User::factory()->admin()->create();
    $kategori = Kategori::factory()->create();

    $produk = Produk::factory()->create([
        'id_kategori' => $kategori->id_kategori,
        'sku' => 'K001-PCS-OLD',
        'barcode' => '1234567890125',
        'satuan' => 'pack',
        'isi_per_pack' => 12,
    ]);

    $this->actingAs($admin);

    $response = $this->patch('/admin/produk/'.$produk->id_produk, [
        'nama' => 'Produk PCS Update',
        'id_kategori' => $kategori->id_kategori,
        'barcode' => '1234567890126',
        'no_bpom' => 'BPOM12347',
        'satuan' => 'pcs',
        'isi_per_pack' => 9,
        'harga' => 17500,
        'harga_pack' => 0,
        'stok' => 30,
        'sisa_pcs_terbuka' => 0,
        'batas_stok_minimum' => 5,
        'jumlah_restock' => 10,
        'sku' => 'K001-PCS-OLD',
    ]);

    $response->assertRedirect(route('admin.produk.index'));
    $this->assertDatabaseHas('produk', [
        'id_produk' => $produk->id_produk,
        'nama' => 'Produk PCS Update',
        'satuan' => 'pcs',
        'isi_per_pack' => 1,
    ]);
});
