<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Ubah CHECK qty_from agar mengizinkan 0 (buffer-only conversions)
            try {
                DB::statement('ALTER TABLE konversi_stok DROP CHECK konversi_qty_from_chk');
            } catch (\Throwable $e) {
                // abaikan jika constraint belum ada (untuk kompatibilitas)
            }
            DB::statement('ALTER TABLE konversi_stok ADD CONSTRAINT konversi_qty_from_chk CHECK (qty_from >= 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement('ALTER TABLE konversi_stok DROP CHECK konversi_qty_from_chk');
            } catch (\Throwable $e) {
                // ignore if missing
            }
            DB::statement('ALTER TABLE konversi_stok ADD CONSTRAINT konversi_qty_from_chk CHECK (qty_from > 0)');
        }
    }
};

