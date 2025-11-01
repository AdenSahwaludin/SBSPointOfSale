# 🏪 SBS Point of Sale (POS) System

**Sistem Point of Sale modern untuk manajemen penjualan, inventory, dan kredit dengan dukungan partial stock conversion.**

---

## 📋 Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Stack Teknologi](#stack-teknologi)
- [Instalasi](#instalasi)
- [Konfigurasi Database](#konfigurasi-database)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Fitur Stock Conversion](#-fitur-stock-conversion)
- [API Documentation](#api-documentation)
- [Testing](#testing)
- [Struktur Project](#struktur-project)
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

### 4. **🆕 Stock Conversion System (Buffer-Based)**
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
- **PHP 8.3** dengan Laravel 11
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

### Step 5: Install JavaScript Dependencies
```bash
npm install
# atau
bun install
```

### Step 6: Run Migrations & Seeders
```bash
php artisan migrate
php artisan db:seed
```

### Step 7: Build Frontend Assets
```bash
npm run build
# untuk development:
npm run dev
```

---

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
