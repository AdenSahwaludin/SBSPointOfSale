<?php

namespace Database\Seeders;

use App\Models\KontrakKredit;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class KontrakKreditSeeder extends Seeder
{
 /**
  * Run the database seeds.
  */
 public function run(): void
 {
  // Sample credit contract for customer P003
  $kontrak = KontrakKredit::create([
   'nomor_kontrak' => 'KRD-202510-0001',
   'id_pelanggan' => 'P003',
   'nomor_transaksi' => 'INV-2025-10-001-P003',
   'mulai_kontrak' => Carbon::now()->format('Y-m-d'),
   'tenor_bulan' => 12,
   'pokok_pinjaman' => 2000000,
   'dp' => 500000,
   'bunga_persen' => 2.5,
   'cicilan_bulanan' => 135000,
   'status' => 'AKTIF',
   'score_snapshot' => 80,
   'alasan_eligibilitas' => 'Trust score tinggi, riwayat pembayaran baik',
  ]);

  // Update the transaction with contract ID
  Transaksi::where('nomor_transaksi', 'INV-2025-10-001-P003')
   ->update(['id_kontrak' => $kontrak->id_kontrak]);
 }
}