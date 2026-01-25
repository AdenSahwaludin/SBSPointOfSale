<?php

namespace Database\Seeders;

use App\Models\Pembayaran;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get product IDs
        $produk1 = Produk::where('sku', 'DA-GDP-60ML')->first();
        $produk2 = Produk::where('sku', 'DA-ALK-60ML')->first();

        if (! $produk1 || ! $produk2) {
            return; // Skip if products don't exist
        }

        // Sample cash transaction
        $transaksiTunai = Transaksi::create([
            'nomor_transaksi' => 'INV-2026-01-001-P002',
            'id_pelanggan' => 'P002',
            'id_kasir' => '001-ADEN',
            'tanggal' => Carbon::create(2026, 1, 25, 10, 0, 0),
            'total_item' => 2,
            'subtotal' => 32000,
            'diskon' => 0,
            'pajak' => 0,
            'biaya_pengiriman' => 0,
            'total' => 32000,
            'metode_bayar' => 'TUNAI',
            'status_pembayaran' => 'LUNAS',
            'paid_at' => Carbon::create(2026, 1, 25, 10, 0, 0),
            'jenis_transaksi' => 'TUNAI',
            'dp' => 0,
            'tenor_bulan' => null,
            'bunga_persen' => 0,
            'cicilan_bulanan' => null,
            'ar_status' => 'NA',
            'id_kontrak' => null,
        ]);

        // Transaction details for cash transaction
        TransaksiDetail::create([
            'nomor_transaksi' => $transaksiTunai->nomor_transaksi,
            'id_produk' => $produk1->id_produk,
            'nama_produk' => $produk1->nama,
            'harga_satuan' => $produk1->harga,
            'jumlah' => 2,
            'jenis_satuan' => 'unit',
            'diskon_item' => 0,
            'subtotal' => 32000,
        ]);

        // Payment for cash transaction
        Pembayaran::create([
            'id_pembayaran' => 'PAY-20260125-0000001',
            'id_transaksi' => $transaksiTunai->nomor_transaksi,
            'id_angsuran' => null,
            'metode' => 'TUNAI',
            'jumlah' => 32000,
            'tanggal' => Carbon::create(2026, 1, 25, 10, 0, 0),
            'keterangan' => 'Pembayaran tunai',
        ]);

        // Sample credit transaction
        $transaksiKredit = Transaksi::create([
            'nomor_transaksi' => 'INV-2026-01-002-P003',
            'id_pelanggan' => 'P003',
            'id_kasir' => '001-ADEN',
            'tanggal' => Carbon::create(2026, 1, 26, 14, 0, 0),
            'total_item' => 10,
            'subtotal' => 140000,
            'diskon' => 0,
            'pajak' => 0,
            'biaya_pengiriman' => 0,
            'total' => 140000,
            'metode_bayar' => 'KREDIT',
            'status_pembayaran' => 'MENUNGGU',
            'paid_at' => null,
            'jenis_transaksi' => 'KREDIT',
            'dp' => 50000,
            'tenor_bulan' => 6,
            'bunga_persen' => 2,
            'cicilan_bulanan' => 16000,
            'ar_status' => 'AKTIF',
            'id_kontrak' => null, // Will be updated in KontrakKreditSeeder
        ]);

        // Transaction details for credit transaction
        TransaksiDetail::create([
            'nomor_transaksi' => $transaksiKredit->nomor_transaksi,
            'id_produk' => $produk2->id_produk,
            'nama_produk' => $produk2->nama,
            'harga_satuan' => $produk2->harga,
            'jumlah' => 10,
            'jenis_satuan' => 'unit',
            'diskon_item' => 0,
            'subtotal' => 140000,
        ]);

        // DP Payment for credit transaction
        Pembayaran::create([
            'id_pembayaran' => 'PAY-20260126-0000002',
            'id_transaksi' => $transaksiKredit->nomor_transaksi,
            'id_angsuran' => null,
            'metode' => 'TRANSFER BCA',
            'jumlah' => 50000,
            'tanggal' => Carbon::create(2026, 1, 26, 14, 0, 0),
            'keterangan' => 'Pembayaran DP cicilan via Transfer BCA',
        ]);
    }
}
