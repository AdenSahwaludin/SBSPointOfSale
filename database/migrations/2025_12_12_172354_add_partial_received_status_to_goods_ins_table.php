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
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Use temp column approach
            Schema::table('goods_ins', function (Blueprint $table) {
                $table->string('status_temp', 20)->default('draft')->after('status');
            });

            DB::statement('UPDATE goods_ins SET status_temp = status');

            Schema::table('goods_ins', function (Blueprint $table) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            });

            Schema::table('goods_ins', function (Blueprint $table) {
                $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'partial_received', 'received'])->default('draft')->after('nomor_po');
                $table->index('status');
            });

            DB::statement('UPDATE goods_ins SET status = status_temp');

            Schema::table('goods_ins', function (Blueprint $table) {
                $table->dropColumn('status_temp');
            });
        } else {
            // MySQL: Use MODIFY COLUMN
            DB::statement("ALTER TABLE goods_ins MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected', 'partial_received', 'received') DEFAULT 'draft'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Use temp column approach
            Schema::table('goods_ins', function (Blueprint $table) {
                $table->string('status_temp', 20)->default('draft')->after('status');
            });

            DB::statement('UPDATE goods_ins SET status_temp = status');

            Schema::table('goods_ins', function (Blueprint $table) {
                $table->dropIndex(['status']);
                $table->dropColumn('status');
            });

            Schema::table('goods_ins', function (Blueprint $table) {
                $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'received'])->default('draft')->after('nomor_po');
                $table->index('status');
            });

            DB::statement('UPDATE goods_ins SET status = status_temp');

            Schema::table('goods_ins', function (Blueprint $table) {
                $table->dropColumn('status_temp');
            });
        } else {
            // MySQL: Use MODIFY COLUMN
            DB::statement("ALTER TABLE goods_ins MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected', 'received') DEFAULT 'draft'");
        }
    }
};
