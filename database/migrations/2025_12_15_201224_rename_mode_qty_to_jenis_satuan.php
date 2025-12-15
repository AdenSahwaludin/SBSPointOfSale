<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Rename mode_qty column ke jenis_satuan di tabel transaksi_detail
     * untuk penamaan yang lebih mudah dipahami pengguna toko.
     */
    public function up(): void
    {
        Schema::table('transaksi_detail', function (Blueprint $table) {
            $table->renameColumn('mode_qty', 'jenis_satuan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Kembalikan jenis_satuan ke mode_qty
     */
    public function down(): void
    {
        Schema::table('transaksi_detail', function (Blueprint $table) {
            $table->renameColumn('jenis_satuan', 'mode_qty');
        });
    }
};
