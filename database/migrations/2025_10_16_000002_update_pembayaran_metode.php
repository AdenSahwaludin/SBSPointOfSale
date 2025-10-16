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
        // First, expand the enum to include both old and new values
        DB::statement("
            ALTER TABLE pembayaran 
            MODIFY COLUMN metode ENUM(
                'TUNAI', 
                'QRIS', 
                'TRANSFER BCA',
                'VA_BCA', 
                'VA_BNI', 
                'VA_BRI', 
                'VA_MANDIRI', 
                'GOPAY', 
                'OVO', 
                'DANA', 
                'SHOPEEPAY', 
                'CREDIT_CARD'
            ) NOT NULL
        ");
        
        // Update existing data to match new values
        DB::statement("UPDATE pembayaran SET metode = 'VA_BCA' WHERE metode = 'TRANSFER BCA'");
        
        // Now remove the old value from enum
        DB::statement("
            ALTER TABLE pembayaran 
            MODIFY COLUMN metode ENUM(
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
                'CREDIT_CARD'
            ) NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert data back
        DB::statement("UPDATE pembayaran SET metode = 'TRANSFER BCA' WHERE metode = 'VA_BCA'");
        
        // Revert back to original payment methods
        DB::statement("
            ALTER TABLE pembayaran 
            MODIFY COLUMN metode ENUM(
                'TUNAI', 
                'QRIS', 
                'TRANSFER BCA'
            ) NOT NULL
        ");
    }
};
