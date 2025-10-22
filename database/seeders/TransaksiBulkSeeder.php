<?php

namespace Database\Seeders;

use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\Produk;
use App\Models\Pembayaran;
use App\Models\Pelanggan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TransaksiBulkSeeder extends Seeder
{
    public function run(): void
    {
        $produkList = Produk::take(6)->get();
        if ($produkList->count() === 0) {
            return;
        }

        $kasirId = '001-ADEN';
        $customers = Pelanggan::where('id_pelanggan', '!=', 'P001')->pluck('id_pelanggan')->all();
        if (empty($customers)) {
            $customers = ['P002', 'P003'];
        }

        $now = Carbon::now();

        for ($i = 1; $i <= 50; $i++) {
            $idPelanggan = $customers[array_rand($customers)];
            $tanggal = $now->copy()->subDays(random_int(0, 60))->setTime(random_int(9, 20), random_int(0, 59));
            $metode = (random_int(0, 100) < 35) ? 'KREDIT' : (random_int(0, 1) ? 'TUNAI' : 'TRANSFER BCA');

            $itemsCount = random_int(1, 4);
            $picked = $produkList->random($itemsCount);

            $subtotal = 0;
            $totalItem = 0;
            $details = [];
            foreach ($picked as $produk) {
                $qty = random_int(1, 5);
                $harga = (int)$produk->harga;
                $lineTotal = $harga * $qty;
                $subtotal += $lineTotal;
                $totalItem += $qty;
                $details[] = [
                    'id_produk' => $produk->id_produk,
                    'nama_produk' => $produk->nama,
                    'harga_satuan' => $harga,
                    'jumlah' => $qty,
                    'mode_qty' => 'unit',
                    'diskon_item' => 0,
                    'subtotal' => $lineTotal,
                    'isi_pack_saat_transaksi' => max(1, (int)$produk->isi_per_pack),
                    // Note: satuan_saat_transaksi column doesn't exist in table, removed
                ];
            }

            $diskon = 0;
            $pajak = 0;
            $total = $subtotal - $diskon + $pajak;

            $sequence = sprintf('%03d', $i + 2); // avoid clashing with existing seeders 001..002
            $nomorTransaksi = sprintf('INV-%s-%s-%s-%s', $tanggal->format('Y'), $tanggal->format('m'), $sequence, $idPelanggan);

            $isKredit = $metode === 'KREDIT';
            $dp = $isKredit ? (int)round($total * [0, 0.1, 0.2, 0.3][random_int(0, 3)]) : 0;
            $tenor = $isKredit ? [3, 6, 9, 12][random_int(0, 3)] : null;
            $cicilan = $isKredit ? (int)ceil(($total - $dp) / max(1, $tenor)) : null;

            $trx = Transaksi::create([
                'nomor_transaksi' => $nomorTransaksi,
                'id_pelanggan' => $idPelanggan,
                'id_kasir' => $kasirId,
                'tanggal' => $tanggal,
                'total_item' => $totalItem,
                'subtotal' => $subtotal,
                'diskon' => $diskon,
                'pajak' => $pajak,
                'biaya_pengiriman' => 0,
                'total' => $total,
                'metode_bayar' => $metode,
                'status_pembayaran' => $isKredit ? 'MENUNGGU' : 'LUNAS',
                'paid_at' => $isKredit ? null : $tanggal,
                'jenis_transaksi' => $isKredit ? 'KREDIT' : 'TUNAI',
                'dp' => $dp,
                'tenor_bulan' => $tenor,
                'bunga_persen' => $isKredit ? 2 : 0,
                'cicilan_bulanan' => $cicilan,
                'ar_status' => $isKredit ? 'AKTIF' : 'NA',
                'id_kontrak' => null,
            ]);

            foreach ($details as $d) {
                TransaksiDetail::create(array_merge($d, [
                    'nomor_transaksi' => $trx->nomor_transaksi,
                ]));
            }

            if ($isKredit) {
                // DP payment
                if ($dp > 0) {
                    Pembayaran::create([
                        'id_pembayaran' => 'PAY-' . $tanggal->format('Ymd') . '-' . str_pad((string)random_int(1, 9999999), 7, '0', STR_PAD_LEFT),
                        'id_transaksi' => $trx->nomor_transaksi,
                        'id_angsuran' => null,
                        'metode' => 'TRANSFER BCA',
                        'jumlah' => $dp,
                        'tanggal' => $tanggal,
                        'keterangan' => 'DP awal kredit',
                    ]);
                }
                // Update customer's saldo_kredit as outstanding
                Pelanggan::where('id_pelanggan', $idPelanggan)->increment('saldo_kredit', max(0, $total - $dp));
            } else {
                // Full payment record for non-credit
                Pembayaran::create([
                    'id_pembayaran' => 'PAY-' . $tanggal->format('Ymd') . '-' . str_pad((string)random_int(1, 9999999), 7, '0', STR_PAD_LEFT),
                    'id_transaksi' => $trx->nomor_transaksi,
                    'id_angsuran' => null,
                    'metode' => $metode === 'TUNAI' ? 'TUNAI' : 'TRANSFER BCA',
                    'jumlah' => $total,
                    'tanggal' => $tanggal,
                    'keterangan' => 'Pelunasan',
                ]);
            }
        }
    }
}

