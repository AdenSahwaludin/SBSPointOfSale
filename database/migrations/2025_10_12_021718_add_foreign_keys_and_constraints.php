<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add circular reference foreign key (transaksi.id_kontrak -> kontrak_kredit.id_kontrak)
     * This is added separately to avoid circular reference errors during table creation
     */
    public function up(): void
    {
        // Transaksi -> Kontrak Kredit (circular reference, added after both tables exist)
        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreign('id_kontrak')
                ->references('id_kontrak')
                ->on('kontrak_kredit')
                ->onUpdate('cascade')
                ->onDelete('set null');
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
    }
};
