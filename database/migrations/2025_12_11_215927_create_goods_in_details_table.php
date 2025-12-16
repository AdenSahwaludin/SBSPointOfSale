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
        Schema::create('detail_pemesanan_barang', function (Blueprint $table) {
            $table->id('id_detail_pemesanan_barang');
            $table->unsignedBigInteger('id_pemesanan_barang');
            $table->unsignedBigInteger('id_produk');
            $table->integer('jumlah_dipesan');
            $table->integer('jumlah_diterima')->default(0);
            $table->timestamps();

            $table->foreign('id_pemesanan_barang')->references('id_pemesanan_barang')->on('pemesanan_barang')->cascadeOnDelete();
            $table->foreign('id_produk')->references('id_produk')->on('produk')->restrictOnDelete();
            $table->index('id_pemesanan_barang');
            $table->index('id_produk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pemesanan_barang');
    }
};
