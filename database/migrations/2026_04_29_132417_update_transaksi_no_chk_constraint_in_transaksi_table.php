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
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE transaksi DROP CHECK transaksi_no_chk');
            DB::statement("ALTER TABLE transaksi ADD CONSTRAINT transaksi_no_chk CHECK (nomor_transaksi REGEXP '^INV-[0-9]{4}-[0-9]{2}-[0-9]{3,6}-P[0-9]{3,6}$')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE transaksi DROP CHECK transaksi_no_chk');
            DB::statement("ALTER TABLE transaksi ADD CONSTRAINT transaksi_no_chk CHECK (nomor_transaksi REGEXP '^INV-[0-9]{4}-[0-9]{2}-[0-9]{3}-P[0-9]{3,6}$')");
        }
    }
};
