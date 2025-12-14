# Sistem Auto-Increase Credit Limit & Saldo Kredit 💳

## Overview

Sistem otomatis untuk meningkatkan credit limit dan mengelola saldo kredit berdasarkan aktivitas transaksi pelanggan. Setiap transaksi yang berhasil akan memicu perhitungan ulang untuk potensi peningkatan limit.

## 📋 Aturan Peningkatan Credit Limit

### Trust Score Requirement

- **Syarat Utama**: Trust score minimal **70** untuk bisa mendapatkan peningkatan credit limit
- Customer dengan trust score < 70 tidak akan mendapat auto-increase

### Bonus Based on Transaction Frequency (6 bulan terakhir)

| Transaction Count | Bonus | Deskripsi    |
| ----------------- | ----- | ------------ |
| 0-2               | 0%    | No bonus     |
| 3-5               | 10%   | Mulai aktif  |
| 6-10              | 15%   | Sangat aktif |
| 11+               | 20%   | Sangat loyal |

**Formula**: Bonus = Total Spending (6 bulan) × Frequency Percentage × Trust Multiplier

### Trust Score Multiplier

| Trust Score | Multiplier | Contoh   |
| ----------- | ---------- | -------- |
| 70-74       | 1.0×       | Standard |
| 75-89       | 1.2×       | Premium  |
| 90+         | 1.5×       | VIP      |

### Contoh Perhitungan

**Scenario**: Pelanggan P002 dengan trust score 80, melakukan 6 transaksi dalam 6 bulan dengan total Rp 9,000,000

```
Base Limit (calculated) = Rp 2,700,000  (30% × 9M)
Frequency Bonus = 15% (6 transactions)
Trust Multiplier = 1.2× (score 80)

Increase Amount = 9,000,000 × 15% × 1.2 = Rp 1,620,000
New Limit = 2,700,000 + 1,620,000 = Rp 4,320,000
```

## 🔄 Saldo Kredit Management

### Available Credit Balance

- **Credit Limit**: Total maksimal yang boleh dipinjam (bisa bertambah)
- **Saldo Kredit**: Sisa yang masih bisa digunakan = Credit Limit - Outstanding Debt
- **Available Credit**: Kredit yang benar-benar bisa dipakai sekarang

### Flow Ketika Transaksi Kredit

```
1. Customer membuat transaksi kredit Rp 2,000,000
   ↓
2. Saldo kredit berkurang: 5,000,000 - 2,000,000 = 3,000,000
   ↓
3. Customer hanya bisa membuat kredit Rp 3,000,000 lagi
   ↓
4. Saat pembayaran Rp 1,500,000:
   Saldo kredit naik: 3,000,000 + 1,500,000 = 4,500,000
```

## 🛠️ Implementation

### Service Methods

#### `autoIncreaseCredit(Pelanggan $pelanggan): array`

Auto-increase credit limit setelah transaksi berhasil.

```php
$result = CustomerCreditScoringService::autoIncreaseCredit($pelanggan);

// Returns:
[
    'limit_increased' => true,
    'new_limit' => 4320000,
    'increase_amount' => 1620000,
    'reason' => 'Activity bonus: 6 transactions in 6 months (Total: Rp 9,000,000)',
]
```

#### `restoreCreditBalance(Pelanggan $pelanggan, int $paidAmount): array`

Kembalikan saldo kredit saat pelanggan melakukan pembayaran.

```php
$result = CustomerCreditScoringService::restoreCreditBalance($pelanggan, 1000000);

// Returns:
[
    'saldo_restored' => true,
    'original_saldo' => 3000000,
    'new_saldo' => 2000000,
    'amount_restored' => 1000000,
]
```

#### `isCreditEligible(Pelanggan $pelanggan): array`

Cek apakah pelanggan memenuhi syarat untuk membuat transaksi kredit.

```php
$result = CustomerCreditScoringService::isCreditEligible($pelanggan);

// Returns (eligible):
[
    'eligible' => true,
    'available_limit' => 3000000,
    'message' => 'Pelanggan memenuhi syarat untuk kredit.',
]

// Returns (not eligible):
[
    'eligible' => false,
    'available_limit' => 0,
    'message' => 'Credit limit habis. Silakan lunasi cicilan terlebih dahulu.',
]
```

