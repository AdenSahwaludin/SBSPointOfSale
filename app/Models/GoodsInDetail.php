<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsInDetail extends Model
{
    use HasFactory;

    protected $table = 'detail_pemesanan_barang';

    protected $primaryKey = 'id_detail_pemesanan_barang';

    protected $fillable = [
        'id_pemesanan_barang',
        'id_produk',
        'jumlah_dipesan',
        'jumlah_diterima',
    ];

    public function goodsIn(): BelongsTo
    {
        return $this->belongsTo(GoodsIn::class, 'id_pemesanan_barang');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
}
