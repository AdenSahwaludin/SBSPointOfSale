<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceived extends Model
{
    protected $table = 'penerimaan_barang';

    protected $primaryKey = 'id_penerimaan_barang';

    protected $fillable = [
        'id_pemesanan_barang',
        'id_detail_pemesanan_barang',
        'id_produk',
        'jumlah_diterima',
        'jumlah_rusak',
        'id_kasir',
        'catatan',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function goodsIn(): BelongsTo
    {
        return $this->belongsTo(GoodsIn::class, 'id_pemesanan_barang');
    }

    public function goodsInDetail(): BelongsTo
    {
        return $this->belongsTo(GoodsInDetail::class, 'id_detail_pemesanan_barang');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_kasir', 'id_pengguna');
    }
}
