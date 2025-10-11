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
            $table->id('id_produk');
            $table->string('sku', 32)->unique();
            $table->string('barcode', 13)->nullable()->unique();
            $table->string('no_bpom', 18)->nullable();
            $table->string('nama', 100);
            $table->string('id_kategori', 4);
            $table->enum('satuan', ['pcs', 'karton', 'pack'])->default('pcs');
            $table->integer('isi_per_pack')->default(1);
            $table->decimal('harga', 18, 0);
            $table->integer('stok')->default(0);
            $table->timestamps();

            // Add indexes
            $table->index('nama');
            $table->index('id_kategori');
        });

        // Add check constraints (MySQL specific)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE produk ADD CONSTRAINT produk_isi_per_pack_chk CHECK (isi_per_pack > 0)");
            DB::statement("ALTER TABLE produk ADD CONSTRAINT produk_harga_chk CHECK (harga >= 0)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
