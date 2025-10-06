<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalAngsuran extends Model
{
    use HasFactory;

    protected $table = 'jadwal_angsuran';
    protected $primaryKey = 'id_angsuran';

    protected $fillable = [
        'id_kontrak',
        'periode_ke',
        'jatuh_tempo',
        'jumlah_tagihan',
        'jumlah_dibayar',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'id_kontrak' => 'integer',
        'periode_ke' => 'integer',
        'jatuh_tempo' => 'date',
        'jumlah_tagihan' => 'decimal:0',
        'jumlah_dibayar' => 'decimal:0',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function kontrakKredit(): BelongsTo
    {
        return $this->belongsTo(KontrakKredit::class, 'id_kontrak', 'id_kontrak');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'id_angsuran', 'id_angsuran');
    }

    /**
     * Scope: Angsuran yang jatuh tempo
     */
    public function scopeDue($query)
    {
        return $query->where('status', 'DUE');
    }

    /**
     * Scope: Angsuran yang telat
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'LATE');
    }

    /**
     * Scope: Angsuran yang sudah dibayar
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'PAID');
    }

    /**
     * Check if angsuran is overdue
     */
    public function isOverdue(): bool
    {
        return $this->jatuh_tempo < now() && $this->status !== 'PAID';
    }

    /**
     * Get sisa tagihan
     */
    public function getSisaTagihanAttribute(): float
    {
        return $this->jumlah_tagihan - $this->jumlah_dibayar;
    }

    /**
     * Check if fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->jumlah_dibayar >= $this->jumlah_tagihan;
    }
}