<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW v_piutang_pelanggan AS
            SELECT
                k.id_pelanggan,
                SUM(j.jumlah_tagihan - j.jumlah_dibayar) AS saldo_piutang
            FROM kontrak_kredit k
            JOIN jadwal_angsuran j ON j.id_kontrak = k.id_kontrak
            WHERE k.status IN ('AKTIF','TUNDA') AND j.status IN ('DUE','LATE')
            GROUP BY k.id_pelanggan
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_piutang_pelanggan");
    }
};