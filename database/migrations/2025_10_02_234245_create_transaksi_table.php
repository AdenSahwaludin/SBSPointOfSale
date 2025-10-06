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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->string('nomor_transaksi', 40)->primary();
            $table->string('id_pelanggan', 7);
            $table->string('id_kasir', 8);
            $table->timestamp('tanggal')->useCurrent();
            $table->integer('total_item')->default(0);
            $table->decimal('subtotal', 18, 0)->default(0);
            $table->decimal('diskon', 18, 0)->default(0);
            $table->decimal('pajak', 18, 0)->default(0);
            $table->decimal('biaya_pengiriman', 18, 0)->default(0);
            $table->decimal('total', 18, 0)->default(0);

            $table->enum('metode_bayar', [
                'TUNAI',
                'QRIS',
                'TRANSFER BCA',
                'KREDIT'
            ])->default('TUNAI');

            $table->enum('status_pembayaran', [
                'MENUNGGU',
                'LUNAS',
                'GAGAL',
                'BATAL',
                'REFUND_SEBAGIAN',
                'REFUND_PENUH'
            ])->default('MENUNGGU');

            $table->timestamp('paid_at')->nullable();

            // Cicilan Pintar fields
            $table->enum('jenis_transaksi', ['TUNAI', 'KREDIT'])->default('TUNAI');
            $table->decimal('dp', 12, 0)->default(0);
            $table->unsignedTinyInteger('tenor_bulan')->nullable();
            $table->decimal('bunga_persen', 5, 2)->default(0);
            $table->decimal('cicilan_bulanan', 12, 0)->nullable();
            $table->enum('ar_status', ['NA', 'AKTIF', 'LUNAS', 'GAGAL', 'BATAL'])->default('NA');
            $table->unsignedBigInteger('id_kontrak')->nullable();
            $table->timestamps();

            $table->index('id_pelanggan');
            $table->index('id_kasir');
            $table->index('tanggal');
            $table->index('status_pembayaran');
            $table->index('jenis_transaksi');
            $table->index('ar_status');

            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_kasir')->references('id_pengguna')->on('pengguna')
                ->onUpdate('cascade')->onDelete('restrict');
        });

        // Add check constraints
        DB::statement('ALTER TABLE transaksi ADD CONSTRAINT transaksi_no_chk CHECK (nomor_transaksi REGEXP "^INV-[0-9]{4}-[0-9]{2}-[0-9]{3}-P[0-9]{3,6}$")');
        DB::statement('ALTER TABLE transaksi ADD CONSTRAINT transaksi_dp_chk CHECK (dp >= 0)');
        DB::statement('ALTER TABLE transaksi ADD CONSTRAINT transaksi_tenor_chk CHECK (tenor_bulan IS NULL OR tenor_bulan BETWEEN 1 AND 24)');
        DB::statement('ALTER TABLE transaksi ADD CONSTRAINT transaksi_bunga_chk CHECK (bunga_persen >= 0)');
        DB::statement('ALTER TABLE transaksi ADD CONSTRAINT transaksi_cicilan_chk CHECK (cicilan_bulanan IS NULL OR cicilan_bulanan >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
