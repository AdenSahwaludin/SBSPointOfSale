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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->string('id_pembayaran', 32)->primary();
            $table->string('id_transaksi', 40);
            $table->unsignedBigInteger('id_angsuran')->nullable();
            $table->enum('metode', [
                'TUNAI',
                'QRIS',
                'VA_BCA',
                'VA_BNI',
                'VA_BRI',
                'VA_PERMATA',
                'VA_MANDIRI',
                'GOPAY',
                'OVO',
                'DANA',
                'LINKAJA',
                'SHOPEEPAY',
                'CREDIT_CARD',
                'MANUAL_TRANSFER'
            ]);
            $table->decimal('jumlah', 18, 0);
            $table->timestamp('tanggal')->useCurrent();
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();

            $table->index('id_transaksi');
            $table->index('tanggal');
            $table->index('id_angsuran');

            $table->foreign('id_transaksi')->references('nomor_transaksi')->on('transaksi')
                ->onUpdate('cascade')->onDelete('cascade');
        });

        // Add check constraints
        DB::statement('ALTER TABLE pembayaran ADD CONSTRAINT pembayaran_id_chk CHECK (id_pembayaran REGEXP "^PAY-[0-9]{8}-[0-9]{7}$")');
        DB::statement('ALTER TABLE pembayaran ADD CONSTRAINT pembayaran_jumlah_chk CHECK (jumlah > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
