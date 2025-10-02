<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('id_transaksi')->primary();
            $table->string('id_kasir');
            $table->string('nama_pelanggan')->nullable();
            $table->string('nomor_hp_pelanggan')->nullable();
            $table->integer('total_item');
            $table->integer('subtotal');
            $table->integer('diskon')->default(0);
            $table->integer('pajak')->default(0);
            $table->integer('total');
            $table->enum('metode_pembayaran', ['tunai', 'midtrans']);
            $table->integer('bayar')->nullable();
            $table->integer('kembalian')->nullable();
            $table->enum('status', ['pending', 'success', 'failed', 'cancelled'])->default('pending');
            $table->string('midtrans_order_id')->nullable();
            $table->string('midtrans_transaction_status')->nullable();
            $table->json('midtrans_response')->nullable();
            $table->timestamps();

            $table->foreign('id_kasir')->references('id_pengguna')->on('pengguna');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
