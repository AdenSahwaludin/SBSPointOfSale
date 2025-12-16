<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename tables dari English ke Bahasa Indonesia
        // Urutan penting karena foreign key constraints
        // Skip jika table sudah memiliki nama Indonesia (fresh database from updated create migrations)

        // 1. Rename goods_ins ke pemesanan_barang (parent table)
        if (Schema::hasTable('goods_ins') && !Schema::hasTable('pemesanan_barang')) {
            Schema::rename('goods_ins', 'pemesanan_barang');
        }

        // 2. Rename goods_in_details ke detail_pemesanan_barang (child of goods_ins)
        if (Schema::hasTable('goods_in_details') && !Schema::hasTable('detail_pemesanan_barang')) {
            Schema::rename('goods_in_details', 'detail_pemesanan_barang');
        }

        // 3. Rename goods_received ke penerimaan_barang (child of goods_ins and goods_in_details)
        if (Schema::hasTable('goods_received') && !Schema::hasTable('penerimaan_barang')) {
            Schema::rename('goods_received', 'penerimaan_barang');
        }

        // 4. Rename stock_adjustments ke penyesuaian_stok (independent table)
        if (Schema::hasTable('stock_adjustments') && !Schema::hasTable('penyesuaian_stok')) {
            Schema::rename('stock_adjustments', 'penyesuaian_stok');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse rename - kembalikan ke nama asli
        // Skip jika table sudah memiliki nama English (tidak perlu di-rename back)
        
        if (Schema::hasTable('penyesuaian_stok') && !Schema::hasTable('stock_adjustments')) {
            Schema::rename('penyesuaian_stok', 'stock_adjustments');
        }
        
        if (Schema::hasTable('penerimaan_barang') && !Schema::hasTable('goods_received')) {
            Schema::rename('penerimaan_barang', 'goods_received');
        }
        
        if (Schema::hasTable('detail_pemesanan_barang') && !Schema::hasTable('goods_in_details')) {
            Schema::rename('detail_pemesanan_barang', 'goods_in_details');
        }
        
        if (Schema::hasTable('pemesanan_barang') && !Schema::hasTable('goods_ins')) {
            Schema::rename('pemesanan_barang', 'goods_ins');
        }
    }
};
