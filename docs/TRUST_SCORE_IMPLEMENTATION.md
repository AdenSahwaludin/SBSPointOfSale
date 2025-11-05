# Trust Score System - Implementation Guide 🎯

## Overview

Sistem Trust Score yang lengkap dengan perhitungan otomatis, event listeners, scheduled jobs, dan admin UI.

## ✅ Implemented Features

### 1. **Full Service Implementation** (`TrustScoreService.php`)

#### Method Utama:

```php
// Calculate full score with breakdown
$breakdown = TrustScoreService::calculateFullScore($pelanggan);
// Returns:
// [
//     'baseline' => 50,
//     'account_age' => 20,
//     'installment_history' => 6,
//     'shopping_frequency' => 5,
//     'transaction_value' => 5,
//     'active_arrears' => -10,
//     'total' => 76
// ]

// Update trust score and save
$newScore = TrustScoreService::updateTrustScore($pelanggan);

// Update with breakdown
$breakdown = TrustScoreService::updateTrustScore($pelanggan, true);
```

#### Scoring Rules Implemented:

- ✅ **Baseline**: 50 points
- ✅ **Account Age**: +10 (≥30d), +20 (≥180d)
- ✅ **Installment History**: +2 (on-time), -5 (late), -25 (failed)
- ✅ **Shopping Frequency**: +5 (≥3 transactions/month)
- ✅ **Transaction Value**: +5 (average > median)
- ✅ **Active Arrears**: -10 (1 late), -15 (>1 late)
- ✅ **Score Limits**: 0-100 (clamped)

### 2. **Artisan Command** (`RecalculateTrustScores`)

#### Usage:

```bash
# Recalculate for specific customer
php artisan trust-score:recalculate --customer=P001

# Recalculate for all customers
php artisan trust-score:recalculate --all

# Dry run (preview without saving)
php artisan trust-score:recalculate --all --dry-run
```

#### Features:

- ✅ Single customer or batch processing
- ✅ Progress bar for batch operations
- ✅ Detailed breakdown display
- ✅ Dry-run mode for testing
- ✅ Summary statistics

#### Scheduled Execution:

Command otomatis dijalankan daily di `routes/console.php`:

```php
Schedule::command(RecalculateTrustScores::class, ['--all'])
    ->dailyAt('02:00')
    ->name('recalculate-trust-scores')
    ->withoutOverlapping();
```

### 3. **Event-Driven Updates**

#### Event: `PaymentReceived`

Triggered saat payment diterima untuk update trust score secara real-time.

#### Listener: `UpdateTrustScoreOnPayment`

- ✅ Implements `ShouldQueue` untuk async processing
- ✅ Auto-recalculates trust score after payment
- ✅ Logs before/after scores
- ✅ Error handling & logging

#### Trigger Event:

```php
use App\Events\PaymentReceived;

// After saving payment
event(new PaymentReceived($pembayaran));
```

### 4. **Admin Controller** (`TrustScoreController`)

#### Routes (add to `routes/admin.php`):

```php
Route::get('/trust-score/{id}', [TrustScoreController::class, 'show'])
    ->name('trust-score.show');

Route::post('/trust-score/{id}/recalculate', [TrustScoreController::class, 'recalculate'])
    ->name('trust-score.recalculate');

Route::post('/trust-score/recalculate-all', [TrustScoreController::class, 'recalculateAll'])
    ->name('trust-score.recalculate-all');
```

#### Methods:

- `show($id)` - Display detailed breakdown
- `recalculate($id)` - Manual recalculation for one customer
- `recalculateAll()` - Batch recalculation for all customers

## 📊 Usage Examples

### Example 1: Auto-Update on Payment

```php
// In AngsuranController or PembayaranController
use App\Events\PaymentReceived;

// After successful payment save
$pembayaran = Pembayaran::create([...]);
event(new PaymentReceived($pembayaran));

// Trust score akan di-update otomatis via listener
```

### Example 2: Manual Recalculation

```php
use App\Services\TrustScoreService;

$pelanggan = Pelanggan::find('P001');

// Get breakdown only
$breakdown = TrustScoreService::calculateFullScore($pelanggan);
echo "New score would be: " . $breakdown['total'];

// Update and save
$newScore = TrustScoreService::updateTrustScore($pelanggan);
echo "Updated to: {$newScore}";
```

### Example 3: Display Breakdown in Blade/Vue

```php
// Controller
$breakdown = TrustScoreService::calculateFullScore($pelanggan);
return view('admin.pelanggan.show', compact('pelanggan', 'breakdown'));
```

