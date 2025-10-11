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

    public const STATUS_MENUNGGU = 'MENUNGGU';
    public const STATUS_LUNAS = 'LUNAS';
    public const STATUS_BATAL = 'BATAL';

    public const JENIS_TUNAI = 'TUNAI';
    public const JENIS_KREDIT = 'KREDIT';

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
        'jenis_transaksi',
        'dp',
        'tenor_bulan',
        'bunga_persen',
        'cicilan_bulanan',
        'ar_status',
        'id_kontrak',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'total_item' => 'integer',
        'subtotal' => 'decimal:0',
        'diskon' => 'decimal:0',
        'pajak' => 'decimal:0',
        'biaya_pengiriman' => 'decimal:0',
        'total' => 'decimal:0',
        'dp' => 'decimal:0',
        'tenor_bulan' => 'integer',
        'bunga_persen' => 'decimal:2',
        'cicilan_bulanan' => 'decimal:0',
        'paid_at' => 'datetime',
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

    public function kontrakKredit(): BelongsTo
    {
        return $this->belongsTo(KontrakKredit::class, 'id_kontrak', 'id_kontrak');
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
        return $query->where('status_pembayaran', self::STATUS_LUNAS);
    }

    /**
     * Scope: Transaksi kredit
     */
    public function scopeKredit($query)
    {
        return $query->where('jenis_transaksi', self::JENIS_KREDIT);
    }

    /**
     * Check if transaction is paid
     */
    public function isPaid(): bool
    {
        return $this->status_pembayaran === self::STATUS_LUNAS;
    }

    /**
     * Check if transaction is credit
     */
    public function isKredit(): bool
    {
        return $this->jenis_transaksi === self::JENIS_KREDIT;
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format((float)$this->total, 0, ',', '.');
    }
}
