# 📁 STRUKTUR ROUTE ORGANIZANTION

Sistem route telah dibagi menjadi 4 file terpisah untuk kemudahan pengelolaan:

## 📂 File Route Structure

```
routes/
├── web.php     → Route utama & public routes
├── auth.php    → Authentication & profile routes
├── admin.php   → Admin-only routes
└── kasir.php   → Kasir-only routes
```

---

## 🔗 **1. web.php** - Route Utama

**Fungsi:** Root redirect dan include file route lainnya

### Routes:

- `GET /` → Root redirect berdasarkan role user
- Include semua file route lainnya

---

## 🔐 **2. auth.php** - Authentication Routes

**Fungsi:** Login, logout, dan profile management untuk semua user

### Routes:

- **Guest Routes:**
    - `GET /login` → Halaman login
    - `POST /login` → Proses login

- **Authenticated Routes:**
    - `GET /auth/profile` → Lihat profile
    - `PATCH /auth/profile` → Update profile
    - `PATCH /auth/profile/password` → Update password
    - `POST|DELETE /logout` → Logout

---

## 👨‍💼 **3. admin.php** - Admin Routes

**Middleware:** `auth` + `role:admin`  
**Prefix:** `/admin`  
**Name Prefix:** `admin.`

### Dashboard:

- `GET /admin` → `admin.dashboard`

### Manajemen Pengguna:

- `GET /admin/pengguna` → `admin.pengguna.index`
- `GET /admin/pengguna/create` → `admin.pengguna.create`
- `POST /admin/pengguna` → `admin.pengguna.store`
- `GET /admin/pengguna/{id}` → `admin.pengguna.show`
- `GET /admin/pengguna/{id}/edit` → `admin.pengguna.edit`
- `PUT /admin/pengguna/{id}` → `admin.pengguna.update`
- `DELETE /admin/pengguna/{id}` → `admin.pengguna.destroy`
- `POST /admin/pengguna/{id}/reset-password` → `admin.pengguna.reset-password`

### Manajemen Produk:

- `GET /admin/produk` → `admin.produk.index`
- `GET /admin/produk/create` → `admin.produk.create`
- `POST /admin/produk` → `admin.produk.store`
- `GET /admin/produk/{id}` → `admin.produk.show`
- `GET /admin/produk/{id}/edit` → `admin.produk.edit`
- `PUT /admin/produk/{id}` → `admin.produk.update`
- `DELETE /admin/produk/{id}` → `admin.produk.destroy`
- `POST /admin/produk/bulk-action` → `admin.produk.bulk-action`

### Quick Access:

- `GET /admin/users` → `admin.users`
- `GET /admin/products` → `admin.products`
- `GET /admin/reports` → `admin.reports`
- `GET /admin/settings` → `admin.settings`

---

## 🛒 **4. kasir.php** - Kasir Routes

**Middleware:** `auth` + `role:kasir`  
**Prefix:** `/kasir`  
**Name Prefix:** `kasir.`

### Dashboard:

- `GET /kasir` → `kasir.dashboard`

### Point of Sale (POS):

- `GET /kasir/pos` → `kasir.pos.index`
- `POST /kasir/pos` → `kasir.pos.store`
- `GET /kasir/pos/search-produk` → `kasir.pos.search-produk`
- `GET /kasir/pos/produk/{barcode}` → `kasir.pos.produk`
- `GET /kasir/pos/receipt/{nomorTransaksi}` → `kasir.pos.receipt`
- `GET /kasir/pos/today-transactions` → `kasir.pos.today`
- `POST /kasir/pos/cancel/{nomorTransaksi}` → `kasir.pos.cancel`

### Transaction History:

- `GET /kasir/transactions` → `kasir.transactions`

### Profile (Kasir specific):

- `GET /kasir/profile` → `kasir.profile.show`
- `PATCH /kasir/profile` → `kasir.profile.update`
- `PATCH /kasir/profile/password` → `kasir.profile.password`

---

## 🔧 **Cara Menambah Route Baru**

### Untuk Admin:

1. Edit file `routes/admin.php`
2. Tambahkan route di dalam group admin
3. Route otomatis dapat prefix `/admin` dan name prefix `admin.`

### Untuk Kasir:

1. Edit file `routes/kasir.php`
2. Tambahkan route di dalam group kasir
3. Route otomatis dapat prefix `/kasir` dan name prefix `kasir.`

### Untuk Authentication:

1. Edit file `routes/auth.php`
2. Pilih grup middleware yang sesuai (guest/auth)

### Untuk Public Routes:

1. Edit file `routes/web.php`
2. Tambahkan sebelum include file lain

---

## 🎯 **Keuntungan Struktur Ini**

1. **Organized** - Route dikelompokkan berdasarkan fungsi dan role
2. **Maintainable** - Mudah menemukan dan mengedit route spesifik
3. **Scalable** - Mudah menambah fitur baru tanpa mengacaukan struktur
4. **Secure** - Middleware role sudah diterapkan per group
5. **Clear** - Nama route yang konsisten dan jelas

---

## ⚠️ **Breaking Changes**

Dengan perubahan struktur ini, tidak ada breaking changes karena:

- URL tetap sama (`/admin/produk`, `/kasir/pos`, dll)
- Route names tetap sama (`admin.produk.index`, `kasir.pos.index`, dll)
- Middleware protection tetap aktif
- Frontend tidak perlu perubahan

---

## 🚀 **Testing Commands**

```bash
# Lihat semua route
php artisan route:list

# Lihat route admin saja
php artisan route:list --name=admin

# Lihat route kasir saja
php artisan route:list --name=kasir

# Lihat route auth saja
php artisan route:list --name=auth
```