#### `getDetailedScoreBreakdown(Pelanggan $pelanggan): array`

Dapatkan breakdown lengkap credit score untuk customer.

```php
$breakdown = CustomerCreditScoringService::getDetailedScoreBreakdown($pelanggan);

// Returns:
[
    'trust_score' => 80,
    'credit_limit' => 4320000,
    'saldo_kredit' => 2000000,
    'available_credit' => 2320000,
    'transactions_6_months' => 6,
    'membership_days' => 180,
    'status_kredit' => 'aktif',
    'eligibility' => [...],
    'limit_breakdown' => [...],
    'trust_factor' => 1.3,
]
```

## 🔌 Integration Points

### Dalam TransaksiPOSController

```php
// Setelah transaksi disimpan dan di-commit
DB::commit();

// Auto-increase credit limit jika eligible
$pelanggan = Pelanggan::find($request->id_pelanggan);
if ($pelanggan && $pelanggan->trust_score >= 70) {
    CustomerCreditScoringService::autoIncreaseCredit($pelanggan);
}
```

## 📊 Contoh Scenario Real-World

### Customer Baru (P004 - Umum)

```
Initial: TS=50, Limit=0, Saldo=0
Status: Tidak boleh kredit (TS < 70)
```

### Setelah 1 Bulan Transaksi

```
TS meningkat ke 60 (account age bonus)
Limit tetap 0
Status: Manual review needed (MANUAL_REVIEW)
```

### Setelah 6 Bulan Transaksi Aktif

```
TS meningkat ke 75 (account age + activity)
Total spending 6M: Rp 12,000,000
Transaction count: 7

Calculated limit = 30% × 12M = Rp 3,600,000
Frequency bonus = 15% (7 tx)
Trust multiplier = 1.2× (TS 75)

Increase = 12M × 15% × 1.2 = Rp 2,160,000
NEW LIMIT = 3,600,000 + 2,160,000 = Rp 5,760,000

Status: APPROVED ✅
Available Credit: Rp 5,760,000 (belum pakai)
```

### Saat Membuat Transaksi Kredit Rp 2,000,000

```
Before: Available = 5,760,000
Credit Transaction: -2,000,000

After:
  - Saldo Kredit (outstanding) = 2,000,000
  - Available Credit = 5,760,000 - 2,000,000 = 3,760,000
```

### Saat Pembayaran Rp 500,000

```
Before: Saldo Kredit = 2,000,000
Payment: +500,000

After:
  - Saldo Kredit = 1,500,000
  - Available Credit = 5,760,000 - 1,500,000 = 4,260,000
```

## 🧪 Testing

Semua fitur di-test melalui comprehensive test suite:

```bash
php artisan test tests/Feature/CustomerCreditScoringTest.php
```

**Coverage**:

- ✅ Auto-increase credit functionality
- ✅ Saldo kredit restoration
- ✅ Eligibility checks
- ✅ Detailed breakdowns
- ✅ Edge cases (negative values, max limits, etc)

## 🚀 Benefits

1. **Automatic Reward**: Pelanggan setia otomatis mendapat peningkatan limit
2. **Risk Mitigation**: Hanya customer dengan track record bagus yang mendapat limit naik
3. **Encourages Usage**: Mendorong transaksi rutin untuk unlock benefits
4. **Transparent**: Customer bisa lihat breakdown lengkap kemampuan kredit mereka
5. **Fair**: Sistem berbasis data, bukan subjektif

## ⚙️ Configuration

Tidak ada config file terpisah. Semua aturan hardcoded di dalam service class, mudah di-update jika diperlukan:

```php
// Di CustomerCreditScoringService::calculateCreditIncreaseAmount()
// Edit persentase frequency dan trust multiplier sesuai kebutuhan business
```

## 📝 Notes

- Auto-increase dipicu setiap kali ada transaksi TUNAI atau KREDIT yang LUNAS
- Update terjadi di database secara langsung (via forceFill + save)
- Tidak ada notification/email dikirim saat limit naik (bisa ditambahkan later)
- System round semua nilai ke nearest thousand untuk cleanliness
