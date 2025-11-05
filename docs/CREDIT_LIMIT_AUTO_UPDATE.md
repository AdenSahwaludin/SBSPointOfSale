# Sistem Auto-Update Credit Limit 💳

## 📋 Overview

Sistem ini **otomatis menghitung dan mengupdate credit limit** pelanggan berdasarkan:

1. **Trust Score** (0-100)
2. **Riwayat Transaksi** (kebiasaan belanja)

Credit limit akan **naik otomatis** ketika:

- ✅ Trust score meningkat (karena pembayaran tepat waktu, belanja rutin, dll)
- ✅ Transaksi bertambah (meningkatkan limit base calculation)

---

## 🎯 Cara Kerja

### Formula Perhitungan

Berdasarkan `brief/Catatan_Limit.txt`:

```
credit_limit = limit_base × trust_factor (dibulatkan ke ribuan)
```

### 1. **Limit Base** (pilih nilai terbesar dari 3 metode)

| Metode       | Perhitungan                     | Contoh (Transaksi Rp 1M) |
| ------------ | ------------------------------- | ------------------------ |
| **Method 1** | 50% × Transaksi Terbesar        | Rp 500,000               |
| **Method 2** | 50% × Rata-rata Top 3 Transaksi | Rp 400,000               |
| **Method 3** | 30% × Total 6 Bulan Terakhir    | Rp 900,000               |
| **Selected** | MAX dari ketiga metode          | **Rp 900,000**           |

### 2. **Trust Factor** (multiplier berdasar trust score)

| Trust Score | Factor   | Status           | Contoh Limit |
| ----------- | -------- | ---------------- | ------------ |
| **< 50**    | **0.0×** | ❌ Ditolak       | Rp 0         |
| **50-59**   | **0.7×** | ⚠️ Tinjau Manual | Rp 630k      |
| **60-74**   | **1.0×** | ✅ Layak         | Rp 900k      |
| **75-89**   | **1.3×** | ⭐ Baik          | Rp 1.17jt    |
| **≥ 90**    | **1.5×** | 🌟 Premium       | Rp 1.35jt    |

### 3. **Contoh Perhitungan Lengkap**

**Pelanggan A:**

- Transaksi terbesar: Rp 2,000,000
- Avg top 3: Rp 1,800,000
- Total 6 bulan: Rp 10,000,000
- Trust Score: **80**

**Calculation:**

```
Method 1: 50% × 2,000,000 = Rp 1,000,000
Method 2: 50% × 1,800,000 = Rp   900,000
Method 3: 30% × 10,000,000 = Rp 3,000,000  ← TERBESAR

Limit Base: Rp 3,000,000
Trust Factor: 1.3× (TS 80)

Credit Limit = 3,000,000 × 1.3 = Rp 3,900,000 ✅
```

---

## 🔄 Auto-Update Mechanisms

### 1. **Real-time Update (Saat Pembayaran)**

Ketika pelanggan bayar angsuran:

```php
// Event triggered automatically in AngsuranController
event(new PaymentReceived($pembayaran));

// Listener handles async update
UpdateTrustScoreOnPayment::handle()
  → Updates trust score
  → Updates credit limit
  → Logs changes
```

**Triggered by:**

- ✅ Pembayaran angsuran (tepat waktu/telat)
- ✅ Pelunasan cicilan
- ✅ Transaksi POS baru (jika dikonfigurasi)

### 2. **Scheduled Batch Update (Daily)**

Recalculate semua pelanggan setiap hari pukul 02:00:

```bash
# Runs automatically via Laravel scheduler
php artisan schedule:run
```

**Configured in** `routes/console.php`:

```php
Schedule::command(RecalculateTrustScores::class, ['--all'])
    ->dailyAt('02:00')
    ->withoutOverlapping();
```

### 3. **Manual Recalculation**

**Single Customer:**

```bash
php artisan trust-score:recalculate --customer=P001
```

**All Customers:**

```bash
php artisan trust-score:recalculate --all
```

**Dry Run (Preview):**

```bash
php artisan trust-score:recalculate --all --dry-run
```

**Output Example:**

```
📊 Trust Score Breakdown:
┌──────────────────────┬─────────┐
│ Component            │ Points  │
├──────────────────────┼─────────┤
│ Baseline             │ 50      │
│ Account Age          │ +20     │
│ Installment History  │ +6      │
│ Shopping Frequency   │ +5      │
│ Transaction Value    │ +5      │
│ Active Arrears       │ 0       │
│ TOTAL                │ 86/100  │
└──────────────────────┴─────────┘

💳 Credit Limit Breakdown:
┌──────────────────────┬────────────────┐
│ Method               │ Value          │
├──────────────────────┼────────────────┤
│ 50% Largest          │ Rp 1,000,000   │
│ 50% Avg Top 3        │ Rp   900,000   │
│ 30% Last 6 Months    │ Rp 2,400,000   │
│ Selected Base        │ Rp 2,400,000   │
│ Trust Factor         │ 1.3x           │
│ CREDIT LIMIT         │ Rp 3,120,000   │
└──────────────────────┴────────────────┘

📈 Changes:
Trust Score: 75 → 86 (+11)
Credit Limit: Rp 2,600,000 → Rp 3,120,000 (+520,000)
```

