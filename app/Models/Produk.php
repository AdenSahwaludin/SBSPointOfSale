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
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'sku',
        'barcode',
        'no_bpom',
        'nama',
        'id_kategori',
        'satuan',
        'isi_per_pack',
        'harga',
        'harga_pack',
        'stok',
    ];

    protected $casts = [
        'harga' => 'decimal:0',
        'harga_pack' => 'decimal:0',
        'stok' => 'integer',
        'isi_per_pack' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the category that owns the product
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    /**
     * Get the transaction details for the product
     */
    public function transaksiDetail(): HasMany
    {
        return $this->hasMany(TransaksiDetail::class, 'id_produk', 'id_produk');
    }

    /**
     * Get the stock conversions from this product (as source)
     */
    public function konversiFrom(): HasMany
    {
        return $this->hasMany(KonversiStok::class, 'from_produk_id', 'id_produk');
    }

    /**
     * Get the stock conversions to this product (as target)
     */
    public function konversiTo(): HasMany
    {
        return $this->hasMany(KonversiStok::class, 'to_produk_id', 'id_produk');
    }

    /**
     * Scope: Products with low stock
     */
    public function scopeLowStock($query)
    {
        return $query->where('stok', '<=', 10); // Assuming 10 is low stock threshold
    }

    /**
     * Scope: Products in stock
     */
    public function scopeInStock($query)
    {
        return $query->where('stok', '>', 0);
    }

    /**
     * Check if product has low stock
     */
    public function isLowStock(): bool
    {
        return $this->stok <= 10;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format((float)$this->harga, 0, ',', '.');
    }

    /**
     * Get stock equivalent in pieces
     */
    public function getStokSetaraPcsAttribute(): int
    {
        return $this->satuan === 'karton' ? $this->stok * $this->isi_per_pack : $this->stok;
    }
}
