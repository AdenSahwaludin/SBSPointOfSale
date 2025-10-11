<?php

namespace Database\Seeders;

use App\Models\JadwalAngsuran;
use App\Models\KontrakKredit;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class JadwalAngsuranSeeder extends Seeder
{
 /**
  * Run the database seeds.
  */
 public function run(): void
 {
  // Get the first credit contract
  $kontrak = KontrakKredit::where('nomor_kontrak', 'KRD-202510-0001')->first();

  if ($kontrak) {
   // Create installment schedules based on contract tenor
   for ($i = 1; $i <= $kontrak->tenor_bulan; $i++) {
    $jatuhTempo = Carbon::parse($kontrak->mulai_kontrak)->addMonths($i);

    $status = 'DUE';
    $jumlahDibayar = 0;

    // Mark first installment as paid for demo
    if ($i <= 1) {
     $status = 'PAID';
     $jumlahDibayar = $kontrak->cicilan_bulanan;
    }

    JadwalAngsuran::create([
     'id_kontrak' => $kontrak->id_kontrak,
     'periode_ke' => $i,
     'jatuh_tempo' => $jatuhTempo->format('Y-m-d'),
     'jumlah_tagihan' => $kontrak->cicilan_bulanan,
     'jumlah_dibayar' => $jumlahDibayar,
     'status' => $status,
     'paid_at' => $status === 'PAID' ? $jatuhTempo->format('Y-m-d H:i:s') : null,
    ]);
   }
  }
 }
}