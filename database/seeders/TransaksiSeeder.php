<?php

namespace Database\Seeders;

use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\Pembayaran;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample cash transaction
        $transaksiTunai = Transaksi::create([
            'nomor_transaksi' => 'INV-2025-10-001-P002',
            'id_pelanggan' => 'P002',
            'id_kasir' => '001-ADEN',
            'tanggal' => Carbon::now(),
            'total_item' => 2,
            'subtotal' => 28000,
            'diskon' => 0,
            'pajak' => 0,
            'biaya_pengiriman' => 0,
            'total' => 28000,
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

        // Sample credit transaction
        $transaksiKredit = Transaksi::create([
            'nomor_transaksi' => 'INV-2025-10-001-P003',
            'id_pelanggan' => 'P003',
            'id_kasir' => '001-ADEN',
            'tanggal' => Carbon::now(),
            'total_item' => 5,
            'subtotal' => 2500000,
            'diskon' => 0,
            'pajak' => 0,
            'biaya_pengiriman' => 0,
            'total' => 2500000,
            'metode_bayar' => 'KREDIT',
            'status_pembayaran' => 'LUNAS',
            'paid_at' => Carbon::now(),
            'jenis_transaksi' => 'KREDIT',
            'dp' => 500000,
            'tenor_bulan' => 12,
            'bunga_persen' => 2.5,
            'cicilan_bulanan' => 135000,
            'ar_status' => 'AKTIF',
            'id_kontrak' => null, // Will be updated in KontrakKreditSeeder
        ]);

        // Transaction details for cash transaction
        TransaksiDetail::create([
            'nomor_transaksi' => $transaksiTunai->nomor_transaksi,
            'id_produk' => '8997000610060',
            'nama_produk' => 'Minyak Akar Lawang Cap Daun 60 ml',
            'harga_satuan' => 14000,
            'jumlah' => 2,
            'mode_qty' => 'unit',
            'pack_size_snapshot' => 1,
            'diskon_item' => 0,
            'subtotal' => 28000,
        ]);

        // Payment for cash transaction
        Pembayaran::create([
            'id_pembayaran' => 'PAY-' . Carbon::now()->format('Ymd') . '-0000001',
            'id_transaksi' => $transaksiTunai->nomor_transaksi,
            'id_angsuran' => null,
            'metode' => 'TUNAI',
            'jumlah' => 28000,
            'tanggal' => Carbon::now(),
            'keterangan' => 'Pembayaran tunai',
        ]);

        // Transaction details for credit transaction
        TransaksiDetail::create([
            'nomor_transaksi' => $transaksiKredit->nomor_transaksi,
            'id_produk' => '8997000610060',
            'nama_produk' => 'Minyak Akar Lawang Cap Daun 60 ml',
            'harga_satuan' => 14000,
            'jumlah' => 179, // 179 x 14000 = 2,506,000 (approx 2.5M)
            'mode_qty' => 'unit',
            'pack_size_snapshot' => 1,
            'diskon_item' => 6000, // Small discount
            'subtotal' => 2500000,
        ]);

        // DP Payment for credit transaction
        Pembayaran::create([
            'id_pembayaran' => 'PAY-' . Carbon::now()->format('Ymd') . '-0000002',
            'id_transaksi' => $transaksiKredit->nomor_transaksi,
            'id_angsuran' => null,
            'metode' => 'MANUAL_TRANSFER',
            'jumlah' => 500000,
            'tanggal' => Carbon::now(),
            'keterangan' => 'Pembayaran DP cicilan via Transfer BCA',
        ]);
    }
}