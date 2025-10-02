<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pembayaran',
        'id_transaksi',
        'metode',
        'jumlah',
        'tanggal',
        'keterangan',
        'midtrans_transaction_id',
        'midtrans_status',
        'midtrans_payment_type',
        'midtrans_response',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'tanggal' => 'datetime',
        'midtrans_response' => 'array',
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'nomor_transaksi');
    }

    /**
     * Generate ID pembayaran baru
     */
    public static function generateIdPembayaran(): string
    {
        $today = Carbon::now()->format('Ymd');
        
        // Cari pembayaran terakhir hari ini
        $lastPayment = self::where('id_pembayaran', 'like', "PAY-{$today}-%")
            ->orderBy('id_pembayaran', 'desc')
            ->first();
        
        $sequence = 1;
        if ($lastPayment) {
            // Extract sequence dari ID terakhir
            $parts = explode('-', $lastPayment->id_pembayaran);
            if (count($parts) >= 3) {
                $sequence = (int) $parts[2] + 1;
            }
        }
        
        return sprintf('PAY-%s-%07d', $today, $sequence);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedJumlahAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->jumlah, 0, ',', '.');
    }
}
