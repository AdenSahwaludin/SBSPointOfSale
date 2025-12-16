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
        Schema::create('pemesanan_barang', function (Blueprint $table) {
            $table->id('id_pemesanan_barang');
            $table->string('nomor_po', 32)->unique();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'received'])->default('draft');
            $table->dateTime('tanggal_request');
            $table->dateTime('tanggal_approval')->nullable();
            $table->text('catatan_approval')->nullable();
            $table->string('id_kasir', 15);
            $table->string('id_admin', 15)->nullable();
            $table->timestamps();

            $table->foreign('id_kasir')->references('id_pengguna')->on('pengguna')->cascadeOnDelete();
            $table->foreign('id_admin')->references('id_pengguna')->on('pengguna')->nullOnDelete();
            $table->index('status');
            $table->index('tanggal_request');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemesanan_barang');
    }
};
