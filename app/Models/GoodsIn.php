<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsIn extends Model
{
    use HasFactory;

    protected $table = 'goods_ins';

    protected $primaryKey = 'id_goods_in';

    protected $fillable = [
        'nomor_po',
        'status',
        'tanggal_request',
        'tanggal_approval',
        'catatan_approval',
        'id_kasir',
        'id_admin',
    ];

    protected $casts = [
        'tanggal_request' => 'datetime',
        'tanggal_approval' => 'datetime',
    ];

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_kasir', 'id_pengguna');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_admin', 'id_pengguna');
    }

    public function details(): HasMany
    {
        return $this->hasMany(GoodsInDetail::class, 'id_goods_in');
    }

    public function receivedGoods(): HasMany
    {
        return $this->hasMany(GoodsReceived::class, 'id_goods_in');
    }

    public static function generateNomorPO(): string
    {
        $prefix = 'PO-'.now()->format('Y-m');
        $lastPO = static::where('nomor_po', 'like', "$prefix-%")
            ->latest('id_goods_in')
            ->first();

        $number = $lastPO ? (int) substr($lastPO->nomor_po, -5) + 1 : 1;

        return "$prefix-".str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
