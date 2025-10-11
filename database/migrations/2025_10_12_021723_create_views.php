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
        // Create v_produk_stok_setara_pcs view
        DB::statement("
            CREATE OR REPLACE VIEW v_produk_stok_setara_pcs AS
            SELECT
                p.id_produk, 
                p.sku, 
                p.nama, 
                p.id_kategori, 
                p.satuan, 
                p.isi_per_pack, 
                p.stok,
                CASE WHEN p.satuan='karton' THEN p.stok * p.isi_per_pack ELSE p.stok END AS stok_setara_pcs
            FROM produk p
        ");

        // Create v_piutang_pelanggan view
        DB::statement("
            CREATE OR REPLACE VIEW v_piutang_pelanggan AS
            SELECT
                k.id_pelanggan,
                SUM(j.jumlah_tagihan - j.jumlah_dibayar) AS saldo_piutang
            FROM kontrak_kredit k
            JOIN jadwal_angsuran j ON j.id_kontrak = k.id_kontrak
            WHERE k.status IN ('AKTIF','TUNDA')
              AND j.status IN ('DUE','LATE')
            GROUP BY k.id_pelanggan
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_piutang_pelanggan");
        DB::statement("DROP VIEW IF EXISTS v_produk_stok_setara_pcs");
    }
};
