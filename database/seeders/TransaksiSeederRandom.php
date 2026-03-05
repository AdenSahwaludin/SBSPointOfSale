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
            ->select('id_produk', 'nama', 'harga')
            ->get();
        $produkIds = $produk->pluck('id_produk')->toArray();
        $produkMap = $produk->keyBy('id_produk');

        // Get cashier
        $kasir = User::where('role', 'kasir')->first();
        $id_kasir = $kasir ? $kasir->id_pengguna : '001-ADMI';

        // Payment methods and transaction types (must match enum values)
        $metodePaymentList = ['TUNAI', 'KREDIT', 'QRIS', 'TRANSFER BCA'];
        $tenorOptions = [3, 6]; // 3 or 6 months for credit

        // Get current date
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $currentDay = $now->day;
        $daysInMonth = $now->daysInMonth;
        $lastSevenDaysStart = max(1, $currentDay - 7); // Last 7 days

        // Total 150 transactions
        // 75 in last 7 days, 75 in day 1-23 (rest of the month)
        $transactionCount = 150;
        $lastSevenDaysCount = 75;
        $otherDaysCount = 75;

        $transactionNum = 400;
        $creditCount = 0;
        $maxCredit = 5; // Limit to 5 credit transactions only
        // Create transactions
        for ($i = 0; $i < $transactionCount; $i++) {
            // Determine distribution: last 7 days or other days
            if ($i < $lastSevenDaysCount) {
                // Last 7 days
                $day = rand($lastSevenDaysStart, $currentDay);
            } else {
                // Days 1 to 23
                $day = rand(1, min(23, $daysInMonth - 7));
            }

            $tanggal = Carbon::create($currentYear, $currentMonth, $day, rand(8, 17), rand(0, 59), 0);

            // Select payment method first, then determine customer eligibility
            // Limit KREDIT to only 5 total transactions
            $metodePayment = $metodePaymentList[array_rand($metodePaymentList)];
            if ($metodePayment === 'KREDIT' && $creditCount >= $maxCredit) {
                // If we've reached max credit, use a non-credit method instead
                $nonCreditMethods = ['TUNAI', 'QRIS', 'TRANSFER BCA'];
                $metodePayment = $nonCreditMethods[array_rand($nonCreditMethods)];
            }

            // If KREDIT method, only use credit-eligible customers
            if ($metodePayment === 'KREDIT') {
                $pelangganId = $creditPelangganIds[array_rand($creditPelangganIds)];
                $creditCount++;
            } else {
                // For cash/qris/transfer, use any customer
                $pelangganId = $allPelangganIds[array_rand($allPelangganIds)];
            }

            // Format: INV-YYYY-MM-NNN-PXXX
            $nomorTransaksi = 'INV-'.$currentYear.'-'.str_pad($currentMonth, 2, '0', STR_PAD_LEFT).'-'.str_pad($transactionNum, 3, '0', STR_PAD_LEFT).'-'.$pelangganId;

            // Random number of items (1-5)
            $jumlahItem = rand(1, 5);
            $subtotal = 0;
            $items = [];

            // Generate line items with real products
            for ($j = 0; $j < $jumlahItem; $j++) {
                $produkId = $produkIds[array_rand($produkIds)];
                $produk_data = $produkMap[$produkId];
                $hargaSatuan = (int) $produk_data->harga;
                $kuantitas = rand(1, 5);
                $itemSubtotal = $hargaSatuan * $kuantitas;
                $subtotal += $itemSubtotal;

                $items[] = [
                    'harga_satuan' => $hargaSatuan,
                    'kuantitas' => $kuantitas,
                    'subtotal' => $itemSubtotal,
                    'produk_id' => $produkId,
                    'nama_produk' => $produk_data->nama,
                ];
            }

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
                'total_item' => $jumlahItem,
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
                    'jenis_satuan' => 'unit',
                    'harga_satuan' => $item['harga_satuan'],
                    'isi_pack_saat_transaksi' => 1,
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
