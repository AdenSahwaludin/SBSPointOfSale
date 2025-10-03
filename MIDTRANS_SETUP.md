# Konfigurasi Midtrans untuk POS System

## Setup Midtrans

1. **Daftar/Login ke Midtrans Dashboard**
    - Sandbox: https://dashboard.sandbox.midtrans.com/
    - Production: https://dashboard.midtrans.com/

2. **Dapatkan API Keys**
    - Server Key: Untuk backend/server-side
    - Client Key: Untuk frontend/client-side

3. **Update file .env**

    ```env
    # Midtrans Configuration
    MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxx
    MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxxx
    MIDTRANS_IS_PRODUCTION=false
    ```

4. **Webhook Configuration di Midtrans Dashboard**
    - Payment Notification URL: `https://your-domain.com/midtrans/callback`
    - HTTP Method: POST
    - Enable semua notification types

## Testing Pembayaran

### Sandbox Test Cards (untuk testing):

**Credit Card:**

- Card Number: 4811 1111 1111 1114
- CVV: 123
- Expiry: 01/25

**Virtual Account:**

- BCA: 12345678
- BNI: 1234567890123456
- BRI: 1234567890123456

**E-Wallet:**

- GoPay: Akan redirect ke simulator
- DANA: Akan redirect ke simulator
- OVO: Akan redirect ke simulator

## Payment Flow

1. **Pembayaran Tunai:**
    - Input jumlah bayar
    - Sistem hitung kembalian
    - Transaksi langsung PAID

2. **Pembayaran Non-Tunai (Midtrans):**
    - Sistem generate Snap Token
    - Pop-up Midtrans muncul
    - Customer pilih metode bayar
    - Webhook callback update status

## Status Pembayaran

- **PENDING**: Menunggu pembayaran
- **PAID**: Pembayaran berhasil
- **FAILED**: Pembayaran gagal
- **VOID**: Transaksi dibatalkan

## Troubleshooting

1. **Snap popup tidak muncul:**
    - Cek MIDTRANS_CLIENT_KEY di .env
    - Cek koneksi internet
    - Cek browser console untuk error

2. **Callback tidak diterima:**
    - Cek MIDTRANS_SERVER_KEY di .env
    - Cek URL webhook di dashboard
    - Cek signature validation

3. **Payment status tidak update:**
    - Cek webhook URL accessible
    - Cek database connection
    - Cek log file untuk error
