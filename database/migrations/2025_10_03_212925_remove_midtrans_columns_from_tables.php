<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove Midtrans columns from transaksi table
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn([
                'midtrans_order_id',
                'midtrans_transaction_id',
                'midtrans_status',
                'midtrans_payment_type',
                'midtrans_va_numbers',
                'midtrans_gross_amount',
                'midtrans_response'
            ]);
        });

        // Remove Midtrans columns from pembayaran table
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn([
                'midtrans_transaction_id',
                'midtrans_status',
                'midtrans_payment_type',
                'midtrans_response'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back Midtrans columns to transaksi table
        Schema::table('transaksi', function (Blueprint $table) {
            $table->string('midtrans_order_id', 64)->nullable();
            $table->string('midtrans_transaction_id', 64)->nullable();
            $table->string('midtrans_status', 64)->nullable();
            $table->string('midtrans_payment_type', 64)->nullable();
            $table->json('midtrans_va_numbers')->nullable();
            $table->decimal('midtrans_gross_amount', 18, 2)->nullable();
            $table->json('midtrans_response')->nullable();
        });

        // Add back Midtrans columns to pembayaran table
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->string('midtrans_transaction_id', 64)->nullable();
            $table->string('midtrans_status', 64)->nullable();
            $table->string('midtrans_payment_type', 64)->nullable();
            $table->json('midtrans_response')->nullable();
        });
    }
};
