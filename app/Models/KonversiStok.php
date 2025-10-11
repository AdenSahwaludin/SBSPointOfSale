<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KonversiStok extends Model
{
    use HasFactory;

    protected $table = 'konversi_stok';
    protected $primaryKey = 'id_konversi';

    protected $fillable = [
        'from_produk_id',
        'to_produk_id',
        'rasio',
        'qty_from',
        'qty_to',
        'keterangan',
    ];

    protected $casts = [
        'from_produk_id' => 'integer',
        'to_produk_id' => 'integer',
        'rasio' => 'integer',
        'qty_from' => 'integer',
        'qty_to' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the source product (karton)
     */
    public function fromProduk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'from_produk_id', 'id_produk');
    }

    /**
     * Get the target product (pcs)
     */
    public function toProduk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'to_produk_id', 'id_produk');
    }
}