---

## 💻 Usage in Code

### Calculate Credit Limit

```php
use App\Services\CreditLimitService;

$pelanggan = Pelanggan::find('P001');

// Get calculation breakdown
$result = CreditLimitService::calculateCreditLimit($pelanggan);
/*
[
    'limit_base' => 2400000,
    'trust_factor' => 1.3,
    'credit_limit' => 3120000,
    'breakdown' => [
        'method_1_half_largest' => 1000000,
        'method_2_avg_top3' => 900000,
        'method_3_six_months' => 2400000,
        'selected_base' => 2400000,
    ]
]
*/
```

### Update Credit Limit

```php
// Update and get new limit
$newLimit = CreditLimitService::updateCreditLimit($pelanggan);
// Returns: 3120000

// Update and get full breakdown
$breakdown = CreditLimitService::updateCreditLimit($pelanggan, true);
// Returns: ['limit_base' => ..., 'trust_factor' => ..., ...]
```

### Check Eligibility

```php
$eligibility = CreditLimitService::checkEligibility($pelanggan->trust_score);
/*
[
    'eligible' => true,
    'status' => 'APPROVED',  // REJECTED | MANUAL_REVIEW | APPROVED
    'message' => 'Layak untuk cicilan. Proses dapat dilanjutkan.'
]
*/
```

### Combined Update (Trust Score + Credit Limit)

```php
use App\Services\TrustScoreService;
use App\Services\CreditLimitService;

// Update trust score
$newScore = TrustScoreService::updateTrustScore($pelanggan);

// Update credit limit (uses new trust score)
$pelanggan->refresh();
$newLimit = CreditLimitService::updateCreditLimit($pelanggan);

Log::info("Updated customer {$pelanggan->nama}", [
    'new_score' => $newScore,
    'new_limit' => $newLimit,
]);
```

---

## 🎨 Admin UI Integration

### Controller Methods

```php
use App\Http\Controllers\Admin\TrustScoreController;

// Show breakdown for single customer
GET /admin/trust-score/{id}
TrustScoreController::show($id)

// Manual recalculate single customer
POST /admin/trust-score/{id}/recalculate
TrustScoreController::recalculate($id)

// Batch recalculate all customers
POST /admin/trust-score/recalculate-all
TrustScoreController::recalculateAll()
```

### Example Response

```json
{
    "customer": {
        "id_pelanggan": "P001",
        "nama": "John Doe",
        "trust_score": 86,
        "credit_limit": 3120000
    },
    "score_breakdown": {
        "baseline": 50,
        "account_age": 20,
        "installment_history": 6,
        "shopping_frequency": 5,
        "transaction_value": 5,
        "active_arrears": 0,
        "total": 86
    },
    "limit_breakdown": {
        "limit_base": 2400000,
        "trust_factor": 1.3,
        "credit_limit": 3120000,
        "breakdown": {
            "method_1_half_largest": 1000000,
            "method_2_avg_top3": 900000,
            "method_3_six_months": 2400000,
            "selected_base": 2400000
        }
    }
}
```

---

## 🚀 Setup Instructions

### 1. Queue Worker (for Real-time Updates)

**Using Supervisor (Production):**

Create `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/pos-sbs/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/pos-sbs/storage/logs/worker.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

**Development:**

```bash
php artisan queue:work
```

### 2. Task Scheduler (for Daily Batch)

**Add to crontab:**

```bash
crontab -e
```

**Add line:**

```
* * * * * cd /path/to/pos-sbs && php artisan schedule:run >> /dev/null 2>&1
```

**Windows (Task Scheduler):**

- Action: Start a program
- Program: `php`
- Arguments: `D:\path\to\pos-sbs\artisan schedule:run`
- Trigger: Daily at 12:00 AM
- Repeat every: 1 minute for 24 hours

### 3. Database Indexes (Performance)

```sql
-- Add indexes for faster queries
CREATE INDEX idx_pelanggan_trust_score ON pelanggan(trust_score);
CREATE INDEX idx_transaksi_pelanggan_tanggal ON transaksi(id_pelanggan, tanggal);
CREATE INDEX idx_jadwal_angsuran_status ON jadwal_angsuran(status);
```

---

## 📊 Monitoring & Logging

### Check Logs

```bash
# View real-time updates
tail -f storage/logs/laravel.log | grep "Trust score and credit limit updated"

