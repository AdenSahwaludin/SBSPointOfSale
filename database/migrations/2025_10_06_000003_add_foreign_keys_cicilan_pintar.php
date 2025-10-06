<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 /**
  * Run the migrations.
  */
 public function up(): void
 {
  // Add foreign key from transaksi to kontrak_kredit
  Schema::table('transaksi', function (Blueprint $table) {
   $table->foreign('id_kontrak')->references('id_kontrak')->on('kontrak_kredit')
    ->onUpdate('cascade')->onDelete('set null');
  });

  // Add foreign key from pembayaran to jadwal_angsuran
  Schema::table('pembayaran', function (Blueprint $table) {
   $table->foreign('id_angsuran')->references('id_angsuran')->on('jadwal_angsuran')
    ->onUpdate('cascade')->onDelete('set null');
  });
 }

 /**
  * Reverse the migrations.
  */
 public function down(): void
 {
  Schema::table('transaksi', function (Blueprint $table) {
   $table->dropForeign(['id_kontrak']);
  });

  Schema::table('pembayaran', function (Blueprint $table) {
   $table->dropForeign(['id_angsuran']);
  });
 }
};