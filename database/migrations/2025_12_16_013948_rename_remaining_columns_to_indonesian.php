<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Rename remaining English field names to Indonesian for better understanding
     */
    public function up(): void
    {
        // Note: detail_pemesanan_barang and pemesanan_barang were already renamed in a previous migration
        // Note: penerimaan_barang IDs were already renamed in a previous migration

        // Clean up penerimaan_barang - remove old qty_damaged column (jumlah_rusak already exists)
        Schema::table('penerimaan_barang', function (Blueprint $table) {
            if (Schema::hasColumn('penerimaan_barang', 'qty_damaged')) {
                $table->dropColumn('qty_damaged');
            }
        });

        // Penyesuaian Stok (stock_adjustments) - rename primary key
        if (Schema::hasColumn('penyesuaian_stok', 'id_adjustment') && !Schema::hasColumn('penyesuaian_stok', 'id_penyesuaian')) {
            Schema::table('penyesuaian_stok', function (Blueprint $table) {
                $table->renameColumn('id_adjustment', 'id_penyesuaian');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse Penyesuaian Stok
        if (Schema::hasColumn('penyesuaian_stok', 'id_penyesuaian') && !Schema::hasColumn('penyesuaian_stok', 'id_adjustment')) {
            Schema::table('penyesuaian_stok', function (Blueprint $table) {
                $table->renameColumn('id_penyesuaian', 'id_adjustment');
            });
        }

        // Re-add qty_damaged column to penerimaan_barang (for backwards compatibility)
        Schema::table('penerimaan_barang', function (Blueprint $table) {
            if (!Schema::hasColumn('penerimaan_barang', 'qty_damaged')) {
                $table->integer('qty_damaged')->default(0)->after('jumlah_rusak');
            }
        });

        // Note: We don't reverse other columns because they were already renamed in previous migrations
    }
};
