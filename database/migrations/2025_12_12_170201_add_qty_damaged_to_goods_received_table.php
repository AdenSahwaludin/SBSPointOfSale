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
        // Determine which table exists
        $tableName = Schema::hasTable('penerimaan_barang') ? 'penerimaan_barang' : (Schema::hasTable('goods_received') ? 'goods_received' : null);
        
        if (!$tableName) {
            return; // No table exists yet, skip
        }
        
        // Only add if neither column name exists (CREATE migration already includes jumlah_rusak)
        if (!Schema::hasColumn($tableName, 'qty_damaged') && !Schema::hasColumn($tableName, 'jumlah_rusak')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->integer('qty_damaged')->default(0)->after('qty_received');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check both table names
        $tableName = Schema::hasTable('penerimaan_barang') ? 'penerimaan_barang' : 'goods_received';
        
        if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'qty_damaged')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('qty_damaged');
            });
        }
    }
};
