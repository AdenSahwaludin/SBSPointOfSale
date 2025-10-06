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
        'trust_score',
        'credit_limit',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'trust_score' => 'integer',
        'credit_limit' => 'decimal:0',
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
     * Get the credit contracts for the customer
     */
    public function kontrakKredit(): HasMany
    {
        return $this->hasMany(KontrakKredit::class, 'id_pelanggan', 'id_pelanggan');
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

        $lastNumber = (int)substr($lastCustomer->id_pelanggan, 1);
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
