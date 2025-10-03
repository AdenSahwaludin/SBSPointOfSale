<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->char('id_produk', 13)->primary();
            $table->string('nama', 100);
            $table->string('gambar', 255)->nullable();
            $table->string('nomor_bpom', 50)->nullable();

            $table->decimal('harga', 18);                 // harga jual per unit
            $table->decimal('biaya_produk', 18)->default(0);

            $table->integer('stok')->default(0);
            $table->integer('batas_stok')->default(0);

            // pack / kemasan
            $table->string('satuan', 32)->default('pcs');         // (dulu: unit)
            $table->string('satuan_per_pack', 32)->default('karton'); // (dulu: pack_unit)
            $table->integer('isi_per_pack')->default(1);          // (dulu: pack_size)
            $table->decimal('harga_per_pack', 18)->nullable();

            // harga bertingkat (diskon)
            $table->integer('min_beli_diskon')->nullable();       // minimal qty dapat diskon
            $table->decimal('harga_diskon_per_unit', 18)->nullable();
            $table->decimal('harga_diskon_per_pack', 18)->nullable();

            $table->unsignedSmallInteger('id_kategori');
            $table->timestamps();

            $table->index('nama');
            $table->index('id_kategori');

            $table->foreign('id_kategori')
                ->references('id_kategori')->on('kategori')
                ->onUpdate('cascade')->onDelete('restrict');
        });

        // CHECK constraints (aman untuk MySQL 8+; kalau gagal, di-skip)
        try {
            DB::statement("ALTER TABLE `produk` 
                ADD CONSTRAINT `produk_id_chk` CHECK (`id_produk` REGEXP '^[0-9]{13}$')");
        } catch (\Throwable $e) {
        }

        try {
            DB::statement("ALTER TABLE `produk` 
                ADD CONSTRAINT `produk_harga_chk` CHECK (`harga` >= 0)");
        } catch (\Throwable $e) {
        }

        try {
            DB::statement("ALTER TABLE `produk` 
                ADD CONSTRAINT `produk_biaya_chk` CHECK (`biaya_produk` >= 0)");
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
