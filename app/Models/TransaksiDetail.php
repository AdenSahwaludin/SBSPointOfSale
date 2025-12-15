<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiDetail extends Model
{
    use HasFactory;

    protected $table = 'transaksi_detail';

    protected $primaryKey = 'id_detail';

    protected $fillable = [
        'nomor_transaksi',
        'id_produk',
        'nama_produk',
        'harga_satuan',
        'jumlah',
        'jenis_satuan',
        'isi_pack_saat_transaksi',
        'diskon_item',
        'subtotal',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:0',
        'jumlah' => 'integer',
        'isi_pack_saat_transaksi' => 'integer', // 1️⃣ Cast sebagai integer
        'diskon_item' => 'decimal:0',
        'subtotal' => 'decimal:0',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the transaction that owns the detail
     */
    public function transaksi(): BelongsTo
    {
        return $this->belongsTo(Transaksi::class, 'nomor_transaksi', 'nomor_transaksi');
    }

    /**
     * Get the product that owns the detail
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp '.number_format((float) $this->subtotal, 0, ',', '.');
    }

    /**
     * Get formatted harga satuan
     */
    public function getFormattedHargaSatuanAttribute(): string
    {
        return 'Rp '.number_format((float) $this->harga_satuan, 0, ',', '.');
    }

    /**
     * Get unit description based on jenis_satuan
     */
    public function getUnitDescriptionAttribute(): string
    {
        return $this->jenis_satuan === 'pack' ? 'pack' : 'unit';
    }
}
