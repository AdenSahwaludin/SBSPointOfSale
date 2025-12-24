<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'id_angsuran',
        'id_pelanggan',
        'id_kasir',
        'metode',
        'tipe_pembayaran',
        'jumlah',
        'tanggal',
        'keterangan',
    ];

    protected $casts = [
        'id_angsuran' => 'integer',
        'jumlah' => 'decimal:2',
        'tanggal' => 'datetime',
        'tipe_pembayaran' => 'string',
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'nomor_transaksi');
    }

    public function jadwalAngsuran(): BelongsTo
    {
        return $this->belongsTo(JadwalAngsuran::class, 'id_angsuran', 'id_angsuran');
    }

    /**
     * Relationship: Pembayaran belongs to Pelanggan (for kredit type)
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    /**
     * Relationship: Pembayaran belongs to Kasir/User (for kredit type)
     */
    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_kasir', 'id_pengguna');
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
        return 'Rp '.number_format((float) $this->jumlah, 0, ',', '.');
    }
}
