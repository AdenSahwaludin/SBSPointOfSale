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
        'id_produk', 'nama', 'gambar', 'nomor_bpom', 'harga', 'biaya_produk',
        'stok', 'batas_stok', 'unit', 'pack_unit', 'pack_size', 'harga_pack',
        'qty_tier1', 'harga_tier1', 'harga_tier_qty', 'harga_tier_pack', 'id_kategori',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'biaya_produk' => 'decimal:2',
        'stok' => 'integer',
        'batas_stok' => 'integer',
        'pack_size' => 'integer',
        'harga_pack' => 'decimal:2',
        'qty_tier1' => 'integer',
        'harga_tier1' => 'decimal:2',
        'harga_tier_qty' => 'integer',
        'harga_tier_pack' => 'decimal:2',
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
        return 'Rp ' . number_format((float) $this->harga, 0, ',', '.');
    }
}space App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    //
}
