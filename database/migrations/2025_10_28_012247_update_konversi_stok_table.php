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
        Schema::table('konversi_stok', function (Blueprint $table) {
            if (!Schema::hasColumn('konversi_stok', 'packs_used')) {
                $table->integer('packs_used')->default(0)->after('mode')->comment('Jumlah karton yang digunakan');
            }
            if (!Schema::hasColumn('konversi_stok', 'dari_buffer')) {
                $table->integer('dari_buffer')->default(0)->after('packs_used')->comment('PCS yang diambil dari buffer terbuka');
            }
            if (!Schema::hasColumn('konversi_stok', 'sisa_buffer_after')) {
                $table->integer('sisa_buffer_after')->default(0)->after('dari_buffer')->comment('Sisa PCS buffer setelah konversi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('konversi_stok', function (Blueprint $table) {
            $table->dropColumn(['packs_used', 'dari_buffer', 'sisa_buffer_after']);
        });
    }
};
