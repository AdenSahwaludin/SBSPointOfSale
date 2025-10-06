<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiDetail extends Model
{
    use HasFactory;

    protected $table = 'transaksi_detail';
    protected $primaryKey = 'id_detail';

    protected $fillable = [
        'nomor_transaksi',
        'id_produk',
        'nama_produk',
        'harga_satuan',
        'jumlah',
        'mode_qty',
        'pack_size_snapshot',
        'diskon_item',
        'subtotal',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:0',
        'jumlah' => 'integer',
        'pack_size_snapshot' => 'integer',
        'diskon_item' => 'decimal:0',
        'subtotal' => 'decimal:0',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'nomor_transaksi', 'nomor_transaksi');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format((float)$this->subtotal, 0, ',', '.');
    }

    /**
     * Get unit description based on mode_qty
     */
    public function getUnitDescriptionAttribute(): string
    {
        if ($this->mode_qty === 'pack') {
            return $this->pack_size_snapshot . ' ' . ($this->produk->satuan ?? 'pcs') . '/pack';
        }
        return $this->produk->satuan ?? 'pcs';
    }
}
