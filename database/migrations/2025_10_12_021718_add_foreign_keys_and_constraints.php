<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add foreign key constraints
        
        // Produk -> Kategori
        Schema::table('produk', function (Blueprint $table) {
            $table->foreign('id_kategori')
                  ->references('id_kategori')
                  ->on('kategori')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });

        // Konversi Stok -> Produk
        Schema::table('konversi_stok', function (Blueprint $table) {
            $table->foreign('from_produk_id')
                  ->references('id_produk')
                  ->on('produk')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
                  
            $table->foreign('to_produk_id')
                  ->references('id_produk')
                  ->on('produk')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });

        // Transaksi -> Pelanggan & Pengguna
        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreign('id_pelanggan')
                  ->references('id_pelanggan')
                  ->on('pelanggan')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
                  
            $table->foreign('id_kasir')
                  ->references('id_pengguna')
                  ->on('pengguna')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });

        // Transaksi Detail -> Transaksi & Produk
        Schema::table('transaksi_detail', function (Blueprint $table) {
            $table->foreign('nomor_transaksi')
                  ->references('nomor_transaksi')
                  ->on('transaksi')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
                  
            $table->foreign('id_produk')
                  ->references('id_produk')
                  ->on('produk')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });

        // Kontrak Kredit -> Pelanggan & Transaksi
        Schema::table('kontrak_kredit', function (Blueprint $table) {
            $table->foreign('id_pelanggan')
                  ->references('id_pelanggan')
                  ->on('pelanggan')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
                  
            $table->foreign('nomor_transaksi')
                  ->references('nomor_transaksi')
                  ->on('transaksi')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });

        // Jadwal Angsuran -> Kontrak Kredit
        Schema::table('jadwal_angsuran', function (Blueprint $table) {
            $table->foreign('id_kontrak')
                  ->references('id_kontrak')
                  ->on('kontrak_kredit')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });

        // Pembayaran -> Transaksi & Jadwal Angsuran
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->foreign('id_transaksi')
                  ->references('nomor_transaksi')
                  ->on('transaksi')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
                  
            $table->foreign('id_angsuran')
                  ->references('id_angsuran')
                  ->on('jadwal_angsuran')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });

        // Transaksi -> Kontrak Kredit (circular reference, added last)
        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreign('id_kontrak')
                  ->references('id_kontrak')
                  ->on('kontrak_kredit')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys in reverse order
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['id_kontrak']);
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropForeign(['id_transaksi']);
            $table->dropForeign(['id_angsuran']);
        });

        Schema::table('jadwal_angsuran', function (Blueprint $table) {
            $table->dropForeign(['id_kontrak']);
        });

        Schema::table('kontrak_kredit', function (Blueprint $table) {
            $table->dropForeign(['id_pelanggan']);
            $table->dropForeign(['nomor_transaksi']);
        });

        Schema::table('transaksi_detail', function (Blueprint $table) {
            $table->dropForeign(['nomor_transaksi']);
            $table->dropForeign(['id_produk']);
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['id_pelanggan']);
            $table->dropForeign(['id_kasir']);
        });

        Schema::table('konversi_stok', function (Blueprint $table) {
            $table->dropForeign(['from_produk_id']);
            $table->dropForeign(['to_produk_id']);
        });

        Schema::table('produk', function (Blueprint $table) {
            $table->dropForeign(['id_kategori']);
        });
    }
};
