<?php

use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('Produk Deletion', function () {
    beforeEach(function () {
        $this->admin = User::factory()->admin()->create();
    });

    it('allows deletion of produk with no relations', function () {
        $produk = Produk::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.produk.destroy', $produk->id_produk));

        $response->assertRedirect(route('admin.produk.index'));
        $response->assertSessionHas('message', 'Produk berhasil dihapus.');
        expect(Produk::find($produk->id_produk))->toBeNull();
    });

    it('prevents deletion of produk with goods in details', function () {
        $produk = Produk::factory()->create();

        // Create a GoodsIn record
        $goodsInId = DB::table('pemesanan_barang')->insertGetId([
            'id_kasir' => $this->admin->id_pengguna,
            'nomor_po' => 'PO-2026-01-00001',
            'status' => 'draft',
            'tanggal_request' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create GoodsInDetail
        DB::table('detail_pemesanan_barang')->insert([
            'id_pemesanan_barang' => $goodsInId,
            'id_produk' => $produk->id_produk,
            'jumlah_dipesan' => 10,
            'jumlah_diterima' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.produk.destroy', $produk->id_produk));

        $response->assertRedirect();
        expect(session('warning'))->toContain('Detail Pemesanan Barang');
        expect(session('warning'))->toContain('tidak dapat dihapus');

        // Verify produk still exists
        expect(Produk::find($produk->id_produk))->not->toBeNull();
    });

    it('prevents deletion of produk with transaksi details', function () {
        $produk = Produk::factory()->create();
        $pelanggan = Pelanggan::factory()->create();

        // Create a Transaksi record
        $nomor = 'TRX-2026-01-00001';
        DB::table('transaksi')->insert([
            'nomor_transaksi' => $nomor,
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'id_kasir' => $this->admin->id_pengguna,
            'tanggal' => now(),
            'total_item' => 1,
            'subtotal' => 100000,
            'diskon' => 0,
            'pajak' => 10000,
            'biaya_pengiriman' => 0,
            'total' => 110000,
            'metode_bayar' => 'TUNAI',
            'status_pembayaran' => 'LUNAS',
            'jenis_transaksi' => 'TUNAI',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create TransaksiDetail
        DB::table('transaksi_detail')->insert([
            'nomor_transaksi' => $nomor,
            'id_produk' => $produk->id_produk,
            'nama_produk' => $produk->nama,
            'jumlah' => 5,
            'harga_satuan' => 20000,
            'subtotal' => 100000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.produk.destroy', $produk->id_produk));

        $response->assertRedirect();
        expect(session('warning'))->toContain('Detail Transaksi');

        // Verify produk still exists
        expect(Produk::find($produk->id_produk))->not->toBeNull();
    });

    it('shows error message with multiple relations', function () {
        $produk = Produk::factory()->create();
        $pelanggan = Pelanggan::factory()->create();

        // Create a GoodsIn record
        $goodsInId = DB::table('pemesanan_barang')->insertGetId([
            'id_kasir' => $this->admin->id_pengguna,
            'nomor_po' => 'PO-2026-01-00001',
            'status' => 'draft',
            'tanggal_request' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create GoodsInDetail
        DB::table('detail_pemesanan_barang')->insert([
            'id_pemesanan_barang' => $goodsInId,
            'id_produk' => $produk->id_produk,
            'jumlah_dipesan' => 10,
            'jumlah_diterima' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a Transaksi record
        $nomor = 'TRX-2026-01-00001';
        DB::table('transaksi')->insert([
            'nomor_transaksi' => $nomor,
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'id_kasir' => $this->admin->id_pengguna,
            'tanggal' => now(),
            'total_item' => 1,
            'subtotal' => 100000,
            'diskon' => 0,
            'pajak' => 10000,
            'biaya_pengiriman' => 0,
            'total' => 110000,
            'metode_bayar' => 'TUNAI',
            'status_pembayaran' => 'LUNAS',
            'jenis_transaksi' => 'TUNAI',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create TransaksiDetail
        DB::table('transaksi_detail')->insert([
            'nomor_transaksi' => $nomor,
            'id_produk' => $produk->id_produk,
            'nama_produk' => $produk->nama,
            'jumlah' => 5,
            'harga_satuan' => 20000,
            'subtotal' => 100000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.produk.destroy', $produk->id_produk));

        $response->assertRedirect();
        expect(session('warning'))->toContain('Detail Pemesanan Barang');
        expect(session('warning'))->toContain('Detail Transaksi');
    });
});
