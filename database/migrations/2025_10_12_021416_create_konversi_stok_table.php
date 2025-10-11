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
        Schema::create('konversi_stok', function (Blueprint $table) {
            $table->id('id_konversi');
            $table->unsignedBigInteger('from_produk_id');
            $table->unsignedBigInteger('to_produk_id');
            $table->integer('rasio');
            $table->integer('qty_from');
            $table->integer('qty_to');
            $table->string('keterangan', 200)->nullable();
            $table->timestamps();
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
