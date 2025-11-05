<?php

use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\CreditLimitService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Credit Limit Calculation', function () {
 beforeEach(function () {
  // Create test kasir user
  User::create([
   'id_pengguna' => '001-KASIR',
   'username' => 'kasir_test',
   'nama' => 'Kasir Test',
   'role' => 'kasir',
   'password' => bcrypt('password'),
   'aktif' => true,
  ]);
 });

 // ==================== LIMIT BASE CALCULATION ====================

 it('calculates limit base using method 1: 50% of largest transaction', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'CL001',
   'nama' => 'Test Customer 1',
   'trust_score' => 75,
  ]);

  // Create transactions with different values
  Transaksi::create([
   'nomor_transaksi' => 'INV-001',
   'id_pelanggan' => 'CL001',
   'id_kasir' => '001-KASIR',
   'tanggal' => now(),
   'total_item' => 1,
   'subtotal' => 1000000, // Largest
   'total' => 1000000,
   'jenis_transaksi' => 'TUNAI',
   'status_pembayaran' => 'LUNAS',
  ]);

  $result = CreditLimitService::calculateCreditLimit($pelanggan);

  // Method 1: 50% * 1,000,000 = 500,000
  expect($result['breakdown']['method_1_half_largest'])->toBe(500000);
 });

 it('calculates limit base using method 2: 50% of average top 3 transactions', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'CL002',
   'nama' => 'Test Customer 2',
   'trust_score' => 75,
  ]);

  // Create 5 transactions
  $amounts = [1000000, 800000, 600000, 400000, 200000];
  foreach ($amounts as $i => $amount) {
   Transaksi::create([
    'nomor_transaksi' => "INV-002-{$i}",
    'id_pelanggan' => 'CL002',
    'id_kasir' => '001-KASIR',
    'tanggal' => now()->subDays($i),
    'total_item' => 1,
    'subtotal' => $amount,
    'total' => $amount,
    'jenis_transaksi' => 'TUNAI',
    'status_pembayaran' => 'LUNAS',
   ]);
  }

  $result = CreditLimitService::calculateCreditLimit($pelanggan);

  // Method 2: 50% * avg(1M, 800k, 600k) = 50% * 800,000 = 400,000
  $expectedAvgTop3 = (int)((1000000 + 800000 + 600000) / 3 * 0.5);
  expect($result['breakdown']['method_2_avg_top3'])->toBe($expectedAvgTop3);
 });

 it('calculates limit base using method 3: 30% of last 6 months spending', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'CL003',
   'nama' => 'Test Customer 3',
   'trust_score' => 75,
  ]);

  // Create transactions over 6 months
  for ($i = 0; $i < 6; $i++) {
   Transaksi::create([
    'nomor_transaksi' => "INV-003-{$i}",
    'id_pelanggan' => 'CL003',
    'id_kasir' => '001-KASIR',
    'tanggal' => Carbon::now()->subMonths($i)->startOfMonth(),
    'total_item' => 1,
    'subtotal' => 500000,
    'total' => 500000,
    'jenis_transaksi' => 'TUNAI',
    'status_pembayaran' => 'LUNAS',
   ]);
  }

  $result = CreditLimitService::calculateCreditLimit($pelanggan);

  // Method 3: 30% * (500k * 6) = 30% * 3M = 900,000
  expect($result['breakdown']['method_3_six_months'])->toBe(900000);
 });

 it('selects the largest value as base limit', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'CL004',
   'nama' => 'Test Customer 4',
   'trust_score' => 75,
  ]);

  // Create scenario where method 3 is largest
  for ($i = 0; $i < 12; $i++) {
   Transaksi::create([
    'nomor_transaksi' => "INV-004-{$i}",
    'id_pelanggan' => 'CL004',
    'id_kasir' => '001-KASIR',
    'tanggal' => Carbon::now()->subMonths($i)->startOfMonth(),
    'total_item' => 1,
    'subtotal' => 300000,
    'total' => 300000,
    'jenis_transaksi' => 'TUNAI',
    'status_pembayaran' => 'LUNAS',
   ]);
  }

  $result = CreditLimitService::calculateCreditLimit($pelanggan);

  // Method 3 should be: 30% * (300k * 6 from last 6 months) = 540,000
  $expected = (int)(300000 * 6 * 0.3);

  // Base should be max of all methods
  expect($result['limit_base'])->toBeGreaterThanOrEqual($expected);
 });

 // ==================== TRUST SCORE FACTOR ====================

 it('applies 0.0x factor for trust score < 50 (rejected)', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'CL005',
   'nama' => 'Low Trust Customer',
   'trust_score' => 40,
  ]);

  Transaksi::create([
   'nomor_transaksi' => 'INV-005',
   'id_pelanggan' => 'CL005',
   'id_kasir' => '001-KASIR',
   'tanggal' => now(),
   'total_item' => 1,
   'subtotal' => 1000000,
   'total' => 1000000,
   'jenis_transaksi' => 'TUNAI',
   'status_pembayaran' => 'LUNAS',
  ]);

  $result = CreditLimitService::calculateCreditLimit($pelanggan);

  expect($result['trust_factor'])->toBe(0.0);
  expect($result['credit_limit'])->toBe(0);
 });

 it('applies 0.7x factor for trust score 50-59', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'CL006',
   'nama' => 'Customer TS 55',
   'trust_score' => 55,
  ]);

  Transaksi::create([
   'nomor_transaksi' => 'INV-006',
   'id_pelanggan' => 'CL006',
   'id_kasir' => '001-KASIR',
   'tanggal' => now(),
   'total_item' => 1,
   'subtotal' => 1000000,
   'total' => 1000000,
   'jenis_transaksi' => 'TUNAI',
   'status_pembayaran' => 'LUNAS',
  ]);

  $result = CreditLimitService::calculateCreditLimit($pelanggan);

  expect($result['trust_factor'])->toBe(0.7);
  // Base: 500,000 * 0.7 = 350,000 (rounded to thousands)
  expect($result['credit_limit'])->toBe(350000);
 });

 it('applies 1.0x factor for trust score 60-74', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'CL007',
   'nama' => 'Customer TS 65',
   'trust_score' => 65,
  ]);

  Transaksi::create([
   'nomor_transaksi' => 'INV-007',
   'id_pelanggan' => 'CL007',
   'id_kasir' => '001-KASIR',
   'tanggal' => now(),
   'total_item' => 1,
   'subtotal' => 1000000,
   'total' => 1000000,
   'jenis_transaksi' => 'TUNAI',
   'status_pembayaran' => 'LUNAS',
  ]);

  $result = CreditLimitService::calculateCreditLimit($pelanggan);

  expect($result['trust_factor'])->toBe(1.0);
  expect($result['credit_limit'])->toBe(500000);
 });

 it('applies 1.3x factor for trust score 75-89', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'CL008',
   'nama' => 'Customer TS 80',
   'trust_score' => 80,
  ]);

  Transaksi::create([
   'nomor_transaksi' => 'INV-008',
   'id_pelanggan' => 'CL008',
   'id_kasir' => '001-KASIR',
   'tanggal' => now(),
   'total_item' => 1,
   'subtotal' => 1000000,
   'total' => 1000000,
   'jenis_transaksi' => 'TUNAI',
   'status_pembayaran' => 'LUNAS',
  ]);

  $result = CreditLimitService::calculateCreditLimit($pelanggan);

  expect($result['trust_factor'])->toBe(1.3);
  // Base: 500,000 * 1.3 = 650,000
  expect($result['credit_limit'])->toBe(650000);
 });

 it('applies 1.5x factor for trust score >= 90', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'CL009',
   'nama' => 'Premium Customer',
   'trust_score' => 95,
  ]);

  Transaksi::create([
   'nomor_transaksi' => 'INV-009',
   'id_pelanggan' => 'CL009',
   'id_kasir' => '001-KASIR',
   'tanggal' => now(),
   'total_item' => 1,
   'subtotal' => 1000000,
   'total' => 1000000,
   'jenis_transaksi' => 'TUNAI',
   'status_pembayaran' => 'LUNAS',
  ]);

  $result = CreditLimitService::calculateCreditLimit($pelanggan);

  expect($result['trust_factor'])->toBe(1.5);
  // Base: 500,000 * 1.5 = 750,000
  expect($result['credit_limit'])->toBe(750000);
 });

 // ==================== SPECIAL CASES ====================

 it('returns 0 for customers with no transaction history', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'CL010',
   'nama' => 'New Customer',
   'trust_score' => 75,
  ]);

  $result = CreditLimitService::calculateCreditLimit($pelanggan);

  expect($result['limit_base'])->toBe(0);
  expect($result['credit_limit'])->toBe(0);
 });

 it('rounds credit limit to nearest thousand', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'CL011',
   'nama' => 'Rounding Test',
   'trust_score' => 75, // 1.3x factor
  ]);

  // Create transaction that results in non-round number
  Transaksi::create([
   'nomor_transaksi' => 'INV-011',
   'id_pelanggan' => 'CL011',
   'id_kasir' => '001-KASIR',
   'tanggal' => now(),
   'total_item' => 1,
   'subtotal' => 777777,
   'total' => 777777,
   'jenis_transaksi' => 'TUNAI',
   'status_pembayaran' => 'LUNAS',
  ]);

  $result = CreditLimitService::calculateCreditLimit($pelanggan);

  // Result should be rounded to thousands
  expect($result['credit_limit'] % 1000)->toBe(0);
 });

 // ==================== ELIGIBILITY CHECK ====================

 it('rejects customers with trust score < 50', function () {
  $result = CreditLimitService::checkEligibility(40);

  expect($result['eligible'])->toBe(false);
  expect($result['status'])->toBe('REJECTED');
 });

 it('requires manual review for trust score 50-69', function () {
  $result = CreditLimitService::checkEligibility(60);

  expect($result['eligible'])->toBe(true);
  expect($result['status'])->toBe('MANUAL_REVIEW');
 });

 it('approves customers with trust score >= 70', function () {
  $result = CreditLimitService::checkEligibility(75);

  expect($result['eligible'])->toBe(true);
  expect($result['status'])->toBe('APPROVED');
 });

 // ==================== UPDATE METHOD ====================

 it('updates customer credit limit correctly', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'CL012',
   'nama' => 'Update Test',
   'trust_score' => 75,
   'credit_limit' => 0, // Initial
  ]);

  Transaksi::create([
   'nomor_transaksi' => 'INV-012',
   'id_pelanggan' => 'CL012',
   'id_kasir' => '001-KASIR',
   'tanggal' => now(),
   'total_item' => 1,
   'subtotal' => 1000000,
   'total' => 1000000,
   'jenis_transaksi' => 'TUNAI',
   'status_pembayaran' => 'LUNAS',
  ]);

  $newLimit = CreditLimitService::updateCreditLimit($pelanggan);

  $pelanggan->refresh();
  expect((int)$pelanggan->credit_limit)->toBe($newLimit);
  expect($newLimit)->toBeGreaterThan(0);
 });

 it('returns breakdown when requested', function () {
  $pelanggan = Pelanggan::create([
   'id_pelanggan' => 'CL013',
   'nama' => 'Breakdown Test',
   'trust_score' => 75,
  ]);

  Transaksi::create([
   'nomor_transaksi' => 'INV-013',
   'id_pelanggan' => 'CL013',
   'id_kasir' => '001-KASIR',
   'tanggal' => now(),
   'total_item' => 1,
   'subtotal' => 1000000,
   'total' => 1000000,
   'jenis_transaksi' => 'TUNAI',
   'status_pembayaran' => 'LUNAS',
  ]);

  $result = CreditLimitService::updateCreditLimit($pelanggan, true);

  expect($result)->toBeArray();
  expect($result)->toHaveKeys(['limit_base', 'trust_factor', 'credit_limit', 'breakdown']);
 });
});
