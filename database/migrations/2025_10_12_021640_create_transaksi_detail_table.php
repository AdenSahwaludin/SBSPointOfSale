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
        Schema::create('transaksi_detail', function (Blueprint $table) {
            $table->id('id_detail');
            $table->string('nomor_transaksi', 40);
            $table->unsignedBigInteger('id_produk');
            $table->string('nama_produk', 255);
            $table->decimal('harga_satuan', 18, 0);
            $table->integer('jumlah');
            $table->enum('mode_qty', ['unit', 'pack'])->default('unit');
            $table->integer('isi_pack_saat_transaksi')->default(1)->comment('1️⃣ Snapshot isi per pack saat transaksi untuk audit trail');
            $table->decimal('diskon_item', 18, 0)->default(0);
            $table->decimal('subtotal', 18, 0);
            $table->timestamps();

            // Add indexes
            $table->index('nomor_transaksi');
            $table->index('id_produk');
        });

        // Add check constraints (MySQL specific)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE transaksi_detail ADD CONSTRAINT td_jumlah_chk CHECK (jumlah > 0)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_detail');
    }
};
