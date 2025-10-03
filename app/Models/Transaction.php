<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    protected $primaryKey = 'id_transaksi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_transaksi',
        'id_kasir',
        'nama_pelanggan',
        'nomor_hp_pelanggan',
        'total_item',
        'subtotal',
        'diskon',
        'pajak',
        'total',
        'metode_pembayaran',
        'bayar',
        'kembalian',
        'status',
        'midtrans_order_id',
        'midtrans_transaction_status',
        'midtrans_response',
    ];

    protected $casts = [
        'total_item' => 'integer',
        'subtotal' => 'integer',
        'diskon' => 'integer',
        'pajak' => 'integer',
        'total' => 'integer',
        'bayar' => 'integer',
        'kembalian' => 'integer',
        'midtrans_response' => 'array',
    ];

    public function kasir()
    {
        return $this->belongsTo(User::class, 'id_kasir', 'id_pengguna');
    }
}
