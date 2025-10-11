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
            $table->enum('metode', ['TUNAI', 'QRIS', 'TRANSFER BCA']);
            $table->decimal('jumlah', 18, 0);
            $table->timestamp('tanggal')->useCurrent();
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();

            // Add indexes
            $table->index('id_transaksi');
            $table->index('tanggal');
            $table->index('id_angsuran');
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
