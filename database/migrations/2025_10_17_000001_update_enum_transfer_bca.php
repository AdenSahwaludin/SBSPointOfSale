<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE transaksi MODIFY COLUMN metode_bayar ENUM('TUNAI','QRIS','TRANSFER BCA','KREDIT') NOT NULL DEFAULT 'TUNAI'");
            DB::statement("ALTER TABLE pembayaran MODIFY COLUMN metode ENUM('TUNAI','QRIS','TRANSFER BCA','KREDIT') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Revert to previous set without 'TRANSFER BCA'
            DB::statement("ALTER TABLE transaksi MODIFY COLUMN metode_bayar ENUM('TUNAI','QRIS','KREDIT') NOT NULL DEFAULT 'TUNAI'");
            DB::statement("ALTER TABLE pembayaran MODIFY COLUMN metode ENUM('TUNAI','QRIS','KREDIT') NOT NULL");
        }
    }
};

