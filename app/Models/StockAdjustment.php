<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    protected $table = 'stock_adjustments';

    protected $primaryKey = 'id_adjustment';

    protected $fillable = [
        'id_produk',
        'tipe',
        'qty_adjustment',
        'alasan',
        'id_pengguna',
        'tanggal_adjustment',
    ];

    protected $casts = [
        'tanggal_adjustment' => 'datetime',
    ];

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_pengguna', 'id_pengguna');
    }
}
