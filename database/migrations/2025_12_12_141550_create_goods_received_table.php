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
        Schema::create('penerimaan_barang', function (Blueprint $table) {
            $table->id('id_penerimaan_barang');
            $table->unsignedBigInteger('id_pemesanan_barang');
            $table->unsignedBigInteger('id_detail_pemesanan_barang');
            $table->unsignedBigInteger('id_produk');
            $table->integer('jumlah_diterima'); // Qty diterima oleh kasir
            $table->integer('jumlah_rusak')->default(0); // Qty rusak
            $table->string('id_kasir', 8)->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('completed'); // pending = belum selesai, completed = selesai
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_pemesanan_barang')
                ->references('id_pemesanan_barang')
                ->on('pemesanan_barang')
                ->onDelete('cascade');

            $table->foreign('id_detail_pemesanan_barang')
                ->references('id_detail_pemesanan_barang')
                ->on('detail_pemesanan_barang')
                ->onDelete('cascade');

            $table->foreign('id_produk')
                ->references('id_produk')
                ->on('produk')
                ->onDelete('cascade');

            $table->foreign('id_kasir')
                ->references('id_pengguna')
                ->on('pengguna')
                ->onDelete('set null');

            // Indexes
            $table->index('id_pemesanan_barang');
            $table->index('id_kasir');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_barang');
    }
};
