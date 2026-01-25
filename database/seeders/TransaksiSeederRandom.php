<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;

class TransaksiSeederRandom extends Seeder
{
    public function run(): void
    {
        $pelangganIds = ['P001', 'P002']; // Sesuai PelangganSeeder
        $produkIds = [1, 2, 3, 4, 5]; // ID produk (1-20 ada di seeder)
        
        // Ambil kasir pertama dari database
        $kasir = User::where('role', 'kasir')->first();
        $id_kasir = $kasir ? $kasir->id_pengguna : '001-ADMI';

        for ($i = 400; $i <= 414; $i++) {
            // Distribute transactions: 70% on 25-26, 30% on other dates (1-24)
            $randomDate = rand(1, 100);
            if ($randomDate <= 70) {
                // 70% on 25-26
                $day = rand(0, 1) === 0 ? 25 : 26;
            } else {
                // 30% on dates 1-24
                $day = rand(1, 24);
            }
            $tanggal = Carbon::create(2026, 1, $day, rand(8, 17), rand(0, 59), 0);
            $pelangganId = $pelangganIds[array_rand($pelangganIds)];
            $nomorTransaksi = 'INV-2026-01-' . $i . '-' . $pelangganId;
            
            // Hitung subtotal dari items
            $jumlahItem = rand(2, 5);
            $subtotal = 0;
            $items = [];
            
            for ($j = 0; $j < $jumlahItem; $j++) {
                $hargaSatuan = rand(25000, 100000);
                $kuantitas = rand(2, 8);
                $itemSubtotal = $hargaSatuan * $kuantitas;
                $subtotal += $itemSubtotal;
                
                $items[] = [
                    'harga_satuan' => $hargaSatuan,
                    'kuantitas' => $kuantitas,
                    'subtotal' => $itemSubtotal,
                    'produk_id' => $produkIds[array_rand($produkIds)]
                ];
            }
            
            $pajak = (int)($subtotal * 0.10); // 10% pajak
            $total = $subtotal + $pajak;

            DB::table('transaksi')->insert([
                'nomor_transaksi' => $nomorTransaksi,
                'id_pelanggan' => $pelangganId,
                'id_kasir' => $id_kasir,
                'tanggal' => $tanggal,
                'total_item' => $jumlahItem,
                'subtotal' => $subtotal,
                'diskon' => 0,
                'pajak' => $pajak,
                'biaya_pengiriman' => 0,
                'total' => $total,
                'metode_bayar' => 'TUNAI',
                'status_pembayaran' => 'LUNAS',
                'paid_at' => $tanggal,
                'jenis_transaksi' => 'TUNAI',
                'dp' => 0,
                'tenor_bulan' => null,
                'bunga_persen' => 0,
                'cicilan_bulanan' => null,
                'ar_status' => 'NA',
                'id_kontrak' => null,
                'created_at' => $tanggal,
                'updated_at' => $tanggal,
            ]);

            // Insert detail transaksi
            foreach ($items as $item) {
                DB::table('transaksi_detail')->insert([
                    'nomor_transaksi' => $nomorTransaksi,
                    'id_produk' => $item['produk_id'],
                    'nama_produk' => 'Produk ' . $item['produk_id'],
                    'jumlah' => $item['kuantitas'],
                    'jenis_satuan' => 'unit',
                    'harga_satuan' => $item['harga_satuan'],
                    'isi_pack_saat_transaksi' => 1,
                    'diskon_item' => 0,
                    'subtotal' => $item['subtotal'],
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ]);
            }
        }
    }
}