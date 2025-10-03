<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    protected $primaryKey = 'nomor_transaksi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nomor_transaksi',
        'id_pelanggan',
        'id_kasir',
        'tanggal',
        'total_item',
        'subtotal',
        'diskon',
        'pajak',
        'biaya_pengiriman',
        'total',
        'metode_bayar',
        'status_pembayaran',
        'paid_at',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_status',
        'midtrans_payment_type',
        'midtrans_va_numbers',
        'midtrans_gross_amount',
        'midtrans_response',
        'is_locked',
        'locked_at',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'total_item' => 'integer',
        'subtotal' => 'decimal:2',
        'diskon' => 'decimal:2',
        'pajak' => 'decimal:2',
        'biaya_pengiriman' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'midtrans_va_numbers' => 'array',
        'midtrans_gross_amount' => 'decimal:2',
        'midtrans_response' => 'array',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_kasir', 'id_pengguna');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(TransaksiDetail::class, 'nomor_transaksi', 'nomor_transaksi');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'id_transaksi', 'nomor_transaksi');
    }

    /**
     * Generate nomor transaksi baru
     */
    public static function generateNomorTransaksi(string $idPelanggan): string
    {
        $today = Carbon::now();
        $year = $today->format('Y');
        $month = $today->format('m');

        // Cari transaksi terakhir hari ini
        $lastTransaction = self::where('nomor_transaksi', 'like', "INV-{$year}-{$month}-%")
            ->orderBy('nomor_transaksi', 'desc')
            ->first();

        $sequence = 1;
        if ($lastTransaction) {
            // Extract sequence dari nomor transaksi terakhir
            $parts = explode('-', $lastTransaction->nomor_transaksi);
            if (count($parts) >= 4) {
                $sequence = (int)$parts[3] + 1;
            }
        }

        return sprintf('INV-%s-%s-%03d-%s', $year, $month, $sequence, $idPelanggan);
    }

    /**
     * Scope: Transaksi hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('tanggal', Carbon::today());
    }

    /**
     * Scope: Transaksi yang sudah dibayar
     */
    public function scopePaid($query)
    {
        return $query->where('status_pembayaran', 'PAID');
    }

    /**
     * Check if transaction is paid
     */
    public function isPaid(): bool
    {
        return $this->status_pembayaran === 'PAID';
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format((float)$this->total, 0, ',', '.');
    }
}
