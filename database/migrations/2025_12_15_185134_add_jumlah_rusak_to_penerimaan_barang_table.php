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
        // Only add if jumlah_rusak doesn't exist (CREATE migration already includes it)
        if (!Schema::hasColumn('penerimaan_barang', 'jumlah_rusak')) {
            Schema::table('penerimaan_barang', function (Blueprint $table) {
                $table->integer('jumlah_rusak')->default(0)->after('jumlah_diterima');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop if column exists
        if (Schema::hasColumn('penerimaan_barang', 'jumlah_rusak')) {
            Schema::table('penerimaan_barang', function (Blueprint $table) {
                $table->dropColumn('jumlah_rusak');
            });
        }
    }
};
