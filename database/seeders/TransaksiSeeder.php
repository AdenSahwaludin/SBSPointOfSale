<?php

namespace Database\Seeders;

use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\Pembayaran;
use App\Models\Produk;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransaksiSeeder extends Seeder
{
 /**
  * Run the database seeds.
  */
 public function run(): void
 {
  // Get product IDs
  $produk1 = Produk::where('sku', 'HB-MKP-60ML')->first();
  $produk2 = Produk::where('sku', 'EL-EUC-30ML')->first();

  if (!$produk1 || !$produk2) {
   return; // Skip if products don't exist
  }

  // Sample cash transaction
  $transaksiTunai = Transaksi::create([
   'nomor_transaksi' => 'INV-2025-10-001-P002',
   'id_pelanggan' => 'P002',
   'id_kasir' => '001-ADEN',
   'tanggal' => Carbon::now(),
   'total_item' => 2,
   'subtotal' => 30000,
   'diskon' => 0,
   'pajak' => 0,
   'biaya_pengiriman' => 0,
   'total' => 30000,
   'metode_bayar' => 'TUNAI',
   'status_pembayaran' => 'LUNAS',
   'paid_at' => Carbon::now(),
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
   'mode_qty' => 'unit',
   'diskon_item' => 0,
   'subtotal' => 30000,
  ]);

  // Payment for cash transaction
  Pembayaran::create([
   'id_pembayaran' => 'PAY-' . Carbon::now()->format('Ymd') . '-0000001',
   'id_transaksi' => $transaksiTunai->nomor_transaksi,
   'id_angsuran' => null,
   'metode' => 'TUNAI',
   'jumlah' => 30000,
   'tanggal' => Carbon::now(),
   'keterangan' => 'Pembayaran tunai',
  ]);

  // Sample credit transaction
  $transaksiKredit = Transaksi::create([
   'nomor_transaksi' => 'INV-2025-10-002-P003',
   'id_pelanggan' => 'P003',
   'id_kasir' => '001-ADEN',
   'tanggal' => Carbon::now(),
   'total_item' => 10,
   'subtotal' => 180000,
   'diskon' => 0,
   'pajak' => 0,
   'biaya_pengiriman' => 0,
   'total' => 180000,
   'metode_bayar' => 'KREDIT',
   'status_pembayaran' => 'MENUNGGU',
   'paid_at' => null,
   'jenis_transaksi' => 'KREDIT',
   'dp' => 50000,
   'tenor_bulan' => 6,
   'bunga_persen' => 2,
   'cicilan_bulanan' => 22000,
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
   'mode_qty' => 'unit',
   'diskon_item' => 0,
   'subtotal' => 180000,
  ]);

  // DP Payment for credit transaction
  Pembayaran::create([
   'id_pembayaran' => 'PAY-' . Carbon::now()->format('Ymd') . '-0000002',
   'id_transaksi' => $transaksiKredit->nomor_transaksi,
   'id_angsuran' => null,
   'metode' => 'TRANSFER BCA',
   'jumlah' => 50000,
   'tanggal' => Carbon::now(),
   'keterangan' => 'Pembayaran DP cicilan via Transfer BCA',
  ]);
 }
}