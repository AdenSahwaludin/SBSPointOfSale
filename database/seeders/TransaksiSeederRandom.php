<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransaksiSeederRandom extends Seeder
{
    public function run(): void
    {
        // Get customers - only P002 and P003 can use credit
        // Hardcode known customer IDs
        $allPelangganIds = ['P001', 'P002', 'P003'];
        $creditPelangganIds = ['P002', 'P003']; // Only these can use credit

        // Get all products with their real data
        $produk = DB::table('produk')
            ->select('id_produk', 'nama', 'harga', 'satuan', 'isi_per_pack')
            ->get();

        $produkMap = $produk->keyBy('id_produk');
        $produkPcs = $produk->where('satuan', 'pcs')->values();
        $produkPack = $produk->where('satuan', 'pack')->values();

        $ambilProdukUnik = function ($koleksi, array &$terpakai) use ($produkMap) {
            $tersedia = $koleksi->pluck('id_produk')->diff($terpakai)->values();

            if ($tersedia->isEmpty()) {
                $terpakai = [];
                $tersedia = $koleksi->pluck('id_produk')->values();
            }

            $produkId = $tersedia->random();
            $terpakai[] = $produkId;

            return $produkMap->get($produkId);
        };

        // Get cashier
        $kasir = User::where('role', 'kasir')->first();
        $id_kasir = $kasir ? $kasir->id_pengguna : '001-ADM';

        // Payment methods and transaction types (must match enum values)
        $metodePaymentList = ['TUNAI', 'KREDIT', 'QRIS', 'TRANSFER BCA'];
        $tenorOptions = [3, 6]; // 3 or 6 months for credit

        // Get current date
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $daysInMonth = $now->daysInMonth;
        $lastSevenDaysStart = max(1, $daysInMonth - 6); // Last 7 calendar days in the month
        $earlyMonthEnd = max(1, $lastSevenDaysStart - 1);

        // Total 600 transactions for better distribution (approx 20 per day)
        $transactionCount = 600;
        $lastSevenDaysCount = 200;

        $transactionNum = 400;
        $creditCount = 0;
        $maxCredit = 15; // Increased credit limit for more data

        // Create transactions
        for ($i = 0; $i < $transactionCount; $i++) {
            // Determine distribution: last 7 days or other days
            if ($i < $lastSevenDaysCount) {
                $day = rand($lastSevenDaysStart, $daysInMonth);
            } else {
                $day = rand(1, $earlyMonthEnd);
            }

            $tanggal = Carbon::create($currentYear, $currentMonth, $day, rand(8, 17), rand(0, 59), 0);

            // Select payment method
            $metodePayment = $metodePaymentList[array_rand($metodePaymentList)];

            // Credit logic: only for transactions that have a wholesale item or just randomly for others
            if ($metodePayment === 'KREDIT' && ($creditCount >= $maxCredit)) {
                $nonCreditMethods = ['TUNAI', 'QRIS', 'TRANSFER BCA'];
                $metodePayment = $nonCreditMethods[array_rand($nonCreditMethods)];
            }

            if ($metodePayment === 'KREDIT') {
                $pelangganId = $creditPelangganIds[array_rand($creditPelangganIds)];
                $creditCount++;
            } else {
                $pelangganId = $allPelangganIds[array_rand($allPelangganIds)];
            }

            $nomorTransaksi = 'INV-'.$currentYear.'-'.str_pad($currentMonth, 2, '0', STR_PAD_LEFT).'-'.str_pad($transactionNum, 3, '0', STR_PAD_LEFT).'-'.$pelangganId;

            $items = [];
            $subtotal = 0;
            $terpakaiProdukIds = [];

            $targetSubtotal = rand(400000, 800000);
            if (rand(1, 100) <= 25) {
                $targetSubtotal = rand(800000, 1000000);
            }

            $pakaiPack = rand(1, 100) <= 30 && $produkPack->isNotEmpty();

            if ($pakaiPack) {
                $produk_data = $ambilProdukUnik($produkPack, $terpakaiProdukIds);
                $hargaSatuan = (int) $produk_data->harga;

                if ($hargaSatuan <= ($targetSubtotal * 0.85)) {
                    $itemSubtotal = $hargaSatuan;
                    $subtotal += $itemSubtotal;

                    $items[] = [
                        'harga_satuan' => $hargaSatuan,
                        'kuantitas' => 1,
                        'subtotal' => $itemSubtotal,
                        'produk_id' => $produk_data->id_produk,
                        'nama_produk' => $produk_data->nama,
                        'jenis_satuan' => 'pack',
                        'isi_per_pack' => (int) ($produk_data->isi_per_pack ?? 1),
                    ];
                }
            }

            $maksItem = $pakaiPack ? rand(2, 3) : rand(2, 4);

            while ($subtotal < $targetSubtotal && count($items) < $maksItem) {
                $produk_data = $ambilProdukUnik($produkPcs, $terpakaiProdukIds);
                $hargaSatuan = (int) $produk_data->harga;
                $remaining = max(1, $targetSubtotal - $subtotal);
                $kuantitas = rand(8, 18);
                $kuantitas = max(1, min($kuantitas, (int) max(1, floor($remaining / $hargaSatuan))));
                $itemSubtotal = $hargaSatuan * $kuantitas;

                $subtotal += $itemSubtotal;

                $items[] = [
                    'harga_satuan' => $hargaSatuan,
                    'kuantitas' => $kuantitas,
                    'subtotal' => $itemSubtotal,
                    'produk_id' => $produk_data->id_produk,
                    'nama_produk' => $produk_data->nama,
                    'jenis_satuan' => 'unit',
                    'isi_per_pack' => (int) ($produk_data->isi_per_pack ?? 1),
                ];

                if ($subtotal >= ($targetSubtotal * 0.9) && count($items) >= 2) {
                    break;
                }
            }

            if ($subtotal < 250000 && $produkPcs->isNotEmpty()) {
                $produk_data = $ambilProdukUnik($produkPcs, $terpakaiProdukIds);
                $hargaSatuan = (int) $produk_data->harga;
                $kuantitas = 10;
                $itemSubtotal = $hargaSatuan * $kuantitas;
                $subtotal += $itemSubtotal;

                $items[] = [
                    'harga_satuan' => $hargaSatuan,
                    'kuantitas' => $kuantitas,
                    'subtotal' => $itemSubtotal,
                    'produk_id' => $produk_data->id_produk,
                    'nama_produk' => $produk_data->nama,
                    'jenis_satuan' => 'unit',
                    'isi_per_pack' => (int) ($produk_data->isi_per_pack ?? 1),
                ];
            }

            $jumlahItemReal = count($items);

            $pajak = (int) (floor(($subtotal * 0.10) / 1000) * 1000); // 10% tax, rounded to nearest 1000
            $total = $subtotal + $pajak;

            // Determine transaction type and credit specifics
            $statusPembayaran = 'LUNAS'; // Default to LUNAS for cash payments
            $jenisTranasksi = 'TUNAI'; // Default to TUNAI
            $dp = 0;
            $tenorBulan = null;
            $bungaPersen = 0;
            $cicilanBulanan = null;
            $idKontrak = null;
            $arStatus = 'NA';
            $paidAt = $tanggal;

            // Calculate credit details if payment method is KREDIT
            $nomorKontrak = null;
            $kontrakData = null;

            if ($metodePayment === 'KREDIT') {
                $tenorBulan = $tenorOptions[array_rand($tenorOptions)];
                $dp = (int) (floor(($total * 0.2) / 1000) * 1000); // Down payment 20%, rounded to 1000
                $bungaPersen = 2; // 2% monthly interest
                $pokok = $total - $dp;
                $bunga = (int) (floor(($pokok * $bungaPersen / 100) / 1000) * 1000); // Interest, rounded to 1000
                $cicilanBulanan = (int) (floor((($pokok + $bunga) / $tenorBulan) / 1000) * 1000); // Monthly payment, rounded to 1000

                $statusPembayaran = 'MENUNGGU'; // For credit, payment is pending
                $arStatus = 'AKTIF'; // AR status is AKTIF for active credit
                $jenisTranasksi = 'KREDIT';
                $paidAt = null;

                $nomorKontrak = 'KRD-'.$currentYear.str_pad($currentMonth, 2, '0', STR_PAD_LEFT).'-'.str_pad($transactionNum, 4, '0', STR_PAD_LEFT);
                $kontrakData = [
                    'nomor_kontrak' => $nomorKontrak,
                    'id_pelanggan' => $pelangganId,
                    'nomor_transaksi' => $nomorTransaksi,
                    'mulai_kontrak' => $tanggal,
                    'tenor_bulan' => $tenorBulan,
                    'pokok_pinjaman' => $pokok,
                    'dp' => $dp,
                    'bunga_persen' => $bungaPersen,
                    'cicilan_bulanan' => $cicilanBulanan,
                    'status' => 'AKTIF',
                    'score_snapshot' => rand(50, 100),
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ];
            }

            // Insert transaksi FIRST
            DB::table('transaksi')->insert([
                'nomor_transaksi' => $nomorTransaksi,
                'id_pelanggan' => $pelangganId,
                'id_kasir' => $id_kasir,
                'tanggal' => $tanggal,
                'total_item' => $jumlahItemReal,
                'subtotal' => $subtotal,
                'diskon' => 0,
                'pajak' => $pajak,
                'biaya_pengiriman' => 0,
                'total' => $total,
                'metode_bayar' => $metodePayment,
                'status_pembayaran' => $statusPembayaran,
                'paid_at' => $paidAt,
                'jenis_transaksi' => $jenisTranasksi,
                'dp' => $dp,
                'tenor_bulan' => $tenorBulan,
                'bunga_persen' => $bungaPersen,
                'cicilan_bulanan' => $cicilanBulanan,
                'ar_status' => $arStatus,
                'id_kontrak' => null,  // Will be updated later if credit
                'created_at' => $tanggal,
                'updated_at' => $tanggal,
            ]);

            // Insert detail transaksi
            foreach ($items as $item) {
                DB::table('transaksi_detail')->insert([
                    'nomor_transaksi' => $nomorTransaksi,
                    'id_produk' => $item['produk_id'],
                    'nama_produk' => $item['nama_produk'],
                    'jumlah' => $item['kuantitas'],
                    'jenis_satuan' => $item['jenis_satuan'],
                    'harga_satuan' => $item['harga_satuan'],
                    'isi_pack_saat_transaksi' => $item['isi_per_pack'],
                    'diskon_item' => 0,
                    'subtotal' => $item['subtotal'],
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ]);
            }

            // Create kontrak_kredit and jadwal_angsuran AFTER transaksi is inserted
            if ($metodePayment === 'KREDIT' && $kontrakData !== null) {
                $idKontrak = DB::table('kontrak_kredit')->insertGetId($kontrakData);

                // Update transaksi with id_kontrak
                DB::table('transaksi')
                    ->where('nomor_transaksi', $nomorTransaksi)
                    ->update(['id_kontrak' => $idKontrak]);

                // Create jadwal_angsuran (installment schedule)
                for ($period = 1; $period <= $tenorBulan; $period++) {
                    $jatuhTempo = $tanggal->copy()->addMonths($period);
                    $statusAngsuran = 'DUE'; // Due (not paid)

                    DB::table('jadwal_angsuran')->insert([
                        'id_kontrak' => $idKontrak,
                        'periode_ke' => $period,
                        'jatuh_tempo' => $jatuhTempo,
                        'jumlah_tagihan' => $cicilanBulanan,
                        'jumlah_dibayar' => 0,
                        'status' => $statusAngsuran,
                        'paid_at' => null,
                        'created_at' => $tanggal,
                        'updated_at' => $tanggal,
                    ]);
                }
            }

            $transactionNum++;
        }
    }
}
