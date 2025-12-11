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
        Schema::create('goods_in_details', function (Blueprint $table) {
            $table->id('id_goods_in_detail');
            $table->unsignedBigInteger('id_goods_in');
            $table->unsignedBigInteger('id_produk');
            $table->integer('qty_request');
            $table->integer('qty_received')->default(0);
            $table->timestamps();

            $table->foreign('id_goods_in')->references('id_goods_in')->on('goods_ins')->cascadeOnDelete();
            $table->foreign('id_produk')->references('id_produk')->on('produk')->restrictOnDelete();
            $table->index('id_goods_in');
            $table->index('id_produk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_in_details');
    }
};
