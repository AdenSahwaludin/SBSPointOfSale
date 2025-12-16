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
        Schema::create('penyesuaian_stok', function (Blueprint $table) {
            $table->id('id_penyesuaian');
            $table->unsignedBigInteger('id_produk');
            $table->enum('tipe', [
                'retur_pelanggan',
                'retur_gudang',
                'koreksi_plus',
                'koreksi_minus',
                'penyesuaian_opname',
                'expired',
                'rusak',
            ]);
            $table->integer('qty_adjustment');
            $table->text('alasan')->nullable();
            $table->string('id_pengguna', 15);
            $table->dateTime('tanggal_adjustment');
            $table->timestamps();

            $table->foreign('id_produk')->references('id_produk')->on('produk')->restrictOnDelete();
            $table->foreign('id_pengguna')->references('id_pengguna')->on('pengguna')->restrictOnDelete();
            $table->index('tipe');
            $table->index('tanggal_adjustment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyesuaian_stok');
    }
};
