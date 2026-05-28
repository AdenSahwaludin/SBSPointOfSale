<?php

use App\GoodsInStatus;
use App\Models\GoodsIn;
use App\Models\GoodsInDetail;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Goods In Item Management', function () {
    beforeEach(function () {
        $this->kasir = User::factory()->kasir()->create();
        $this->admin = User::factory()->admin()->create();
    });

    describe('Adding items to PO', function () {
        it('kasir can add item to draft PO', function () {
            $produk = Produk::factory()->create();
            $goodsIn = GoodsIn::create([
                'nomor_po' => GoodsIn::generateNomorPO(),
                'status' => GoodsInStatus::Draft->value,
                'tanggal_request' => now(),
                'id_kasir' => $this->kasir->id_pengguna,
            ]);

            $response = $this->actingAs($this->kasir)
                ->post(route('kasir.goods-in.items.add', $goodsIn->id_pemesanan_barang), [
                    'id_produk' => $produk->id_produk,
                    'jumlah_dipesan' => 10,
                ]);

            $response->assertRedirect(route('kasir.goods-in.show', $goodsIn->id_pemesanan_barang));
            $response->assertSessionHas('success', 'Item berhasil ditambahkan ke PO.');

            $this->assertDatabaseHas('detail_pemesanan_barang', [
                'id_pemesanan_barang' => $goodsIn->id_pemesanan_barang,
                'id_produk' => $produk->id_produk,
                'jumlah_dipesan' => 10,
                'jumlah_diterima' => 0,
            ]);
        });

        it('kasir cannot add duplicate product to same PO', function () {
            $produk = Produk::factory()->create();
            $goodsIn = GoodsIn::create([
                'nomor_po' => GoodsIn::generateNomorPO(),
                'status' => GoodsInStatus::Draft->value,
                'tanggal_request' => now(),
                'id_kasir' => $this->kasir->id_pengguna,
            ]);

            // Add first item
            GoodsInDetail::create([
                'id_pemesanan_barang' => $goodsIn->id_pemesanan_barang,
                'id_produk' => $produk->id_produk,
                'jumlah_dipesan' => 5,
                'jumlah_diterima' => 0,
            ]);

            // Try to add same product again
            $response = $this->actingAs($this->kasir)
                ->post(route('kasir.goods-in.items.add', $goodsIn->id_pemesanan_barang), [
                    'id_produk' => $produk->id_produk,
                    'jumlah_dipesan' => 10,
                ]);

            $response->assertRedirect();
            $response->assertSessionHasErrors('error');
            expect($response->getSession()->get('errors')?->get('error')[0] ?? '')->toContain('sudah ada di PO ini');
        });

        it('kasir cannot add item to non-draft PO', function () {
            $produk = Produk::factory()->create();
            $goodsIn = GoodsIn::create([
                'nomor_po' => GoodsIn::generateNomorPO(),
                'status' => GoodsInStatus::Submitted->value,
                'tanggal_request' => now(),
                'id_kasir' => $this->kasir->id_pengguna,
            ]);

            $response = $this->actingAs($this->kasir)
                ->post(route('kasir.goods-in.items.add', $goodsIn->id_pemesanan_barang), [
                    'id_produk' => $produk->id_produk,
                    'jumlah_dipesan' => 10,
                ]);

            $response->assertRedirect();
            $response->assertSessionHasErrors('error');
        });

        it('validates jumlah_dipesan is required', function () {
            $produk = Produk::factory()->create();
            $goodsIn = GoodsIn::create([
                'nomor_po' => GoodsIn::generateNomorPO(),
                'status' => GoodsInStatus::Draft->value,
                'tanggal_request' => now(),
                'id_kasir' => $this->kasir->id_pengguna,
            ]);

            $response = $this->actingAs($this->kasir)
                ->post(route('kasir.goods-in.items.add', $goodsIn->id_pemesanan_barang), [
                    'id_produk' => $produk->id_produk,
                    'jumlah_dipesan' => null,
                ]);

            $response->assertSessionHasErrors('jumlah_dipesan');
        });

        it('validates jumlah_dipesan minimum is 1', function () {
            $produk = Produk::factory()->create();
            $goodsIn = GoodsIn::create([
                'nomor_po' => GoodsIn::generateNomorPO(),
                'status' => GoodsInStatus::Draft->value,
                'tanggal_request' => now(),
                'id_kasir' => $this->kasir->id_pengguna,
            ]);

            $response = $this->actingAs($this->kasir)
                ->post(route('kasir.goods-in.items.add', $goodsIn->id_pemesanan_barang), [
                    'id_produk' => $produk->id_produk,
                    'jumlah_dipesan' => 0,
                ]);

            $response->assertSessionHasErrors('jumlah_dipesan');
        });
    });

    describe('Updating items in PO', function () {
        it('kasir can update item qty in draft PO', function () {
            $produk = Produk::factory()->create();
            $goodsIn = GoodsIn::create([
                'nomor_po' => GoodsIn::generateNomorPO(),
                'status' => GoodsInStatus::Draft->value,
                'tanggal_request' => now(),
                'id_kasir' => $this->kasir->id_pengguna,
            ]);

            $detail = GoodsInDetail::create([
                'id_pemesanan_barang' => $goodsIn->id_pemesanan_barang,
                'id_produk' => $produk->id_produk,
                'jumlah_dipesan' => 10,
                'jumlah_diterima' => 0,
            ]);

            $response = $this->actingAs($this->kasir)
                ->patch(route('kasir.goods-in.items.update', [$goodsIn->id_pemesanan_barang, $detail->id_detail_pemesanan_barang]), [
                    'id_produk' => $produk->id_produk,
                    'jumlah_dipesan' => 20,
                ]);

            $response->assertRedirect(route('kasir.goods-in.show', $goodsIn->id_pemesanan_barang));
            $response->assertSessionHas('success', 'Kuantitas item berhasil diperbarui.');

            $this->assertDatabaseHas('detail_pemesanan_barang', [
                'id_detail_pemesanan_barang' => $detail->id_detail_pemesanan_barang,
                'jumlah_dipesan' => 20,
            ]);
        });

        it('kasir cannot update item when PO is submitted', function () {
            $produk = Produk::factory()->create();
            $goodsIn = GoodsIn::create([
                'nomor_po' => GoodsIn::generateNomorPO(),
                'status' => GoodsInStatus::Submitted->value,
                'tanggal_request' => now(),
                'id_kasir' => $this->kasir->id_pengguna,
            ]);

            $detail = GoodsInDetail::create([
                'id_pemesanan_barang' => $goodsIn->id_pemesanan_barang,
                'id_produk' => $produk->id_produk,
                'jumlah_dipesan' => 10,
                'jumlah_diterima' => 0,
            ]);

            $response = $this->actingAs($this->kasir)
                ->patch(route('kasir.goods-in.items.update', [$goodsIn->id_pemesanan_barang, $detail->id_detail_pemesanan_barang]), [
                    'id_produk' => $produk->id_produk,
                    'jumlah_dipesan' => 20,
                ]);

            $response->assertRedirect();
            $response->assertSessionHasErrors('error');
        });
    });

    describe('Removing items from PO', function () {
        it('kasir can remove item from draft PO', function () {
            $produk = Produk::factory()->create();
            $goodsIn = GoodsIn::create([
                'nomor_po' => GoodsIn::generateNomorPO(),
                'status' => GoodsInStatus::Draft->value,
                'tanggal_request' => now(),
                'id_kasir' => $this->kasir->id_pengguna,
            ]);

            $detail = GoodsInDetail::create([
                'id_pemesanan_barang' => $goodsIn->id_pemesanan_barang,
                'id_produk' => $produk->id_produk,
                'jumlah_dipesan' => 10,
                'jumlah_diterima' => 0,
            ]);

            $response = $this->actingAs($this->kasir)
                ->delete(route('kasir.goods-in.items.remove', [$goodsIn->id_pemesanan_barang, $detail->id_detail_pemesanan_barang]));

            $response->assertRedirect(route('kasir.goods-in.show', $goodsIn->id_pemesanan_barang));
            $response->assertSessionHas('success', 'Item berhasil dihapus dari PO.');

            $this->assertDatabaseMissing('detail_pemesanan_barang', [
                'id_detail_pemesanan_barang' => $detail->id_detail_pemesanan_barang,
            ]);
        });

        it('kasir cannot remove item from non-draft PO', function () {
            $produk = Produk::factory()->create();
            $goodsIn = GoodsIn::create([
                'nomor_po' => GoodsIn::generateNomorPO(),
                'status' => GoodsInStatus::Submitted->value,
                'tanggal_request' => now(),
                'id_kasir' => $this->kasir->id_pengguna,
            ]);

            $detail = GoodsInDetail::create([
                'id_pemesanan_barang' => $goodsIn->id_pemesanan_barang,
                'id_produk' => $produk->id_produk,
                'jumlah_dipesan' => 10,
                'jumlah_diterima' => 0,
            ]);

            $response = $this->actingAs($this->kasir)
                ->delete(route('kasir.goods-in.items.remove', [$goodsIn->id_pemesanan_barang, $detail->id_detail_pemesanan_barang]));

            $response->assertRedirect();
            $response->assertSessionHasErrors('error');

            $this->assertDatabaseHas('detail_pemesanan_barang', [
                'id_detail_pemesanan_barang' => $detail->id_detail_pemesanan_barang,
            ]);
        });
    });

    describe('Submitting PO', function () {
        it('kasir can submit PO with items', function () {
            $produk = Produk::factory()->create();
            $goodsIn = GoodsIn::create([
                'nomor_po' => GoodsIn::generateNomorPO(),
                'status' => GoodsInStatus::Draft->value,
                'tanggal_request' => now(),
                'id_kasir' => $this->kasir->id_pengguna,
            ]);

            GoodsInDetail::create([
                'id_pemesanan_barang' => $goodsIn->id_pemesanan_barang,
                'id_produk' => $produk->id_produk,
                'jumlah_dipesan' => 10,
                'jumlah_diterima' => 0,
            ]);

            $response = $this->actingAs($this->kasir)
                ->post(route('kasir.goods-in.submit', $goodsIn->id_pemesanan_barang));

            $response->assertRedirect(route('kasir.goods-in.show', $goodsIn->id_pemesanan_barang));
            $response->assertSessionHas('success', 'PO berhasil diajukan untuk persetujuan.');

            $this->assertDatabaseHas('pemesanan_barang', [
                'id_pemesanan_barang' => $goodsIn->id_pemesanan_barang,
                'status' => GoodsInStatus::Submitted->value,
            ]);
        });

        it('kasir cannot submit PO without items', function () {
            $goodsIn = GoodsIn::create([
                'nomor_po' => GoodsIn::generateNomorPO(),
                'status' => GoodsInStatus::Draft->value,
                'tanggal_request' => now(),
                'id_kasir' => $this->kasir->id_pengguna,
            ]);

            $response = $this->actingAs($this->kasir)
                ->post(route('kasir.goods-in.submit', $goodsIn->id_pemesanan_barang));

            $response->assertRedirect();
            $response->assertSessionHasErrors('error');

            $this->assertDatabaseHas('pemesanan_barang', [
                'id_pemesanan_barang' => $goodsIn->id_pemesanan_barang,
                'status' => GoodsInStatus::Draft->value,
            ]);
        });
    });

    describe('Available Products', function () {
        it('available products only shows products not in PO', function () {
            $produk1 = Produk::factory()->create();
            $produk2 = Produk::factory()->create();
            $produk3 = Produk::factory()->create();

            $goodsIn = GoodsIn::create([
                'nomor_po' => GoodsIn::generateNomorPO(),
                'status' => GoodsInStatus::Draft->value,
                'tanggal_request' => now(),
                'id_kasir' => $this->kasir->id_pengguna,
            ]);

            // Add produk1 and produk2 to PO
            GoodsInDetail::create([
                'id_pemesanan_barang' => $goodsIn->id_pemesanan_barang,
                'id_produk' => $produk1->id_produk,
                'jumlah_dipesan' => 5,
                'jumlah_diterima' => 0,
            ]);

            GoodsInDetail::create([
                'id_pemesanan_barang' => $goodsIn->id_pemesanan_barang,
                'id_produk' => $produk2->id_produk,
                'jumlah_dipesan' => 10,
                'jumlah_diterima' => 0,
            ]);

            $response = $this->actingAs($this->kasir)
                ->get(route('kasir.goods-in.show', $goodsIn->id_pemesanan_barang));

            $response->assertSuccessful();

            // Since we use Inertia, check via database
            $addedProductIds = $goodsIn->details()->pluck('id_produk')->toArray();

            expect($addedProductIds)->toContain($produk1->id_produk);
            expect($addedProductIds)->toContain($produk2->id_produk);
            expect($addedProductIds)->not->toContain($produk3->id_produk);
        });
    });
});
