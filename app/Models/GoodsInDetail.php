<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsInDetail extends Model
{
    use HasFactory;

    protected $table = 'goods_in_details';

    protected $primaryKey = 'id_goods_in_detail';

    protected $fillable = [
        'id_goods_in',
        'id_produk',
        'qty_request',
        'qty_received',
    ];

    public function goodsIn(): BelongsTo
    {
        return $this->belongsTo(GoodsIn::class, 'id_goods_in');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
}
