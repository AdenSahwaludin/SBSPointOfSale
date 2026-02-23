# 🏪 SBS Point of Sale (POS) System

**Sistem Point of Sale modern untuk manajemen penjualan, inventory, dan kredit dengan dukungan partial stock conversion dan screening cicilan pintar berbasis trust score.**

---

## 📋 Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Stack Teknologi](#stack-teknologi)
- [Instalasi](#instalasi)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Fitur Stock Conversion](#fitur-stock-conversion)
- [Screening Cicilan Pintar](#screening-cicilan-pintar)
- [Testing](#testing)
- [Struktur Project](#struktur-project)
- [Dokumentasi API](#dokumentasi-api)
- [Kontribusi](#kontribusi)

---

## ✨ Fitur Utama

### 1. **Manajemen Produk**

- ✅ CRUD produk dengan kategori
- ✅ Tracking stok real-time
- ✅ Harga jual dan harga grosir (pack)
- ✅ SKU dan barcode support
- ✅ Status produk (aktif/non-aktif)

### 2. **Sistem Point of Sale (Kasir)**

- ✅ Interface kasir modern dan intuitif
- ✅ Search produk dengan filter real-time
- ✅ Shopping cart dengan edit/delete
- ✅ Diskon per item dan total
- ✅ Multiple payment methods (TUNAI, TRANSFER, CICILAN)
- ✅ Receipt printing
- ✅ Transaction history

### 3. **Manajemen Kredit & Cicilan**

- ✅ Kontrak kredit dengan terms
- ✅ Sistem cicilan pintar dengan pembulatan
- ✅ Jadwal angsuran otomatis
- ✅ Payment tracking dan due date management
- ✅ Trust score untuk pelanggan
- ✅ Credit limit auto-calculation
- ✅ **🆕 Screening Cicilan Pintar** - 3-tier validation berbasis trust score (REJECTED/MANUAL_REVIEW/APPROVED)

### 4. **Stock Conversion System (Buffer-Based)**

- ✅ **Partial stock conversion** tanpa decimal storage
- ✅ **Smart buffer management** - auto-open karton jika buffer kurang
- ✅ **INT-only calculations** - hanya gunakan integer
- ✅ **Complete audit trail** - track packs_used, dari_buffer, sisa_buffer_after
- ✅ **Mode PENUH & PARSIAL** - fleksibel sesuai kebutuhan
- ✅ **Undo/Reverse** - revert konversi dengan restoration penuh
- ✅ **Bulk operations** - proses banyak konversi sekaligus
- ✅ **Race condition safe** - DB transactions + pessimistic locking

### 5. **Dashboard & Reporting**

- ✅ Real-time sales dashboard
- ✅ Inventory analytics
- ✅ Revenue reports
- ✅ Customer insights

---

## 🛠️ Stack Teknologi

### Backend

- **PHP 8.3** dengan Laravel 12
- **MySQL 8.0** (Database)
- **Eloquent ORM** untuk database abstraction
- **Pest PHP** untuk testing

### Frontend

- **Vue 3** dengan Composition API
- **TypeScript** untuk type safety
- **Inertia.js** untuk server-side rendering
- **Tailwind CSS** untuk styling
- **Vite** untuk bundling

### Tools

- **Composer** untuk PHP dependencies
- **NPM/Bun** untuk JavaScript dependencies
- **Git** untuk version control

---

## 🚀 Instalasi

### Prerequisites

- PHP 8.3+
- MySQL 8.0+
- Node.js 18+ atau Bun
- Composer
- Git

### Step 1: Clone Repository

```bash
git clone https://github.com/AdenSahwaludin/SBSPointOfSale.git
cd pos-sbs
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

### Step 3: Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

### Step 4: Configure Database

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sbs
DB_USERNAME=root
DB_PASSWORD=
```

### Step 5: Run Migrations & Seeders

```bash
php artisan migrate
php artisan db:seed
```

### Step 6: Install JavaScript Dependencies

```bash
npm install
# atau
bun install
```

### Step 7: Build Frontend Assets

```bash
npm run build

# untuk development:
npm run dev
```

---

## ▶️ Menjalankan Aplikasi

### Development Server

```bash
# Terminal 1: PHP server
php artisan serve

# Terminal 2: Vite dev server
npm run dev
```

Akses: `http://localhost:8000`

### Production Build

```bash
npm run build
```

### Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/CreditScreeningTest.php

# With coverage
php artisan test --coverage
```

---

## 🎯 Fitur Stock Conversion

### Penjelasan Sistem Buffer

**Problem:** Partial conversions dari karton ke pcs bisa menghasilkan decimal numbers (mis: 10 pcs dari 144 pcs/karton = 0.069 karton). Ini tidak bisa disimpan di database INT.

**Solution:** Buffer management system berbasis INT.

### Konsep Buffer

```
Karton A: 10 karton (stok) + 30 pcs (sisa_pcs_terbuka/buffer)
           └─ 30 pcs ini dari karton yang sudah dibuka sebelumnya
```

### Alur Konversi Parsial

**Scenario:** Convert 100 pcs dari Karton A (buffer 30 pcs, isi 120 pcs)

```
BEFORE:
├─ Karton stok: 10
├─ Buffer: 30 pcs
└─ PCS target: 0

REQUEST: Convert 100 pcs (parsial)

LOGIC:
1. Cek buffer: 30 pcs ada
2. Butuh: 100 - 30 = 70 pcs lagi
3. Buka karton: ceil(70 / 120) = 1 karton
4. Total pcs: 30 + 120 = 150 pcs
5. Gunakan: 100 pcs
6. Sisa buffer: 150 - 100 = 50 pcs

AFTER:
├─ Karton stok: 9 (10 - 1)
├─ Buffer: 50 pcs
├─ PCS target: 100
└─ Audit:
   ├─ packs_used: 1
   ├─ dari_buffer: 30
   └─ sisa_buffer_after: 50
```

---

## 🆕 Screening Cicilan Pintar

### Penjelasan Fitur

**Screening Cicilan Pintar** adalah sistem automatisasi untuk mengevaluasi kelayakan transaksi kredit pelanggan berdasarkan trust score mereka. Sistem ini memiliki 3 tier yang jelas dengan validasi otomatis di frontend dan backend.

### 3-Tier Screening System

#### 1. **REJECTED** (Trust Score < 50) - ❌ Merah

**Status:** Pengajuan cicilan tidak diperbolehkan

**Kondisi:**

- Trust score pelanggan kurang dari 50
- Sistem secara otomatis menolak semua transaksi kredit

**UI Feedback:**

- ❌ Pesan error: "Pengajuan cicilan tidak diperbolehkan karena trust score terlalu rendah"
- 🔴 Badge merah (bg-red-100)
- 🚫 Tombol "Bayar" otomatis disable

**Validasi:**

- Frontend: Real-time banner warning
- Backend: 422 Unprocessable Entity response

#### 2. **MANUAL_REVIEW** (Trust Score 50-70) - ⚠️ Kuning

**Status:** Memerlukan peninjauan manual dengan DP minimal 20%

**Kondisi:**

- Trust score pelanggan antara 50-70
- Wajib ada Down Payment (DP) minimum 20% dari total belanja

**UI Feedback:**

- ⚠️ Pesan warning: "Trust score berada pada kategori menengah. Diperlukan DP minimal 20% dari total belanja untuk melanjutkan cicilan."
- 🟡 Badge kuning (bg-yellow-100)
- DP input field wajib diisi
- Info: "DP minimal 20% = Rp X"

**Validasi:**

- Frontend: Real-time validation saat DP berubah, tombol disable jika DP < 20%
- Backend: 422 jika DP < 20% dari total

#### 3. **APPROVED** (Trust Score ≥ 71) - ✅ Hijau

**Status:** Customer layak untuk proses cicilan normal

**Kondisi:**

- Trust score pelanggan 71 atau lebih
- Tidak ada persyaratan DP minimum tambahan

**UI Feedback:**

- ✅ Pesan sukses: "Customer layak untuk cicilan berdasarkan trust score. Apakah ingin melanjutkan proses cicilan sekarang?"
- 🟢 Badge hijau (bg-green-100)
- Tombol "Bayar" aktif normal

**Validasi:**

- Frontend: Banner info saja, no blocking
- Backend: Proceed normal credit validation

### Implementasi Frontend

**File:** `resources/js/pages/Kasir/POS/Index.vue`

Credit screening computed property menentukan status tier dan validasi realtime untuk DP 20%.

### Implementasi Backend

**File:** `app/Http/Controllers/Kasir/TransaksiPOSController.php`

Validasi screening dilakukan sebelum proses kredit dengan response 422 jika tidak lolos.

### Service: CreditLimitService

**File:** `app/Services/CreditLimitService.php`

Method `checkEligibility()` menentukan tier berdasarkan trust score:

- `< 50` → REJECTED
- `50-70` → MANUAL_REVIEW (DP min 20%)
- `≥ 71` → APPROVED

### Trust Score Display

Customer info modal menampilkan trust score dengan badge warna sesuai tier.

---

## 🧪 Testing

### Testing Status

✅ **Stock Conversion:** 6 tests (29 assertions) - PASSING  
✅ **Credit Limit Calculation:** 16 tests (31 assertions) - PASSING  
✅ **Trust Score:** 14 tests - PASSING  
✅ **Credit Screening (Cicilan Pintar):** 6 tests (21 assertions) - PASSING

**Total:** 42+ tests, 95+ assertions → ALL PASSING ✅

### Screening Tests

**File:** `tests/Feature/CreditScreeningTest.php`

```php
✓ Rejects credit transaction when trust_score < 50
✓ Rejects credit transaction when trust_score 50-70 without sufficient DP
✓ Accepts credit transaction when trust_score 50-70 with >= 20% DP
✓ Accepts credit transaction when trust_score >= 71
✓ Validates screening at trust_score boundary (49, 50, 70, 71)
✓ Allows manual review tier with exactly 20% DP
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run screening tests only
php artisan test tests/Feature/CreditScreeningTest.php

# Run with output
php artisan test --testdox

# Run with coverage
php artisan test --coverage
```

---

## 📁 Struktur Project

```
pos-sbs/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   ├── Kasir/
│   │   │   │   ├── TransaksiPOSController.php    ✏️ UPDATED
│   │   │   │   └── ...
│   │   │   └── ...
│   │   └── ...
│   ├── Models/
│   │   ├── Pelanggan.php
│   │   ├── Produk.php
│   │   ├── Transaksi.php
│   │   ├── KontrakKredit.php
│   │   └── ...
│   ├── Services/
│   │   ├── CreditLimitService.php          ✏️ UPDATED
│   │   ├── CreditSyncService.php
│   │   ├── TrustScoreService.php
│   │   ├── KonversiStokService.php
│   │   └── ...
│   └── ...
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── js/
│   │   ├── pages/
│   │   │   ├── Kasir/
│   │   │   │   ├── POS/
│   │   │   │   │   └── Index.vue          ✏️ UPDATED
│   │   │   │   └── ...
│   │   │   └── ...
│   │   ├── components/
│   │   ├── composables/
│   │   └── ...
│   └── ...
├── routes/
│   ├── web.php
│   ├── api.php
│   ├── admin.php
│   ├── kasir.php
│   └── auth.php
├── tests/
│   ├── Feature/
│   │   ├── CreditScreeningTest.php       ⭐ NEW
│   │   └── ...
│   ├── Unit/
│   │   └── ...
│   └── ...
├── .env
├── .env.example
├── composer.json
├── package.json
├── phpunit.xml
├── vite.config.ts
├── tsconfig.json
├── eslint.config.js
├── README.md
└── ...

Legend:
⭐ NEW    - File baru
✏️ UPDATED - File diubah
```

---

## 📊 Dokumentasi API

### POS Endpoints

#### 1. Create Transaction

```http
POST /kasir/pos
Content-Type: application/json

{
  "id_pelanggan": "P002",
  "items": [...],
  "metode_bayar": "KREDIT",
  "subtotal": 200000,
  "diskon": 0,
  "pajak": 10000,
  "total": 210000,
  "dp": 42000,
  "tenor_bulan": 12,
  "bunga_persen": 10,
  "cicilan_bulanan": 17500,
  "mulai_kontrak": "2025-02-22"
}

Response 200: Transaction success
Response 422: Screening REJECTED or MANUAL_REVIEW with DP < 20%
```

---

## 🤝 Kontribusi

### Development Workflow

1. Create feature branch: `git checkout -b feature/nama-fitur`
2. Make changes and test: `php artisan test`
3. Commit: `git commit -m "feat(module): description"`
4. Push: `git push origin feature/nama-fitur`

### Commit Convention

- `feat:` - New feature
- `fix:` - Bug fix
- `refactor:` - Code refactoring
- `test:` - Test additions
- `docs:` - Documentation
- `chore:` - Build/dependencies

---

## 📞 Dukungan

- **Issues:** [GitHub Issues](https://github.com/AdenSahwaludin/SBSPointOfSale/issues)
- **Documentation:** Lihat folder `/docs` untuk panduan lebih detail

---

## 📄 Lisensi

Proprietary - SBS Point of Sale System

---

## 👨‍💻 Author

**Aden Sahwaludin**  
Repository: [SBSPointOfSale](https://github.com/AdenSahwaludin/SBSPointOfSale)

---

**Terakhir diupdate:** 22 Februari 2026  
**Version:** 2.1.0 (Screening Cicilan Pintar Release)