# View scheduled job logs
tail -f storage/logs/laravel.log | grep "Recalculation completed"
```

### Log Entry Example

```
[2025-11-04 14:23:45] production.INFO: Trust score and credit limit updated for John Doe
{
    "customer_id": "P001",
    "old_score": 75,
    "new_score": 86,
    "old_limit": 2600000,
    "new_limit": 3120000,
    "payment_id": "PAY-001"
}
```

### Admin Dashboard Query

```php
// Get customers with increased limits today
$increased = Pelanggan::whereDate('updated_at', today())
    ->where('credit_limit', '>', DB::raw('(SELECT credit_limit FROM pelanggan_history WHERE id_pelanggan = pelanggan.id_pelanggan ORDER BY created_at DESC LIMIT 1 OFFSET 1)'))
    ->count();
```

---

## 🧪 Testing

### Run Credit Limit Tests

```bash
# All credit limit tests
php artisan test --filter CreditLimitCalculation

# Specific test
php artisan test --filter "it applies 1.3x factor"
```

### Test Coverage

- ✅ **16 tests**, 31 assertions
- ✅ All calculation methods (Method 1, 2, 3)
- ✅ All trust factors (0.0x, 0.7x, 1.0x, 1.3x, 1.5x)
- ✅ Edge cases (no transactions, rounding, eligibility)
- ✅ Update methods (with/without breakdown)

### Manual Test Scenario

```php
// 1. Create test customer
$customer = Pelanggan::create([
    'id_pelanggan' => 'TEST001',
    'nama' => 'Test Customer',
    'trust_score' => 55,
    'credit_limit' => 0,
]);

// 2. Add transactions
for ($i = 1; $i <= 6; $i++) {
    Transaksi::create([
        'nomor_transaksi' => "TEST-{$i}",
        'id_pelanggan' => 'TEST001',
        'id_kasir' => '001-KASIR',
        'tanggal' => now()->subMonths($i),
        'total' => 500000,
        'jenis_transaksi' => 'TUNAI',
        'status_pembayaran' => 'LUNAS',
    ]);
}

// 3. Calculate limit
$result = CreditLimitService::calculateCreditLimit($customer);
// Expected: Method 3 wins (30% × 3M = 900k)
// With TS 55 (0.7x): 900k × 0.7 = 630k

// 4. Update trust score to 80
$customer->trust_score = 80;
$customer->save();

// 5. Recalculate limit
$newResult = CreditLimitService::calculateCreditLimit($customer);
// Expected: Same base (900k), but 1.3x factor
// New limit: 900k × 1.3 = 1,170,000 ✅ INCREASED!
```

---

## 🔧 Troubleshooting

### Credit Limit Tidak Naik

**Cek:**

1. **Trust Score sudah naik?**

    ```bash
    php artisan tinker
    >>> $p = Pelanggan::find('P001');
    >>> $p->trust_score;
    ```

2. **Transaksi history ada?**

    ```bash
    >>> Transaksi::where('id_pelanggan', 'P001')->count();
    ```

3. **Queue worker running?**

    ```bash
    php artisan queue:work --once
    ```

4. **Manual recalculate:**
    ```bash
    php artisan trust-score:recalculate --customer=P001
    ```

### Event Tidak Trigger

**Check:**

```php
// In AngsuranController pay method
use App\Events\PaymentReceived;

public function pay(Request $request, string $nomor_kontrak)
{
    // After successful payment
    event(new PaymentReceived($pembayaran)); // ← Add this

    return redirect()->back();
}
```

### Scheduled Task Tidak Jalan

**Test manually:**

```bash
php artisan schedule:test
```

**Run manually:**

```bash
php artisan trust-score:recalculate --all
```

---

## 📈 Best Practices

### 1. **Regular Monitoring**

- Check daily batch job logs setiap pagi
- Monitor customers dengan limit increase/decrease besar

### 2. **Manual Review Thresholds**

- Trust Score 50-69 → Admin harus approve manual
- Limit increase > 50% → Notify admin

### 3. **Performance Optimization**

- Batch update di off-peak hours (02:00 AM)
- Add database indexes untuk large datasets
- Use queue for real-time updates (non-blocking)

### 4. **Data Integrity**

- Backup database sebelum mass recalculation
- Log semua changes untuk audit trail
- Validate calculation results periodically

---

## 📝 TODO / Future Enhancements

- [ ] Credit limit history tracking table
- [ ] Email notification untuk limit increase
- [ ] Admin approval workflow untuk limit > threshold
- [ ] Dynamic trust factor adjustment based on store performance
- [ ] Customer segmentation (Bronze/Silver/Gold) based on limit
- [ ] API endpoints untuk mobile app
- [ ] Real-time dashboard untuk monitoring changes
- [ ] Machine learning untuk predictive limit calculation

---

## 📚 Related Documentation

- [Trust Score Implementation](TRUST_SCORE_IMPLEMENTATION.md)
- [Brief/Catatan_Limit.txt](../brief/Catatan_Limit.txt)
- [Test Documentation](../tests/Feature/README_TrustScore.md)

---

**Last Updated:** November 4, 2025  
**Version:** 1.0.0  
**Status:** ✅ Production Ready
