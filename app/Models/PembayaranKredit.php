<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembayaranKredit extends Model
{
 protected $table = 'pembayaran_kredit';
 protected $primaryKey = 'id';
 public $incrementing = true;
 protected $keyType = 'int';
 public $timestamps = true;

 protected $fillable = [
  'id_pelanggan',
  'jumlah_pembayaran',
  'metode_pembayaran',
  'keterangan',
  'tanggal_pembayaran',
  'id_kasir',
 ];

 protected $casts = [
  'jumlah_pembayaran' => 'decimal:2',
  'tanggal_pembayaran' => 'datetime',
  'created_at' => 'datetime',
  'updated_at' => 'datetime',
 ];

 /**
  * Relationship: Pembayaran belongs to Pelanggan
  */
 public function pelanggan(): BelongsTo
 {
  return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
 }

 /**
  * Relationship: Pembayaran belongs to Kasir (User)
  */
 public function kasir(): BelongsTo
 {
  return $this->belongsTo(User::class, 'id_kasir', 'id_pengguna');
 }
}
