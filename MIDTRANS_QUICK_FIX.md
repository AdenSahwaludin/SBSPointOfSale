# 🔧 MIDTRANS TESTING SETUP

## Quick Fix untuk Error "Access denied due to unauthorized transaction"

### 1. Update .env dengan Demo Credentials
```env
# Midtrans Configuration (Demo Sandbox Keys)
MIDTRANS_SERVER_KEY=SB-Mid-server-GwS6LGS-PTDuNpXSNIbNxNp
MIDTRANS_CLIENT_KEY=SB-Mid-client-_YPSKi_WK_g6n0VA
MIDTRANS_IS_PRODUCTION=false
VITE_MIDTRANS_CLIENT_KEY="${MIDTRANS_CLIENT_KEY}"
```

### 2. Restart Development Server
```bash
# Clear cache
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Restart server
npm run dev
php artisan serve
```

### 3. Dapatkan Credentials Anda Sendiri
1. Daftar di https://account.midtrans.com
2. Login ke Dashboard
3. Settings → Access Keys
4. Copy Sandbox Server Key & Client Key
5. Replace di .env

### 4. Jika Masih Error 401:
- Pastikan format key benar (harus ada prefix SB-Mid-server- dan SB-Mid-client-)
- Pastikan tidak ada spasi di awal/akhir key
- Coba regenerate key di Midtrans dashboard

### 5. Test Payment Flow:
1. Buka POS System
2. Tambah produk ke cart  
3. Checkout → pilih Digital Payment
4. Snap popup akan muncul
5. Pilih QRIS/E-Wallet untuk test

## ⚠️ Troubleshooting:

### Error "Failed to create payment":
- ✅ Check MIDTRANS_SERVER_KEY di .env
- ✅ Restart Laravel server after .env changes
- ✅ Check Laravel logs: `storage/logs/laravel.log`

### Error "CSRF token not found":
- ✅ Reload halaman POS 
- ✅ Check meta tag csrf-token ada di head

### Error "Network error":
- ✅ Check Laravel server running di port 8000
- ✅ Check network connectivity