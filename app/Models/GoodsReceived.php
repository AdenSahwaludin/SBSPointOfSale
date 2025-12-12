<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceived extends Model
{
    protected $table = 'goods_received';

    protected $primaryKey = 'id_goods_received';

    protected $fillable = [
        'id_goods_in',
        'id_goods_in_detail',
        'id_produk',
        'qty_received',
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
        return $this->belongsTo(GoodsIn::class, 'id_goods_in');
    }

    public function goodsInDetail(): BelongsTo
    {
        return $this->belongsTo(GoodsInDetail::class, 'id_goods_in_detail');
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
