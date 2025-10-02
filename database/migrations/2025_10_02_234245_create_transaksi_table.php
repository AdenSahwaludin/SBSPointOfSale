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
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('diskon', 18, 2)->default(0);
            $table->decimal('pajak', 18, 2)->default(0);
            $table->decimal('biaya_pengiriman', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);

            $table->enum('metode_bayar', [
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
            ])->default('TUNAI');

            $table->enum('status_pembayaran', [
                'PENDING',
                'PAID',
                'FAILED',
                'VOID',
                'EXPIRED',
                'REFUND_PARTIAL',
                'REFUNDED'
            ])->default('PENDING');

            $table->timestamp('paid_at')->nullable();

            // Midtrans fields
            $table->string('midtrans_order_id', 64)->nullable();
            $table->string('midtrans_transaction_id', 64)->nullable();
            $table->string('midtrans_status', 64)->nullable();
            $table->string('midtrans_payment_type', 64)->nullable();
            $table->json('midtrans_va_numbers')->nullable();
            $table->decimal('midtrans_gross_amount', 18, 2)->nullable();
            $table->json('midtrans_response')->nullable();

            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            $table->index('id_pelanggan');
            $table->index('id_kasir');
            $table->index('tanggal');
            $table->index('status_pembayaran');

            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('id_kasir')->references('id_pengguna')->on('pengguna')
                ->onUpdate('cascade')->onDelete('restrict');
        });

        // Add check constraint for nomor_transaksi format
        DB::statement('ALTER TABLE transaksi ADD CONSTRAINT transaksi_no_chk CHECK (nomor_transaksi REGEXP "^INV-[0-9]{4}-[0-9]{2}-[0-9]{3}-P[0-9]{3,6}$")');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
