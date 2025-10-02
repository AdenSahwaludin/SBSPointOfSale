<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer_name',
        'customer_email', 
        'customer_phone',
        'amount',
        'status',
        'payment_type',
        'snap_token',
        'snap_redirect_url',
        'meta',
        'paid_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'paid_at' => 'datetime',
        'amount' => 'integer',
    ];

    public function paymentLogs()
    {
        return $this->hasMany(PaymentLog::class, 'order_id', 'order_id');
    }

    public function markAsPaid(string $paymentType = null): void
    {
        $this->update([
            'status' => 'paid',
            'payment_type' => $paymentType,
            'paid_at' => now(),
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
