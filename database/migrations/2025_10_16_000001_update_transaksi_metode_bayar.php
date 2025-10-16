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
        // Drop existing constraint if exists
        try {
            DB::statement("ALTER TABLE transaksi DROP CONSTRAINT IF EXISTS transaksi_metode_bayar_check");
        } catch (\Exception $e) {
            // Constraint might not exist
        }

        // Modify the metode_bayar column to include all payment methods
        DB::statement("
            ALTER TABLE transaksi 
            MODIFY COLUMN metode_bayar ENUM(
                'TUNAI', 
                'QRIS', 
                'VA_BCA', 
                'VA_BNI', 
                'VA_BRI', 
                'VA_MANDIRI', 
                'GOPAY', 
                'OVO', 
                'DANA', 
                'SHOPEEPAY', 
                'CREDIT_CARD',
                'KREDIT'
            ) NOT NULL DEFAULT 'TUNAI'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original payment methods
        DB::statement("
            ALTER TABLE transaksi 
            MODIFY COLUMN metode_bayar ENUM(
                'TUNAI', 
                'QRIS', 
                'TRANSFER BCA', 
                'KREDIT'
            ) NOT NULL DEFAULT 'TUNAI'
        ");
    }
};
