<?php

use App\Models\Kategori;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('product stock decreases after a successful POS transaction', function () {
    $kasir = User::factory()->kasir()->create();
    $kategori = Kategori::factory()->create();
    $pelanggan = Pelanggan::factory()->create();
    $produk = Produk::factory()->create([
        'id_kategori' => $kategori->id_kategori,
        'stok' => 10,
        'harga' => 5000,
        'satuan' => 'pcs',
        'isi_per_pack' => 1,
    ]);

    $this->actingAs($kasir);

    $response = $this->postJson('/kasir/pos', [
        'id_pelanggan' => $pelanggan->id_pelanggan,
        'items' => [
            [
                'id_produk' => $produk->id_produk,
                'jumlah' => 2,
                'mode_qty' => 'unit',
                'harga_satuan' => 5000,
            ],
        ],
        'metode_bayar' => 'TUNAI',
        'subtotal' => 10000,
        'diskon' => 0,
        'pajak' => 0,
        'total' => 10000,
        'jumlah_bayar' => 10000,
    ]);

    $response->assertSuccessful();
    $response->assertJson(['success' => true]);

    expect($produk->refresh()->stok)->toBe(8);
});
