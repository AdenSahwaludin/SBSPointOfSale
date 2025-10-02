# 🏆 **MIDTRANS INTEGRATION - SARI BUMI SAKTI POS**

## 📋 **Status Mapping Midtrans → Order Status**

```php
// Transaction Status → Order Status Mapping
'capture' + fraud_status 'accept' → 'paid'
'capture' + fraud_status 'pending' → 'pending'  
'settlement' → 'paid' ✅
'pending' → 'pending' ⏳
'expire' → 'failed' ❌
'cancel' → 'failed' ❌ 
'deny' → 'failed' ❌
```

## 🔧 **Environment Setup**

### Sandbox (Development)
```env
MIDTRANS_SERVER_KEY=SB-Mid-server-your-sandbox-server-key-here
MIDTRANS_CLIENT_KEY=SB-Mid-client-your-sandbox-client-key-here
MIDTRANS_IS_PRODUCTION=false
VITE_MIDTRANS_CLIENT_KEY="${MIDTRANS_CLIENT_KEY}"
```

### Production
```env
MIDTRANS_SERVER_KEY=Mid-server-your-production-server-key-here
MIDTRANS_CLIENT_KEY=Mid-client-your-production-client-key-here
MIDTRANS_IS_PRODUCTION=true
VITE_MIDTRANS_CLIENT_KEY="${MIDTRANS_CLIENT_KEY}"
```

## 🚀 **API Endpoints**

### 1. Create Payment
```
POST /api/payments/create
Rate Limit: 30 requests/minute

Body:
{
  "amount": 50000,
  "customer": {
    "name": "John Doe",
    "email": "john@example.com", 
    "phone": "08123456789"
  },
  "items": [
    {
      "id": "P001",
      "price": 25000,
      "quantity": 2,
      "name": "Product Name"
    }
  ]
}

Response:
{
  "success": true,
  "data": {
    "order_id": "SBS-20251002-A1B2C3D4",
    "snap_token": "snap_token_string",
    "redirect_url": "https://app.sandbox.midtrans.com/snap/v2/vtweb/..."
  }
}
```

### 2. Payment Notification (Webhook)
```
POST /api/payments/notification
Content-Type: application/json

Midtrans sends notification automatically
→ Verifies signature
→ Updates order status  
→ Logs payment activity
```

## 🎯 **Frontend Integration**

### Snap.js Usage
```typescript
import { useMidtrans } from '@/composables/useMidtrans';

const { createPayment, isLoading } = useMidtrans();

await createPayment({
  amount: 50000,
  customer: { name: 'John', email: 'john@test.com', phone: '08123456789' },
  items: [{ id: 'P001', price: 25000, quantity: 2, name: 'Product' }]
}, {
  onSuccess: (result) => console.log('Payment success:', result),
  onPending: (result) => console.log('Payment pending:', result),
  onError: (result) => console.log('Payment error:', result),
  onClose: () => console.log('Payment popup closed')
});
```

## 📱 **Payment Channels Enabled**

### Priority Channels:
- **QRIS** (Scan to Pay)
- **E-Wallets**: GoPay, ShopeePay, DANA, LinkAja, OVO
- **Virtual Account**: BCA, BNI, BRI, Permata, Other Banks

### Optional:
- Credit Card (disabled by default, can be enabled)

## 🔒 **Security Features**

### ✅ Implemented:
- ✅ Signature verification untuk webhook notifications
- ✅ Rate limiting (30 requests/minute) untuk create payment
- ✅ Order ID uniqueness validation
- ✅ Amount validation (> 0)
- ✅ Environment separation (Sandbox/Production)
- ✅ Payment logs untuk audit trail
- ✅ Idempotent notification handling

### ⚠️ Server Key Protection:
- Server Key TIDAK dikirim ke frontend
- Hanya Client Key yang dikirim via `data-client-key`
- API endpoints protected dengan proper validation

## 🏃‍♂️ **Production Migration**

### Step 1: Update Environment
```bash
# Change in .env
MIDTRANS_IS_PRODUCTION=true
MIDTRANS_SERVER_KEY=Mid-server-your-production-key  
MIDTRANS_CLIENT_KEY=Mid-client-your-production-key
```

### Step 2: Update Midtrans Dashboard
```
1. Login ke https://dashboard.midtrans.com
2. Settings → Configuration  
3. Set Notification URL: https://yourdomain.com/api/payments/notification
4. Set Finish URL: https://yourdomain.com/payment/finish
5. Enable required payment methods
```

### Step 3: Update Snap.js Script
Frontend composable akan otomatis menggunakan production URL berdasarkan environment.

## 🧪 **Acceptance Criteria Testing**

### ✅ AC1: Create order returns valid snap_token
```bash
curl -X POST http://localhost:8000/api/payments/create \
  -H "Content-Type: application/json" \
  -d '{"amount":50000,"customer":{"name":"Test","email":"test@test.com","phone":"08123456789"}}'
```

### ✅ AC2: QRIS payment success → status "paid (settlement)"
1. Create payment via API
2. Use snap_token di Snap popup  
3. Choose QRIS payment
4. Scan with test QRIS app
5. Verify order status = 'paid'

### ✅ AC3: Payment closed before completion → status remains "pending"
1. Create payment
2. Open Snap popup
3. Close popup tanpa bayar
4. Verify order status = 'pending'

### ✅ AC4: Invalid signature → 403, no status change
```bash
curl -X POST http://localhost:8000/api/payments/notification \
  -H "Content-Type: application/json" \
  -d '{"order_id":"test","signature_key":"invalid"}' 
# Should return 403
```

### ✅ AC5: Retry notification idempotent
Send same notification multiple times → status updated once safely.

## 📊 **Database Schema**

### orders table:
```sql
id, order_id (unique), customer_name, customer_email, 
customer_phone, amount, status, payment_type, 
snap_token, snap_redirect_url, meta (json), 
paid_at, created_at, updated_at
```

### payment_logs table:
```sql  
id, order_id, raw_payload (json), transaction_status,
payment_type, created_at, updated_at
```

## 🎮 **Usage dalam POS System**

1. **Kasir memilih produk** → masukkan ke cart
2. **Klik "Checkout"** → input data pelanggan  
3. **Pilih "Digital Payment (Midtrans)"** → klik Bayar Digital
4. **Snap popup muncul** → pelanggan pilih metode pembayaran
5. **Selesai bayar** → status otomatis terupdate → receipt generated

## 📞 **Support & Troubleshooting**

### Common Issues:
- **Snap.js not loading**: Check client key di .env dan network
- **Invalid signature**: Verify server key configuration
- **Payment stuck pending**: Check Midtrans dashboard untuk notification logs
- **CORS issues**: Pastikan domain terdaftar di Midtrans dashboard

### Logs Location:
- Laravel logs: `storage/logs/laravel.log`
- Payment logs: `payment_logs` table
- Midtrans dashboard: Transaction & Notification logs