```vue
<!-- Vue Component -->
<div class="trust-score-breakdown">
  <h3>Trust Score: {{ breakdown.total }}</h3>
  <ul>
    <li>Baseline: {{ breakdown.baseline }}</li>
    <li>Account Age: {{ breakdown.account_age > 0 ? '+' : '' }}{{ breakdown.account_age }}</li>
    <li>Installments: {{ breakdown.installment_history > 0 ? '+' : '' }}{{ breakdown.installment_history }}</li>
    <li>Frequency: {{ breakdown.shopping_frequency > 0 ? '+' : '' }}{{ breakdown.shopping_frequency }}</li>
    <li>Value: {{ breakdown.transaction_value > 0 ? '+' : '' }}{{ breakdown.transaction_value }}</li>
    <li>Arrears: {{ breakdown.active_arrears }}</li>
  </ul>
</div>
```

## 🔧 Setup & Configuration

### 1. Run Migrations

```bash
php artisan migrate
```

### 2. Setup Queue Worker (untuk Event Listener)

```bash
# Development
php artisan queue:work

# Production (supervisor/systemd)
php artisan queue:work --queue=default --tries=3
```

### 3. Setup Scheduler (untuk Daily Recalculation)

```bash
# Add to crontab
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Test Command

```bash
# Test with dry-run
php artisan trust-score:recalculate --all --dry-run

# Run for real
php artisan trust-score:recalculate --all
```

## 📝 Integration Points

### Where to Trigger Events:

1. **Pembayaran Angsuran** (`AngsuranController::pay`)

```php
$pembayaran = Pembayaran::create([...]);
event(new PaymentReceived($pembayaran));
```

2. **Transaksi POS Lunas** (`TransaksiPOSController::store`)

```php
if ($transaksi->status_pembayaran === 'LUNAS') {
    // Optionally recalculate immediately
    TrustScoreService::updateTrustScore($pelanggan);
}
```

3. **Admin Manual Update** (`PelangganController::show`)

```php
// Add button in view to trigger recalculate
```

## 🎨 Admin UI (To Be Created)

### Recommended Views:

1. **`Admin/TrustScore/Show.vue`**
    - Display detailed breakdown
    - Show history/changes
    - Manual recalculate button
    - Score trend chart

2. **Add to `Admin/Pelanggan/Index.vue`**
    - Trust score badge/indicator
    - Quick recalculate button

3. **Add to `Admin/Pelanggan/Show.vue`**
    - Trust score section
    - Link to detailed breakdown
    - Recalculate button

## 📈 Monitoring & Logging

Trust score updates are logged:

```
[INFO] Trust score updated for Budi Santoso
{
    "customer_id": "P001",
    "old_score": 50,
    "new_score": 76,
    "payment_id": 123
}
```

Check logs:

```bash
tail -f storage/logs/laravel.log | grep "Trust score"
```

## 🧪 Testing

All scoring rules tested:

```bash
php artisan test --filter TrustScoreCalculation
# 14 tests, 16 assertions - All passing ✅
```

## 🚀 Performance Considerations

1. **Queue Processing**: Listener uses `ShouldQueue` untuk async
2. **Batch Operations**: Command uses chunking untuk large datasets
3. **Caching**: Consider caching median calculation
4. **Indexes**: Ensure proper DB indexes on:
    - `pelanggan.id_pelanggan`
    - `transaksi.id_pelanggan`, `transaksi.status_pembayaran`
    - `jadwal_angsuran.status`, `jadwal_angsuran.id_kontrak`

## 📋 TODO / Future Enhancements

- [ ] Create Vue components for admin UI
- [ ] Add score history tracking (audit table)
- [ ] Implement caching for expensive calculations
- [ ] Add API endpoints for mobile/external access
- [ ] Create dashboard widgets for trust score analytics
- [ ] Add email notifications for score changes
- [ ] Implement score prediction ML model

## 🔗 Related Files

- Service: `app/Services/TrustScoreService.php`
- Command: `app/Console/Commands/RecalculateTrustScores.php`
- Event: `app/Events/PaymentReceived.php`
- Listener: `app/Listeners/UpdateTrustScoreOnPayment.php`
- Controller: `app/Http/Controllers/Admin/TrustScoreController.php`
- Tests: `tests/Feature/TrustScoreCalculationTest.php`
- Schedule: `routes/console.php`
- Providers: `app/Providers/AppServiceProvider.php`

---

**Created**: November 3, 2025  
**Status**: ✅ Core Implementation Complete  
**Next**: Create Admin UI Components
