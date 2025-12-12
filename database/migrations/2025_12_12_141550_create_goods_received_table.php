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
        Schema::create('goods_received', function (Blueprint $table) {
            $table->id('id_goods_received');
            $table->unsignedBigInteger('id_goods_in');
            $table->unsignedBigInteger('id_goods_in_detail');
            $table->unsignedBigInteger('id_produk');
            $table->integer('qty_received'); // Qty diterima oleh kasir
            $table->string('id_kasir', 8)->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('completed'); // pending = belum selesai, completed = selesai
            $table->timestamps();

            // Foreign keys
            $table->foreign('id_goods_in')
                ->references('id_goods_in')
                ->on('goods_ins')
                ->onDelete('cascade');

            $table->foreign('id_goods_in_detail')
                ->references('id_goods_in_detail')
                ->on('goods_in_details')
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
            $table->index('id_goods_in');
            $table->index('id_kasir');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_received');
    }
};
