<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pelanggan',
        'nama',
        'email',
        'telepon',
        'kota',
        'alamat',
        'aktif',
        'tanggal_daftar',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'tanggal_daftar' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the transactions for the customer
     */
    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class, 'id_pelanggan', 'id_pelanggan');
    }

    /**
     * Generate next customer ID
     */
    public static function generateNextId(): string
    {
        $lastCustomer = self::orderBy('id_pelanggan', 'desc')->first();
        
        if (!$lastCustomer) {
            return 'P001';
        }

        $lastNumber = (int) substr($lastCustomer->id_pelanggan, 1);
        $nextNumber = $lastNumber + 1;
        
        return 'P' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Scope: Active customers only
     */
    public function scopeActive($query)
    {
        return $query->where('aktif', true);
    }
}
