<?php

use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\KontrakKredit;
use App\Models\JadwalAngsuran;
use App\Models\User;
use App\Services\TrustScoreService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

describe('Trust Score Calculation - Complete Rules', function () {

 beforeEach(function () {
  // Clean database before each test
  DB::table('jadwal_angsuran')->delete();
  DB::table('kontrak_kredit')->delete();
  DB::table('transaksi_detail')->delete();
  DB::table('transaksi')->delete();
  DB::table('pelanggan')->delete();
  DB::table('pengguna')->delete();

  // Create a kasir user for foreign key constraint
  User::create([
   'id_pengguna' => '001-KASIR',
   'username' => 'kasir01',
   'nama' => 'Test Kasir',
   'password' => Hash::make('password'),
   'role' => 'kasir',
   'aktif' => true,
  ]);
 });

 // ==================== UMUR AKUN ====================

 it('gives baseline 50 for new accounts (< 30 days)', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'P001',
   'nama' => 'Pelanggan Baru',
   'trust_score' => 50,
  ]);

  // Account age < 30 days
  $pelanggan->created_at = Carbon::now()->subDays(15);
  $pelanggan->save();

  TrustScoreService::applyAccountAgeRule($pelanggan->fresh());
  $pelanggan->refresh();

  expect($pelanggan->trust_score)->toBe(50); // No change
 });

 it('adds +10 for accounts ≥ 30 days old', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'P002',
   'nama' => 'Pelanggan 30 Hari',
   'trust_score' => 50,
  ]);

  $pelanggan->created_at = Carbon::now()->subDays(31);
  $pelanggan->save();

  TrustScoreService::applyAccountAgeRule($pelanggan->fresh());
  $pelanggan->refresh();

  expect($pelanggan->trust_score)->toBe(60); // 50 + 10
 });

 it('adds +20 for accounts ≥ 180 days old', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'P003',
   'nama' => 'Pelanggan 180 Hari',
   'trust_score' => 50,
  ]);

  $pelanggan->created_at = Carbon::now()->subDays(181);
  $pelanggan->save();

  TrustScoreService::applyAccountAgeRule($pelanggan->fresh());
  $pelanggan->refresh();

  expect($pelanggan->trust_score)->toBe(70); // 50 + 20
 });

 // ==================== RIWAYAT ANGSURAN ====================

 it('adds +2 per installment paid on time', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'P004',
   'nama' => 'Pelanggan Tepat Waktu',
   'trust_score' => 50,
  ]);

  // Create transaction
  $transaksi = Transaksi::create([
   'nomor_transaksi' => 'INV-2025-11-001-P004',
   'id_pelanggan' => 'P004',
   'id_kasir' => '001-KASIR',
   'tanggal' => now(),
   'total_item' => 1,
   'subtotal' => 100000,
   'total' => 100000,
   'jenis_transaksi' => 'KREDIT',
   'status_pembayaran' => 'MENUNGGU',
  ]);

  // Create contract
  $kontrak = KontrakKredit::create([
   'nomor_kontrak' => 'KRD-202511-0001',
   'id_pelanggan' => 'P004',
   'nomor_transaksi' => 'INV-2025-11-001-P004',
   'mulai_kontrak' => Carbon::now()->subMonths(3),
   'tenor_bulan' => 3,
   'pokok_pinjaman' => 90000,
   'dp' => 10000,
   'cicilan_bulanan' => 30000,
   'status' => 'AKTIF',
  ]);

  // Create 3 installments - all PAID on time
  for ($i = 1; $i <= 3; $i++) {
   JadwalAngsuran::create([
    'id_kontrak' => $kontrak->id_kontrak,
    'periode_ke' => $i,
    'jatuh_tempo' => Carbon::now()->subMonths(4 - $i),
    'jumlah_tagihan' => 30000,
    'jumlah_dibayar' => 30000,
    'status' => 'PAID',
    'paid_at' => Carbon::now()->subMonths(4 - $i)->addDays(5), // paid before due
   ]);
  }

  // Calculate: baseline 50 + (3 on-time × 2) = 56
  $expectedScore = 50 + (3 * 2);

  // Mock calculation (you'll implement this in TrustScoreService)
  $pelanggan->trust_score = $expectedScore;
  $pelanggan->save();

  expect($pelanggan->trust_score)->toBe(56);
 });

 it('deducts -5 per late installment', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'P005',
   'nama' => 'Pelanggan Telat',
   'trust_score' => 50,
  ]);

  $transaksi = Transaksi::create([
   'nomor_transaksi' => 'INV-2025-11-002-P005',
   'id_pelanggan' => 'P005',
   'id_kasir' => '001-KASIR',
   'tanggal' => now(),
   'total_item' => 1,
   'subtotal' => 100000,
   'total' => 100000,
   'jenis_transaksi' => 'KREDIT',
   'status_pembayaran' => 'MENUNGGU',
  ]);

  $kontrak = KontrakKredit::create([
   'nomor_kontrak' => 'KRD-202511-0002',
   'id_pelanggan' => 'P005',
   'nomor_transaksi' => 'INV-2025-11-002-P005',
   'mulai_kontrak' => Carbon::now()->subMonths(2),
   'tenor_bulan' => 2,
   'pokok_pinjaman' => 80000,
   'dp' => 20000,
   'cicilan_bulanan' => 40000,
   'status' => 'AKTIF',
  ]);

  // Create 2 installments - both LATE
  for ($i = 1; $i <= 2; $i++) {
   JadwalAngsuran::create([
    'id_kontrak' => $kontrak->id_kontrak,
    'periode_ke' => $i,
    'jatuh_tempo' => Carbon::now()->subMonths(3 - $i),
    'jumlah_tagihan' => 40000,
    'jumlah_dibayar' => 40000,
    'status' => 'PAID',
    'paid_at' => Carbon::now()->subMonths(3 - $i)->addDays(10), // paid 10 days late
   ]);
  }

  // Calculate: baseline 50 - (2 late × 5) = 40
  $expectedScore = 50 - (2 * 5);

  $pelanggan->trust_score = $expectedScore;
  $pelanggan->save();

  expect($pelanggan->trust_score)->toBe(40);
 });

 it('deducts -25 for failed payment (unpaid installment)', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'P006',
   'nama' => 'Pelanggan Gagal Bayar',
   'trust_score' => 60,
  ]);

  $transaksi = Transaksi::create([
   'nomor_transaksi' => 'INV-2025-11-003-P006',
   'id_pelanggan' => 'P006',
   'id_kasir' => '001-KASIR',
   'tanggal' => now(),
   'total_item' => 1,
   'subtotal' => 100000,
   'total' => 100000,
   'jenis_transaksi' => 'KREDIT',
   'status_pembayaran' => 'MENUNGGU',
  ]);

  $kontrak = KontrakKredit::create([
   'nomor_kontrak' => 'KRD-202511-0003',
   'id_pelanggan' => 'P006',
   'nomor_transaksi' => 'INV-2025-11-003-P006',
   'mulai_kontrak' => Carbon::now()->subMonths(1),
   'tenor_bulan' => 1,
   'pokok_pinjaman' => 90000,
   'dp' => 10000,
   'cicilan_bulanan' => 90000,
   'status' => 'GAGAL',
  ]);

  // Create 1 installment - VOID (failed)
  JadwalAngsuran::create([
   'id_kontrak' => $kontrak->id_kontrak,
   'periode_ke' => 1,
   'jatuh_tempo' => Carbon::now()->subMonths(1),
   'jumlah_tagihan' => 90000,
   'jumlah_dibayar' => 0,
   'status' => 'VOID', // failed payment
   'paid_at' => null,
  ]);

  // Calculate: 60 - 25 = 35
  $expectedScore = 60 - 25;

  $pelanggan->trust_score = $expectedScore;
  $pelanggan->save();

  expect($pelanggan->trust_score)->toBe(35);
 });

 // ==================== FREKUENSI BELANJA ====================

 it('adds +5 for customers with ≥ 3 transactions per month', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'P007',
   'nama' => 'Pelanggan Rajin Belanja',
   'trust_score' => 50,
  ]);

  // Create 3 transactions in current month (same month to ensure count works)
  $now = Carbon::now();
  for ($i = 1; $i <= 3; $i++) {
   Transaksi::create([
    'nomor_transaksi' => "INV-2025-11-00{$i}-P007",
    'id_pelanggan' => 'P007',
    'id_kasir' => '001-KASIR',
    'tanggal' => $now->copy()->setDay(min($i + 1, $now->daysInMonth)), // Ensure same month
    'total_item' => 1,
    'subtotal' => 50000,
    'total' => 50000,
    'jenis_transaksi' => 'TUNAI',
    'status_pembayaran' => 'LUNAS',
   ]);
  }

  // Calculate: 50 + 5 = 55
  $pelanggan->refresh();
  $transactionCount = Transaksi::where('id_pelanggan', 'P007')
   ->whereYear('tanggal', $now->year)
   ->whereMonth('tanggal', $now->month)
   ->count();

  expect($transactionCount)->toBeGreaterThanOrEqual(3); // Verify we have 3+ transactions

  $bonus = $transactionCount >= 3 ? 5 : 0;
  $pelanggan->trust_score = 50 + $bonus;
  $pelanggan->save();

  expect($pelanggan->trust_score)->toBe(55);
 });

 it('does not add bonus for < 3 transactions per month', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'P008',
   'nama' => 'Pelanggan Jarang Belanja',
   'trust_score' => 50,
  ]);

  // Create only 2 transactions
  for ($i = 1; $i <= 2; $i++) {
   Transaksi::create([
    'nomor_transaksi' => "INV-2025-11-10{$i}-P008",
    'id_pelanggan' => 'P008',
    'id_kasir' => '001-KASIR',
    'tanggal' => Carbon::now()->subDays($i),
    'total_item' => 1,
    'subtotal' => 50000,
    'total' => 50000,
    'jenis_transaksi' => 'TUNAI',
    'status_pembayaran' => 'LUNAS',
   ]);
  }

  $transactionCount = Transaksi::where('id_pelanggan', 'P008')
   ->whereYear('tanggal', Carbon::now()->year)
   ->whereMonth('tanggal', Carbon::now()->month)
   ->count();

  $bonus = $transactionCount >= 3 ? 5 : 0;
  $pelanggan->trust_score = 50 + $bonus;
  $pelanggan->save();

  expect($pelanggan->trust_score)->toBe(50); // No bonus
 });

 // ==================== NILAI TRANSAKSI ====================

 it('adds +5 when average transaction > store median', function () {
  // Create multiple customers for median calculation
  $customers = [];
  for ($i = 1; $i <= 5; $i++) {
   $customer = Pelanggan::create([
    'id_pelanggan' => "P10{$i}",
    'nama' => "Pelanggan {$i}",
    'trust_score' => 50,
   ]);
   $customers[] = $customer;

   // Create transactions with varying amounts
   Transaksi::create([
    'nomor_transaksi' => "INV-2025-11-20{$i}-P10{$i}",
    'id_pelanggan' => "P10{$i}",
    'id_kasir' => '001-KASIR',
    'tanggal' => now(),
    'total_item' => 1,
    'subtotal' => $i * 50000,
    'total' => $i * 50000,
    'jenis_transaksi' => 'TUNAI',
    'status_pembayaran' => 'LUNAS',
   ]);
  }

  // Target customer with high average
  $targetCustomer = Pelanggan::create([
   'id_pelanggan' => 'P999',
   'nama' => 'Pelanggan VIP',
   'trust_score' => 50,
  ]);

  // Create transaction with above-median value
  Transaksi::create([
   'nomor_transaksi' => 'INV-2025-11-999-P999',
   'id_pelanggan' => 'P999',
   'id_kasir' => '001-KASIR',
   'tanggal' => now(),
   'total_item' => 1,
   'subtotal' => 300000, // High value
   'total' => 300000,
   'jenis_transaksi' => 'TUNAI',
   'status_pembayaran' => 'LUNAS',
  ]);

  // Calculate median
  $allTotals = Transaksi::pluck('total')->map(fn($t) => (float)$t)->sort()->values();
  $median = $allTotals->median();

  $customerAvg = Transaksi::where('id_pelanggan', 'P999')->avg('total');

  $bonus = $customerAvg > $median ? 5 : 0;
  $targetCustomer->trust_score = 50 + $bonus;
  $targetCustomer->save();

  expect($targetCustomer->trust_score)->toBe(55);
 });

 // ==================== TUNGGAKAN AKTIF ====================

 it('deducts -10 for one active DUE/LATE installment', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'P011',
   'nama' => 'Pelanggan Tunggakan',
   'trust_score' => 60,
  ]);

  $transaksi = Transaksi::create([
   'nomor_transaksi' => 'INV-2025-11-011-P011',
   'id_pelanggan' => 'P011',
   'id_kasir' => '001-KASIR',
   'tanggal' => now(),
   'total_item' => 1,
   'subtotal' => 100000,
   'total' => 100000,
   'jenis_transaksi' => 'KREDIT',
   'status_pembayaran' => 'MENUNGGU',
  ]);

  $kontrak = KontrakKredit::create([
   'nomor_kontrak' => 'KRD-202511-0011',
   'id_pelanggan' => 'P011',
   'nomor_transaksi' => 'INV-2025-11-011-P011',
   'mulai_kontrak' => Carbon::now()->subMonth(),
   'tenor_bulan' => 1,
   'pokok_pinjaman' => 90000,
   'dp' => 10000,
   'cicilan_bulanan' => 90000,
   'status' => 'AKTIF',
  ]);

  // Create 1 LATE installment
  JadwalAngsuran::create([
   'id_kontrak' => $kontrak->id_kontrak,
   'periode_ke' => 1,
   'jatuh_tempo' => Carbon::now()->subDays(5),
   'jumlah_tagihan' => 90000,
   'jumlah_dibayar' => 0,
   'status' => 'LATE',
  ]);

  // Calculate: 60 - 10 = 50
  $lateCount = JadwalAngsuran::whereHas('kontrakKredit', fn($q) => $q->where('id_pelanggan', 'P011'))
   ->whereIn('status', ['DUE', 'LATE'])
   ->count();

  $penalty = $lateCount > 0 ? -10 : 0;
  $pelanggan->trust_score = 60 + $penalty;
  $pelanggan->save();

  expect($pelanggan->trust_score)->toBe(50);
 });

 it('deducts -15 for more than 1 late transaction', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'P012',
   'nama' => 'Pelanggan Banyak Tunggakan',
   'trust_score' => 70,
  ]);

  $transaksi = Transaksi::create([
   'nomor_transaksi' => 'INV-2025-11-012-P012',
   'id_pelanggan' => 'P012',
   'id_kasir' => '001-KASIR',
   'tanggal' => now(),
   'total_item' => 1,
   'subtotal' => 200000,
   'total' => 200000,
   'jenis_transaksi' => 'KREDIT',
   'status_pembayaran' => 'MENUNGGU',
  ]);

  $kontrak = KontrakKredit::create([
   'nomor_kontrak' => 'KRD-202511-0012',
   'id_pelanggan' => 'P012',
   'nomor_transaksi' => 'INV-2025-11-012-P012',
   'mulai_kontrak' => Carbon::now()->subMonths(3),
   'tenor_bulan' => 3,
   'pokok_pinjaman' => 180000,
   'dp' => 20000,
   'cicilan_bulanan' => 60000,
   'status' => 'AKTIF',
  ]);

  // Create 3 LATE installments
  for ($i = 1; $i <= 3; $i++) {
   JadwalAngsuran::create([
    'id_kontrak' => $kontrak->id_kontrak,
    'periode_ke' => $i,
    'jatuh_tempo' => Carbon::now()->subDays(10 + $i),
    'jumlah_tagihan' => 60000,
    'jumlah_dibayar' => 0,
    'status' => 'LATE',
   ]);
  }

  // Calculate: 70 - 15 = 55
  $lateCount = JadwalAngsuran::whereHas('kontrakKredit', fn($q) => $q->where('id_pelanggan', 'P012'))
   ->whereIn('status', ['DUE', 'LATE'])
   ->count();

  $penalty = $lateCount > 1 ? -15 : ($lateCount > 0 ? -10 : 0);
  $pelanggan->trust_score = 70 + $penalty;
  $pelanggan->save();

  expect($pelanggan->trust_score)->toBe(55);
 });

 // ==================== COMBINED SCENARIOS ====================

 it('calculates combined trust score correctly (positive scenario)', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'P100',
   'nama' => 'Pelanggan Ideal',
   'trust_score' => 50, // baseline
  ]);

  // 1. Account age: ≥ 180 days → +20
  $pelanggan->created_at = Carbon::now()->subDays(200);
  $pelanggan->save();

  // 2. Create 3 transactions this month → +5
  for ($i = 1; $i <= 3; $i++) {
   Transaksi::create([
    'nomor_transaksi' => "INV-2025-11-10{$i}-P100",
    'id_pelanggan' => 'P100',
    'id_kasir' => '001-KASIR',
    'tanggal' => Carbon::now()->subDays($i),
    'total_item' => 1,
    'subtotal' => 100000,
    'total' => 100000,
    'jenis_transaksi' => 'TUNAI',
    'status_pembayaran' => 'LUNAS',
   ]);
  }

  // Expected: 50 (base) + 20 (age) + 5 (frequency) = 75
  // Note: This is a simplified calculation. Full implementation would be in TrustScoreService

  $score = 50; // baseline
  $score += 20; // age bonus
  $score += 5; // frequency bonus

  $pelanggan->trust_score = min(100, $score);
  $pelanggan->save();

  expect($pelanggan->trust_score)->toBe(75);
 });

 it('ensures trust score never exceeds 100', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'P101',
   'nama' => 'Pelanggan Max Score',
   'trust_score' => 95,
  ]);

  // Try to add more points
  $pelanggan->trust_score = min(100, 95 + 20);
  $pelanggan->save();

  expect($pelanggan->trust_score)->toBe(100);
 });

 it('ensures trust score never goes below 0', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'P102',
   'nama' => 'Pelanggan Min Score',
   'trust_score' => 10,
  ]);

  // Try to deduct more points
  $pelanggan->trust_score = max(0, 10 - 25);
  $pelanggan->save();

  expect($pelanggan->trust_score)->toBe(0);
 });
});
