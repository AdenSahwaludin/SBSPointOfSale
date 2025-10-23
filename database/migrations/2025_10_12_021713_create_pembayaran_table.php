<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->string('id_pembayaran', 32)->primary();
            $table->string('id_transaksi', 40)->nullable(); // nullable untuk pembayaran kredit langsung (non-transaksi)
            $table->unsignedBigInteger('id_angsuran')->nullable();
            $table->enum('metode', allowed: ['TUNAI', 'QRIS', 'TRANSFER BCA', 'KREDIT']);
            $table->decimal('jumlah', 18, 0);
            $table->timestamp('tanggal')->useCurrent();
            $table->string('keterangan', 255)->nullable();
            // Fields for credit balance payment (pembayaran_kredit)
            $table->string('id_pelanggan', 7)->nullable(); // FK to pelanggan (untuk pembayaran kredit)
            $table->string('id_kasir', 8)->nullable(); // FK to pengguna (untuk pembayaran kredit)
            $table->enum('tipe_pembayaran', ['transaksi', 'kredit'])->default('transaksi'); // Membedakan tipe pembayaran
            $table->timestamps();

            // Add indexes
            $table->index('id_transaksi');
            $table->index('tanggal');
            $table->index('id_angsuran');
            $table->index('id_pelanggan');
            $table->index('tipe_pembayaran');

            // Foreign Keys
            $table->foreign('id_transaksi')
                ->references('nomor_transaksi')
                ->on('transaksi')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('id_angsuran')
                ->references('id_angsuran')
                ->on('jadwal_angsuran')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('id_pelanggan')
                ->references('id_pelanggan')
                ->on('pelanggan')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('id_kasir')
                ->references('id_pengguna')
                ->on('pengguna')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });

        // Add check constraints (MySQL specific)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pembayaran ADD CONSTRAINT pembayaran_id_chk CHECK (id_pembayaran REGEXP '^PAY-[0-9]{8}-[0-9]{7}$')");
            DB::statement("ALTER TABLE pembayaran ADD CONSTRAINT pembayaran_jumlah_chk CHECK (jumlah > 0)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
