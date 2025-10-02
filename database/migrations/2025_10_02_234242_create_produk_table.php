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
        Schema::create('produk', function (Blueprint $table) {
            $table->char('id_produk', 13)->primary();
            $table->string('nama', 100);
            $table->string('gambar', 255)->nullable();
            $table->string('nomor_bpom', 50)->nullable();
            $table->decimal('harga', 18, 2);
            $table->decimal('biaya_produk', 18, 2)->default(0);
            $table->integer('stok')->default(0);
            $table->integer('batas_stok')->default(0);
            $table->string('unit', 32)->default('pcs');
            $table->string('pack_unit', 32)->default('karton');
            $table->integer('pack_size')->default(1);
            $table->decimal('harga_pack', 18, 2)->nullable();
            $table->integer('qty_tier1')->nullable();
            $table->decimal('harga_tier1', 18, 2)->nullable();
            $table->integer('harga_tier_qty')->nullable();
            $table->decimal('harga_tier_pack', 18, 2)->nullable();
            $table->smallInteger('id_kategori')->unsigned();
            $table->timestamps();

            $table->index('nama');
            $table->index('id_kategori');

            $table->foreign('id_kategori')->references('id_kategori')->on('kategori')
                ->onUpdate('cascade')->onDelete('restrict');
        });

        // Add check constraints
        DB::statement('ALTER TABLE produk ADD CONSTRAINT produk_id_chk CHECK (id_produk REGEXP "^[0-9]{13}$")');
        DB::statement('ALTER TABLE produk ADD CONSTRAINT produk_harga_chk CHECK (harga >= 0)');
        DB::statement('ALTER TABLE produk ADD CONSTRAINT produk_biaya_chk CHECK (biaya_produk >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
