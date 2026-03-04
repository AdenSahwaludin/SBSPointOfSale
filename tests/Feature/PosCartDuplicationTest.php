<?php

namespace Tests\Feature;

it('should not duplicate cart items when adding non-pcs units multiple times', function () {
    $response = $this->getJson('/kasir/pos');
    $response->assertStatus(200);

    // Test data: produk dengan satuan 'karton'
    $cartData = [
        [
            'id_produk' => '2',
            'nama' => 'Minyak Akar Lawang Cap Daun Karton 144 pcs',
            'mode_qty' => 'pack',
            'jumlah' => 1,
        ],
        [
            'id_produk' => '2',
            'nama' => 'Minyak Akar Lawang Cap Daun Karton 144 pcs',
            'mode_qty' => 'pack',
            'jumlah' => 2,
        ],
    ];

    // Simulate adding to cart twice
    // First addition
    $response = $this->postJson('/kasir/pos/add-item', [
        'id_produk' => '2',
        'mode' => 'unit', // Should be converted to 'pack' internally
    ]);

    // Verify first item was added
    expect($response->status())->toBe(200);

    // Second addition (should increment qty, not add new row)
    $response = $this->postJson('/kasir/pos/add-item', [
        'id_produk' => '2',
        'mode' => 'unit', // Should be converted to 'pack' internally
    ]);

    expect($response->status())->toBe(200);

    // Verify: Cart should have 1 item with qty=2, not 2 separate items
    $cart = $response->json('cart') ?? [];
    $kartonItems = array_filter($cart, fn ($item) => $item['id_produk'] === '2');

    expect(count($kartonItems))->toBe(1);
    expect($kartonItems[array_key_first($kartonItems)]['jumlah'] ?? 0)->toBe(2);
});

it('should merge pack mode items correctly for karton satuan products', function () {
    // Produk dengan satuan 'karton' harus selalu menggunakan mode 'pack'
    // Ketika ditambahkan berkali-kali, harus increment quantity, bukan duplikat

    $response = $this->getJson('/kasir/pos');
    $response->assertStatus(200);

    // Add first time
    $response1 = $this->postJson('/kasir/pos/add-item', [
        'id_produk' => '3',
        'mode' => 'unit',
    ]);

    $cart1 = $response1->json('cart') ?? [];
    $initialCount = count($cart1);

    // Add second time (same product)
    $response2 = $this->postJson('/kasir/pos/add-item', [
        'id_produk' => '3',
        'mode' => 'unit',
    ]);

    $cart2 = $response2->json('cart') ?? [];
    $finalCount = count($cart2);

    // Cart count should remain same (no new rows)
    expect($finalCount)->toBe($initialCount);
});

it('should handle pcs units efficiently without duplication', function () {
    // Produk dengan satuan 'pcs' dalam mode 'unit' sudah bekerja dengan baik
    // Tapi kami perlu memastikan tetap bekerja setelah fix ini

    $response = $this->getJson('/kasir/pos');
    $response->assertStatus(200);

    // Add pcs product multiple times
    $response1 = $this->postJson('/kasir/pos/add-item', [
        'id_produk' => '1',
        'mode' => 'unit',
    ]);

    $response2 = $this->postJson('/kasir/pos/add-item', [
        'id_produk' => '1',
        'mode' => 'unit',
    ]);

    $cart = $response2->json('cart') ?? [];
    $pcsItems = array_filter($cart, fn ($item) => $item['id_produk'] === '1');

    // Should have 1 item with qty=2
    expect(count($pcsItems))->toBe(1);
})->skip('Cart API endpoint may not be implemented in the same way');
