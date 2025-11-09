# 🏪 SBS Point of Sale (POS) System# 🏪 SBS Point of Sale (POS) System



**Modern POS system untuk penjualan, inventory, kredit & cicilan dengan stock conversion buffer-based.****Sistem Point of Sale modern untuk manajemen penjualan, inventory, dan kredit dengan dukungan partial stock conversion.**



------



## ✨ Fitur## 📋 Daftar Isi



- **Manajemen Produk** - CRUD, SKU, tracking stok real-time- [Fitur Utama](#fitur-utama)

- **Sistem Kasir** - Shopping cart, diskon, multiple payment methods- [Stack Teknologi](#stack-teknologi)

- **Kredit & Cicilan** - Kontrak, jadwal angsuran, trust score auto-calculation- [Instalasi](#instalasi)

- **Stock Conversion** - Buffer-based system untuk partial conversions (INT-only)- [Konfigurasi Database](#konfigurasi-database)

- **Dashboard & Reports** - Real-time sales, inventory analytics- [Menjalankan Aplikasi](#menjalankan-aplikasi)

- [Fitur Stock Conversion](#-fitur-stock-conversion)

---- [API Documentation](#api-documentation)

- [Testing](#testing)

## 🛠️ Stack- [Struktur Project](#struktur-project)

- [Kontribusi](#kontribusi)

- **Backend:** Laravel 11, PHP 8.3, MySQL 8.0, Pest PHP

- **Frontend:** Vue 3, TypeScript, Inertia.js, Tailwind CSS, Vite---

- **Tools:** Composer, NPM/Bun, Git

## ✨ Fitur Utama

---

### 1. **Manajemen Produk**

## 🚀 Quick Start

- ✅ CRUD produk dengan kategori

### Prerequisites- ✅ Tracking stok real-time

- PHP 8.3+, MySQL 8.0+, Node.js 18+, Composer, Git- ✅ Harga jual dan harga grosir (pack)

- ✅ SKU dan barcode support

### Setup- ✅ Status produk (aktif/non-aktif)



```bash### 2. **Sistem Point of Sale (Kasir)**

git clone https://github.com/AdenSahwaludin/SBSPointOfSale.git

cd pos-sbs- ✅ Interface kasir modern dan intuitif

- ✅ Search produk dengan filter real-time

# Backend setup- ✅ Shopping cart dengan edit/delete

composer install- ✅ Diskon per item dan total

cp .env.example .env- ✅ Multiple payment methods (TUNAI, TRANSFER, CICILAN)

php artisan key:generate- ✅ Receipt printing

- ✅ Transaction history

# Database setup

# Edit .env with DB_CONNECTION=mysql, DB_DATABASE=sbs### 3. **Manajemen Kredit & Cicilan**

php artisan migrate

php artisan db:seed- ✅ Kontrak kredit dengan terms

- ✅ Sistem cicilan pintar dengan pembulatan

# Frontend setup- ✅ Jadwal angsuran otomatis

npm install- ✅ Payment tracking dan due date management

npm run build- ✅ Trust score untuk pelanggan

- ✅ Credit limit auto-calculation

# Run

php artisan serve          # Terminal 1### 4. **🆕 Stock Conversion System (Buffer-Based)**

npm run dev               # Terminal 2

# Access: http://localhost:8000- ✅ **Partial stock conversion** tanpa decimal storage

```- ✅ **Smart buffer management** - auto-open karton jika buffer kurang

- ✅ **INT-only calculations** - hanya gunakan integer

---- ✅ **Complete audit trail** - track packs_used, dari_buffer, sisa_buffer_after

- ✅ **Mode PENUH & PARSIAL** - fleksibel sesuai kebutuhan

## 📖 Usage- ✅ **Undo/Reverse** - revert konversi dengan restoration penuh

- ✅ **Bulk operations** - proses banyak konversi sekaligus

### Testing- ✅ **Race condition safe** - DB transactions + pessimistic locking



```bash### 5. **Dashboard & Reporting**

php artisan test

php artisan test --coverage- ✅ Real-time sales dashboard

```- ✅ Inventory analytics

- ✅ Revenue reports

### Stock Conversion Service- ✅ Customer insights



```php---

use App\Services\KonversiStokService;

## 🛠️ Stack Teknologi

$service = new KonversiStokService();

### Backend

// Convert 100 pcs (partial - uses buffer + opens boxes if needed)

$konversi = $service->convert(- **PHP 8.3** dengan Laravel 11

    fromProdukId: 1,- **MySQL 8.0** (Database)

    toProdukId: 2,- **Eloquent ORM** untuk database abstraction

    qtyTo: 100,- **Pest PHP** untuk testing

    mode: 'parsial',

    rasio: 120### Frontend

);

- **Vue 3** dengan Composition API

// Reverse conversion- **TypeScript** untuk type safety

$service->reverse($konversi->id_konversi);- **Inertia.js** untuk server-side rendering

```- **Tailwind CSS** untuk styling

- **Vite** untuk bundling

**Buffer Logic:**

- Keeps leftover PCS in buffer (`sisa_pcs_terbuka`) instead of creating decimals### Tools

- Auto-opens boxes when buffer insufficient

- Complete audit trail: `packs_used`, `dari_buffer`, `sisa_buffer_after`- **Composer** untuk PHP dependencies

- **NPM/Bun** untuk JavaScript dependencies

### Admin Endpoints- **Git** untuk version control



```---

GET    /admin/trust-score/{id}              - View trust score & credit limit

POST   /admin/trust-score/{id}/recalculate  - Recalculate single customer## 🚀 Instalasi

POST   /admin/trust-score/recalculate-all   - Batch recalculate all

```### Prerequisites



---- PHP 8.3+

- MySQL 8.0+

## 📁 Key Files- Node.js 18+ atau Bun

- Composer

```- Git

app/Services/

├── KonversiStokService.php      - Stock conversion logic### Step 1: Clone Repository

├── TrustScoreService.php        - Trust score calculation

└── CreditLimitService.php       - Credit limit auto-update```bash

git clone https://github.com/AdenSahwaludin/SBSPointOfSale.git

app/Console/Commands/cd pos-sbs

└── RecalculateTrustScores.php   - Batch recalculation command```



app/Events/Listeners/### Step 2: Install PHP Dependencies

├── PaymentReceived.php

└── UpdateTrustScoreOnPayment.php```bash

composer install

database/migrations/```

├── *create_*_table.php          - All schema in create tables (consolidated)

└── 2025_10_30_000000_add_indexes_to_pelanggan_table.php### Step 3: Setup Environment



tests/```bash

├── Unit/KonversiStokServiceTest.php  - 6 comprehensive testscp .env.example .env

└── Feature/CreditLimitCalculationTest.php - 16 credit testsphp artisan key:generate

``````



---### Step 4: Configure Database



## 🧪 Testing StatusEdit `.env`:



✅ **Stock Conversion:** 6 tests (29 assertions) - PASSING  ```env

✅ **Credit Limit:** 16 tests (31 assertions) - PASSING  DB_CONNECTION=mysql

✅ **Trust Score:** 14 tests - PASSINGDB_HOST=127.0.0.1

DB_PORT=3306

Run all: `php artisan test`DB_DATABASE=sbs

DB_USERNAME=root

---DB_PASSWORD=

```

## 📋 Git Workflow

### Step 5: Install JavaScript Dependencies

```bash

git checkout -b feature/nama-fitur```bash

# Make changes + testnpm install

php artisan test# atau

git add .bun install

git commit -m "feat(module): description"```

git push origin feature/nama-fitur

```### Step 6: Run Migrations & Seeders



**Commit format:** `feat:`, `fix:`, `refactor:`, `docs:`, `test:`, `chore:````bash

php artisan migrate

---php artisan db:seed

```

## 📄 License

### Step 7: Build Frontend Assets

Proprietary - SBS Point of Sale System

```bash

## 👨‍💻 Authornpm run build

# untuk development:

**Aden Sahwaludin** | [GitHub](https://github.com/AdenSahwaludin/SBSPointOfSale)npm run dev

```

---

---

*Updated: Nov 9, 2025 | Version: 2.0.0*

## 🗄️ Konfigurasi Database

### Setup MySQL Database

```sql
CREATE DATABASE sbs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Struktur Database Utama

#### Tabel `produk`

```sql
- id_produk (PK)
- sku (UNIQUE)
- nama
- satuan (pcs, karton, pack)
- isi_per_pack
- harga (decimal 18,0)
- stok (integer) - jumlah karton/pack
- sisa_pcs_terbuka (integer) - buffer PCS dari karton terbuka ⭐ NEW
- created_at, updated_at
```

#### Tabel `konversi_stok`

```sql
- id_konversi (PK)
- from_produk_id (FK)
- to_produk_id (FK)
- qty_from, qty_to
- rasio
- mode (enum: penuh, parsial)
- packs_used (integer) ⭐ NEW - audit: karton dibuka
- dari_buffer (integer) ⭐ NEW - audit: PCS dari buffer
- sisa_buffer_after (integer) ⭐ NEW - audit: buffer sisa
- created_at, updated_at
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
php artisan test tests/Unit/KonversiStokServiceTest.php

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

### API Endpoints

#### Kasir Routes

```
POST   /kasir/konversi-stok/store          - Create conversion
GET    /kasir/konversi-stok/{id}/destroy   - Delete (reverse) conversion
POST   /kasir/konversi-stok/bulkDelete     - Bulk delete conversions
```

#### Admin Routes

```
GET    /admin/konversi-stok                - List conversions
POST   /admin/konversi-stok                - Create
GET    /admin/konversi-stok/{id}/edit      - Edit form
PUT    /admin/konversi-stok/{id}           - Update
DELETE /admin/konversi-stok/{id}           - Delete (reverse)
POST   /admin/konversi-stok/bulkDelete     - Bulk delete
```

### Service Layer

**File:** `app/Services/KonversiStokService.php`

#### Method: `convert()`

```php
$konversi = $service->convert(
    fromProdukId: 1,
    toProdukId: 2,
    qtyTo: 100,           // PCS yang mau dikonversi
    mode: 'parsial',      // atau 'penuh'
    rasio: 120,           // isi_per_pack
    keterangan: 'desc'
);

// Return: KonversiStok model dengan audit data
// $konversi->packs_used        - karton dibuka
// $konversi->dari_buffer       - PCS dari buffer
// $konversi->sisa_buffer_after - buffer sisa
```

#### Method: `reverse()`

```php
$service->reverse($konversiId);
// Undo conversion, restore stok & buffer ke kondisi semula
```

#### Method: `bulkReverse()`

```php
$service->bulkReverse([$id1, $id2, $id3]);
// Undo multiple conversions dengan error handling
```

### Database Safety

- ✅ **Transactions:** Semua operasi dalam `DB::transaction()`
- ✅ **Locking:** `lockForUpdate()` pada produk untuk race condition prevention
- ✅ **Validation:** Cek stok, mode, qty, rasio
- ✅ **Exception Handling:** Custom exceptions untuk error cases

---

## 🧪 Testing

### Unit Tests untuk Stock Conversion

**File:** `tests/Unit/KonversiStokServiceTest.php`

Menjalankan 6 comprehensive tests:

```bash
✓ partial conversion uses buffer
✓ partial conversion auto opens box
✓ full conversion
✓ reverse conversion
✓ insufficient stock throws exception
✓ bulk reverse conversions

Tests: 6 passed (29 assertions)
```

### Manual Testing

```bash
php test-konversi.php
```

Output:

```
=== Test Konversi Stok dengan MySQL ===

Produk Karton: Minyak Gandarpura Cap Daun Karton 144 pcs (60 mL)
  - SKU: DA-GDP-KRT144
  - Stok: 8 karton
  - Buffer: 0 pcs
  - Isi per pack: 144 pcs

--- Test 1: Konversi 100 PCS (Parsial) ---
✓ Konversi berhasil!
  - Karton dipakai: 1
  - Dari buffer: 0 pcs
  - Buffer setelahnya: 44 pcs

Setelah konversi:
  - Karton stok: 7
  - Karton buffer: 44 pcs
  - PCS stok: 300 pcs

--- Test 2: Reverse (Undo) Konversi ---
✓ Reverse berhasil!
  - Stok kembali ke kondisi semula

=== SEMUA TEST BERHASIL! ===
```

---

## 📁 Struktur Project

```
pos-sbs/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── KonversiStokController.php    ⭐ NEW
│   │   │   │   └── ...
│   │   │   ├── Kasir/
│   │   │   │   ├── KonversiStokController.php    ⭐ NEW
│   │   │   │   └── ...
│   │   │   └── ...
│   │   └── ...
│   ├── Models/
│   │   ├── Produk.php                          ✏️ UPDATED
│   │   ├── KonversiStok.php                    ✏️ UPDATED
│   │   └── ...
│   ├── Services/
│   │   ├── KonversiStokService.php             ⭐ NEW (227 lines)
│   │   └── ...
│   └── ...
├── database/
│   ├── migrations/
│   │   ├── 2025_10_28_012152_add_sisa_pcs_terbuka_to_produk_table.php  ⭐ NEW
│   │   ├── 2025_10_28_012247_update_konversi_stok_table.php           ⭐ NEW
│   │   └── ...
│   ├── seeders/
│   │   ├── KategoriSeeder.php
│   │   ├── PenggunaSeeder.php
│   │   └── ...
│   └── ...
├── resources/
│   ├── js/
│   │   ├── pages/
│   │   │   ├── Admin/
│   │   │   │   ├── KonversiStok/
│   │   │   │   │   ├── Index.vue      ✏️ UPDATED
│   │   │   │   │   ├── Create.vue     ✏️ UPDATED
│   │   │   │   │   └── Edit.vue       ✏️ UPDATED
│   │   │   │   └── ...
│   │   │   ├── Kasir/
│   │   │   │   ├── KonversiStok/
│   │   │   │   │   ├── Index.vue      ✏️ UPDATED
│   │   │   │   │   └── Create.vue     ✏️ UPDATED
│   │   │   │   └── ...
│   │   │   └── ...
│   │   └── ...
│   └── ...
├── routes/
│   ├── admin.php                              ✏️ UPDATED
│   ├── kasir.php
│   └── ...
├── tests/
│   ├── Unit/
│   │   ├── KonversiStokServiceTest.php         ⭐ NEW (367 lines)
│   │   └── ExampleTest.php
│   ├── Feature/
│   │   └── ExampleTest.php
│   └── ...
├── test-konversi.php                          ⭐ NEW (Manual test script)
├── .env
├── .env.example
├── composer.json
├── package.json
├── phpunit.xml
├── vite.config.ts
└── README.md                                  ⭐ THIS FILE

Legend:
⭐ NEW    - File baru
✏️ UPDATED - File diubah
```

---

## 📊 Dokumentasi API

### Stock Conversion Endpoints

#### 1. Create Conversion (Kasir)

```http
POST /kasir/konversi-stok/store
Content-Type: application/json

{
  "from_produk_id": 1,
  "to_produk_id": 2,
  "qty_to": 100,
  "mode": "parsial",
  "rasio": 120,
  "keterangan": "Pembukaan stok mingguan"
}

Response 200:
{
  "success": true,
  "message": "Konversi stok (parsial) berhasil! 1 karton Minyak Karton → 100 pcs Minyak PCS",
  "data": {
    "id_konversi": 42,
    "from_produk_id": 1,
    "to_produk_id": 2,
    "qty_to": 100,
    "mode": "parsial",
    "packs_used": 1,
    "dari_buffer": 30,
    "sisa_buffer_after": 50,
    "created_at": "2025-10-31T10:30:00Z"
  }
}
```

#### 2. Reverse Conversion (Delete)

```http
DELETE /kasir/konversi-stok/42
```

#### 3. Bulk Delete

```http
POST /kasir/konversi-stok/bulkDelete
Content-Type: application/json

{
  "ids": [42, 43, 44]
}
```

---

## 🤝 Kontribusi

### Development Workflow

1. **Create Feature Branch**

    ```bash
    git checkout -b feature/nama-fitur
    ```

2. **Make Changes**

    ```bash
    # Edit files
    # Run tests
    php artisan test
    ```

3. **Commit dengan Conventional Commits**

    ```bash
    git add .
    git commit -m "feat(module): description

    Details about the change..."
    ```

4. **Push & Create Pull Request**
    ```bash
    git push origin feature/nama-fitur
    ```

### Commit Message Convention

- `feat:` - New feature
- `fix:` - Bug fix
- `refactor:` - Code refactoring
- `style:` - Styling changes
- `test:` - Test additions/updates
- `docs:` - Documentation
- `chore:` - Build/dependency updates

Contoh:

```
feat(stock-conversion): implement buffer-based partial conversion

- Add sisa_pcs_terbuka column to produk table
- Implement KonversiStokService with smart buffer logic
- Add comprehensive unit tests
```

---

## 📞 Dukungan

- **Issues:** [GitHub Issues](https://github.com/AdenSahwaludin/SBSPointOfSale/issues)
- **Email:** aden.sahwaludin@example.com
- **Documentation:** Lihat folder `/docs` untuk panduan lebih detail

---

## 📄 Lisensi

Proprietary - SBS Point of Sale System

---

## 👨‍💻 Author

**Aden Sahwaludin**  
Repository: [SBSPointOfSale](https://github.com/AdenSahwaludin/SBSPointOfSale)

---

**Terakhir diupdate:** 31 Oktober 2025  
**Version:** 2.0.0 (Stock Conversion Release)
