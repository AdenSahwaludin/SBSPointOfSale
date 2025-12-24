<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KontrakKredit extends Model
{
    use HasFactory;

    protected $table = 'kontrak_kredit';

    protected $primaryKey = 'id_kontrak';

    protected $fillable = [
        'nomor_kontrak',
        'id_pelanggan',
        'nomor_transaksi',
        'mulai_kontrak',
        'tenor_bulan',
        'pokok_pinjaman',
        'dp',
        'bunga_persen',
        'cicilan_bulanan',
        'status',
        'score_snapshot',
    ];

    protected $casts = [
        'mulai_kontrak' => 'date',
        'tenor_bulan' => 'integer',
        'pokok_pinjaman' => 'decimal:2',
        'dp' => 'decimal:2',
        'bunga_persen' => 'decimal:2',
        'cicilan_bulanan' => 'decimal:2',
        'score_snapshot' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'nomor_transaksi', 'nomor_transaksi');
    }

    public function jadwalAngsuran(): HasMany
    {
        return $this->hasMany(JadwalAngsuran::class, 'id_kontrak', 'id_kontrak');
    }

    /**
     * Generate nomor kontrak baru
     */
    public static function generateNomorKontrak(): string
    {
        $today = Carbon::now();
        $yearMonth = $today->format('Ym');

        $lastKontrak = self::where('nomor_kontrak', 'like', "KRD-{$yearMonth}-%")
            ->orderBy('nomor_kontrak', 'desc')
            ->first();

        $sequence = 1;
        if ($lastKontrak) {
            $parts = explode('-', $lastKontrak->nomor_kontrak);
            if (count($parts) >= 3) {
                $sequence = (int) $parts[2] + 1;
            }
        }

        return sprintf('KRD-%s-%04d', $yearMonth, $sequence);
    }

    /**
     * Scope: Kontrak aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'AKTIF');
    }

    /**
     * Check if contract is active
     */
    public function isAktif(): bool
    {
        return $this->status === 'AKTIF';
    }

    /**
     * Get total yang sudah dibayar
     */
    public function getTotalDibayarAttribute(): float
    {
        return $this->jadwalAngsuran()->sum('jumlah_dibayar');
    }

    /**
     * Get sisa tagihan
     */
    public function getSisaTagihanAttribute(): float
    {
        return $this->jadwalAngsuran()->sum('jumlah_tagihan') - $this->total_dibayar;
    }
}
