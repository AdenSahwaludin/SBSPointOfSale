<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('konversi_stok', function (Blueprint $table) {
            $table->id('id_konversi');
            $table->unsignedBigInteger('from_produk_id');
            $table->unsignedBigInteger('to_produk_id');
            $table->integer('rasio');
            $table->integer('qty_from');
            $table->integer('qty_to');
            $table->enum('mode', ['penuh', 'parsial'])->default('penuh');
            $table->string('keterangan', 200)->nullable();
            $table->timestamps();

            // Foreign keys
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

        // Add check constraints (MySQL specific)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE konversi_stok ADD CONSTRAINT konversi_rasio_chk CHECK (rasio > 0)");
            DB::statement("ALTER TABLE konversi_stok ADD CONSTRAINT konversi_qty_from_chk CHECK (qty_from > 0)");
            DB::statement("ALTER TABLE konversi_stok ADD CONSTRAINT konversi_qty_to_chk CHECK (qty_to > 0)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konversi_stok');
    }
};
