<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi_detail', function (Blueprint $table) {
            $table->bigIncrements('id_detail');
            $table->string('nomor_transaksi', 40);
            $table->char('id_produk', 13);
            $table->string('nama_produk', 255);
            $table->decimal('harga_satuan', 18, 2);
            $table->integer('jumlah');
            $table->enum('mode_qty', ['unit', 'pack'])->default('unit');
            $table->integer('pack_size_snapshot')->default(1);
            $table->decimal('diskon_item', 18, 2)->default(0);
            $table->decimal('subtotal', 18, 2);
            $table->timestamps();
            
            $table->index('nomor_transaksi');
            $table->index('id_produk');
            
            $table->foreign('nomor_transaksi')->references('nomor_transaksi')->on('transaksi')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_produk')->references('id_produk')->on('produk')
                ->onUpdate('cascade')->onDelete('restrict');
        });
        
        // Add check constraints
        DB::statement('ALTER TABLE transaksi_detail ADD CONSTRAINT td_jumlah_chk CHECK (jumlah > 0)');
        DB::statement('ALTER TABLE transaksi_detail ADD CONSTRAINT td_packsize_chk CHECK (pack_size_snapshot > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_detail');
    }
};
