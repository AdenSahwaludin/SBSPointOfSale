<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Determine table name - could be English or Indonesian depending on migration order
        $tableName = Schema::hasTable('pemesanan_barang') ? 'pemesanan_barang' : 'goods_ins';
        
        if (!Schema::hasTable($tableName)) {
            return; // Skip if table doesn't exist
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Use temp column approach
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('status_temp', 20)->default('draft')->after('status');
            });

            DB::statement("UPDATE {$tableName} SET status_temp = status");

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            });

            Schema::table($tableName, function (Blueprint $table) {
                $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'partial_received', 'received'])->default('draft')->after('nomor_po');
                $table->index('status');
            });

            DB::statement("UPDATE {$tableName} SET status = status_temp");

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('status_temp');
            });
        } else {
            // MySQL: Use MODIFY COLUMN
            DB::statement("ALTER TABLE {$tableName} MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected', 'partial_received', 'received') DEFAULT 'draft'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Determine table name - could be English or Indonesian depending on migration order
        $tableName = Schema::hasTable('pemesanan_barang') ? 'pemesanan_barang' : 'goods_ins';
        
        if (!Schema::hasTable($tableName)) {
            return; // Skip if table doesn't exist
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Use temp column approach
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('status_temp', 20)->default('draft')->after('status');
            });

            DB::statement("UPDATE {$tableName} SET status_temp = status");

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            });

            Schema::table($tableName, function (Blueprint $table) {
                $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'received'])->default('draft')->after('nomor_po');
                $table->index('status');
            });

            DB::statement("UPDATE {$tableName} SET status = status_temp");

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('status_temp');
            });
        } else {
            // MySQL: Use MODIFY COLUMN
            DB::statement("ALTER TABLE {$tableName} MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected', 'received') DEFAULT 'draft'");
        }
    }
};
