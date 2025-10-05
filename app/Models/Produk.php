<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_produk',
        'nama',
        'gambar',
        'nomor_bpom',
        'harga',
        'biaya_produk',
        'stok',
        'batas_stok',
        'satuan',
        'satuan_per_pack',
        'isi_per_pack',
        'harga_per_pack',
        'min_beli_diskon',
        'harga_diskon_per_unit',
        'harga_diskon_per_pack',
        'id_kategori',
    ];

    protected $casts = [
        'harga' => 'decimal:0',
        'biaya_produk' => 'decimal:0',
        'stok' => 'integer',
        'batas_stok' => 'integer',
        'isi_per_pack' => 'integer',
        'harga_per_pack' => 'decimal:0',
        'min_beli_diskon' => 'integer',
        'harga_diskon_per_unit' => 'decimal:0',
        'harga_diskon_per_pack' => 'decimal:0',
        'id_kategori' => 'integer',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    public function transaksiDetail(): HasMany
    {
        return $this->hasMany(TransaksiDetail::class, 'id_produk', 'id_produk');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stok', '<=', 'batas_stok');
    }

    public function scopeInStock($query)
    {
        return $query->where('stok', '>', 0);
    }

    public function isLowStock(): bool
    {
        return $this->stok <= $this->batas_stok;
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format((float)$this->harga, 0, ',', '.');
    }
}
