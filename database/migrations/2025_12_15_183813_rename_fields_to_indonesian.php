<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Mengubah penamaan field ke Bahasa Indonesia yang mudah dipahami pengguna toko:
     * 1. Tabel produk: ROP & ROQ menjadi batas_stok_minimum & jumlah_restock
     * 2. Tabel konversi_stok: dari_buffer & sisa_buffer_after menjadi stok_awal_pcs & stok_sisa_pcs
     * 3. Tabel detail_pemesanan_barang: qty_request & qty_received menjadi jumlah_dipesan & jumlah_diterima
     * 4. Tabel penerimaan_barang: qty_received menjadi jumlah_diterima
     * 5. Tabel penyesuaian_stok: qty_adjustment menjadi jumlah_penyesuaian
     * 6. Tabel transaksi_detail: mode_qty tetap (karena teknis: unit/pack)
     */
    public function up(): void
    {
        // 1. Tabel produk: ROP & ROQ
        if (Schema::hasColumn('produk', 'rop') && !Schema::hasColumn('produk', 'batas_stok_minimum')) {
            Schema::table('produk', function (Blueprint $table) {
                $table->renameColumn('rop', 'batas_stok_minimum');
            });
        }
        if (Schema::hasColumn('produk', 'roq') && !Schema::hasColumn('produk', 'jumlah_restock')) {
            Schema::table('produk', function (Blueprint $table) {
                $table->renameColumn('roq', 'jumlah_restock');
            });
        }

        // 2. Tabel konversi_stok: dari_buffer & sisa_buffer_after
        if (Schema::hasColumn('konversi_stok', 'dari_buffer') && !Schema::hasColumn('konversi_stok', 'stok_awal_pcs')) {
            Schema::table('konversi_stok', function (Blueprint $table) {
                $table->renameColumn('dari_buffer', 'stok_awal_pcs');
            });
        }
        if (Schema::hasColumn('konversi_stok', 'sisa_buffer_after') && !Schema::hasColumn('konversi_stok', 'stok_sisa_pcs')) {
            Schema::table('konversi_stok', function (Blueprint $table) {
                $table->renameColumn('sisa_buffer_after', 'stok_sisa_pcs');
            });
        }

        // 3. Tabel detail_pemesanan_barang: qty_request & qty_received
        if (Schema::hasColumn('detail_pemesanan_barang', 'qty_request') && !Schema::hasColumn('detail_pemesanan_barang', 'jumlah_dipesan')) {
            Schema::table('detail_pemesanan_barang', function (Blueprint $table) {
                $table->renameColumn('qty_request', 'jumlah_dipesan');
            });
        }
        if (Schema::hasColumn('detail_pemesanan_barang', 'qty_received') && !Schema::hasColumn('detail_pemesanan_barang', 'jumlah_diterima')) {
            Schema::table('detail_pemesanan_barang', function (Blueprint $table) {
                $table->renameColumn('qty_received', 'jumlah_diterima');
            });
        }

        // 4. Tabel penerimaan_barang: qty_received menjadi jumlah_diterima
        if (Schema::hasColumn('penerimaan_barang', 'qty_received') && !Schema::hasColumn('penerimaan_barang', 'jumlah_diterima')) {
            Schema::table('penerimaan_barang', function (Blueprint $table) {
                $table->renameColumn('qty_received', 'jumlah_diterima');
            });
        }

        // 5. Tabel penyesuaian_stok: qty_adjustment menjadi jumlah_penyesuaian
        if (Schema::hasColumn('penyesuaian_stok', 'qty_adjustment') && !Schema::hasColumn('penyesuaian_stok', 'jumlah_penyesuaian')) {
            Schema::table('penyesuaian_stok', function (Blueprint $table) {
                $table->renameColumn('qty_adjustment', 'jumlah_penyesuaian');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penyesuaian_stok', function (Blueprint $table) {
            $table->renameColumn('jumlah_penyesuaian', 'qty_adjustment');
        });

        Schema::table('penerimaan_barang', function (Blueprint $table) {
            $table->renameColumn('jumlah_diterima', 'qty_received');
        });

        Schema::table('detail_pemesanan_barang', function (Blueprint $table) {
            $table->renameColumn('jumlah_diterima', 'qty_received');
            $table->renameColumn('jumlah_dipesan', 'qty_request');
        });

        Schema::table('konversi_stok', function (Blueprint $table) {
            $table->renameColumn('stok_sisa_pcs', 'sisa_buffer_after');
            $table->renameColumn('stok_awal_pcs', 'dari_buffer');
        });

        Schema::table('produk', function (Blueprint $table) {
            $table->renameColumn('jumlah_restock', 'roq');
            $table->renameColumn('batas_stok_minimum', 'rop');
        });
    }
};
