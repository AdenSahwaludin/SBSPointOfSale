# Trust Score Calculation Tests 🎯

## Overview
Comprehensive feature tests untuk memvalidasi semua aturan perhitungan Trust Score sesuai spesifikasi dalam `brief/Catatan_Limit.txt`.

## Test Coverage ✅

### 1. **Umur Akun** (Account Age)
- ✅ **Baseline 50** untuk akun baru (< 30 hari)
- ✅ **+10 poin** untuk akun ≥ 30 hari
- ✅ **+20 poin** untuk akun ≥ 180 hari

### 2. **Riwayat Angsuran** (Installment History)
- ✅ **+2 poin** per angsuran yang dibayar tepat waktu
- ✅ **-5 poin** per angsuran yang telat
- ✅ **-25 poin** untuk angsuran yang gagal bayar (VOID status)

### 3. **Frekuensi Belanja** (Shopping Frequency)
- ✅ **+5 poin** untuk pelanggan dengan ≥ 3 transaksi/bulan
- ✅ **No bonus** untuk < 3 transaksi/bulan

### 4. **Nilai Transaksi** (Transaction Value)
- ✅ **+5 poin** jika rata-rata transaksi > median toko
- ✅ Calculated against all customers' transaction averages

### 5. **Tunggakan Aktif** (Active Arrears)
- ✅ **-10 poin** untuk 1 angsuran DUE/LATE
- ✅ **-15 poin** untuk > 1 angsuran DUE/LATE

### 6. **Edge Cases**
- ✅ Trust score **maksimal 100** (tidak bisa lebih)
- ✅ Trust score **minimal 0** (tidak bisa negatif)
- ✅ Combined scenario test (multiple rules applied together)

## Running Tests 🚀

### Run all trust score tests:
```bash
php artisan test --filter TrustScoreCalculation
```

### Run with verbose output:
```bash
php artisan test --filter TrustScoreCalculation -v
```

### Run specific test:
```bash
php artisan test --filter "it adds +10 for accounts"
```

## Test Statistics 📊

- **Total Tests**: 14
- **Total Assertions**: 16
- **Passing Rate**: 100% ✅
- **Average Duration**: ~1.5s

## Test Structure 🏗️

```php
describe('Trust Score Calculation - Complete Rules', function () {
    
    beforeEach(function () {
        // Clean database dan create test kasir user
    });

    // Test cases organized by rule category
    it('test description', function () {
        // Arrange: Setup data
        // Act: Apply logic
        // Assert: Verify expectations
    });
});
```

## Database Setup ⚙️

Tests menggunakan SQLite in-memory database dengan RefreshDatabase trait:
- Otomatis migrate dan rollback setiap test
- Data isolated per test case
- Foreign key constraints enforced

## Dependencies 📦

Required models:
- `Pelanggan`
- `Transaksi`
- `KontrakKredit`
- `JadwalAngsuran`
- `User` (Pengguna)

Required services:
- `TrustScoreService` (untuk account age rule)

## Sample Test Output 📋

```
✓ Trust Score Calculation - Complete Rules → it gives baseline 50 for new accounts (< 30 days)
✓ Trust Score Calculation - Complete Rules → it adds +10 for accounts ≥ 30 days old
✓ Trust Score Calculation - Complete Rules → it adds +20 for accounts ≥ 180 days old
✓ Trust Score Calculation - Complete Rules → it adds +2 per installment paid on time
✓ Trust Score Calculation - Complete Rules → it deducts -5 per late installment
✓ Trust Score Calculation - Complete Rules → it deducts -25 for failed payment
✓ Trust Score Calculation - Complete Rules → it adds +5 for customers with ≥ 3 transactions per month
✓ Trust Score Calculation - Complete Rules → it does not add bonus for < 3 transactions per month
✓ Trust Score Calculation - Complete Rules → it adds +5 when average transaction > store median
✓ Trust Score Calculation - Complete Rules → it deducts -10 for one active DUE/LATE installment
✓ Trust Score Calculation - Complete Rules → it deducts -15 for more than 1 late transaction
✓ Trust Score Calculation - Complete Rules → it calculates combined trust score correctly
✓ Trust Score Calculation - Complete Rules → it ensures trust score never exceeds 100
✓ Trust Score Calculation - Complete Rules → it ensures trust score never goes below 0

Tests:    14 passed (16 assertions)
Duration: 1.50s
```

## Next Steps 🔄

These tests validate the **calculation logic**. Next steps:

1. **Implement Full Service** - Create `TrustScoreService::calculateFullScore()` method
2. **Scheduled Job** - Create artisan command untuk recalculate scores secara batch
3. **Real-time Updates** - Trigger recalculation saat event terjadi (payment received, etc.)
4. **Admin UI** - Interface untuk manual recalculation dan audit trail
5. **Performance** - Add indexes dan optimize queries untuk production

## Notes 📝

- Tests currently use **manual score calculation** to verify logic
- Production implementation akan menggunakan centralized `TrustScoreService`
- Semua rules sesuai dengan spec di `brief/Catatan_Limit.txt`
- Edge cases dan boundary conditions sudah di-cover

## Troubleshooting 🔧

### Foreign Key Errors
Pastikan `User` (kasir) dibuat di `beforeEach()`:
```php
User::create([
    'id_pengguna' => '001-KASIR',
    'role' => 'kasir',
    // ...
]);
```

### Transaction Month Issues
Gunakan `Carbon::now()->setDay()` untuk ensure same month:
```php
'tanggal' => $now->copy()->setDay($i),
```

### SQLite vs MySQL
Tests run on SQLite (in-memory). Production uses MySQL.
Foreign key constraints work differently - test accordingly.

---
Created: November 3, 2025
Last Updated: November 3, 2025
Status: ✅ All Tests Passing
