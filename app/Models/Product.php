<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'id_produk';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_produk',
        'nama_produk',
        'deskripsi',
        'harga',
        'stok',
        'kategori',
        'barcode',
        'foto',
        'status',
    ];

    protected $casts = [
        'harga' => 'integer',
        'stok' => 'integer',
    ];

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class, 'id_produk', 'id_produk');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'aktif');
    }
}
