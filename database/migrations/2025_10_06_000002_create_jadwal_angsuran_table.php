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
        Schema::create('jadwal_angsuran', function (Blueprint $table) {
            $table->bigIncrements('id_angsuran');
            $table->unsignedBigInteger('id_kontrak');
            $table->unsignedTinyInteger('periode_ke');
            $table->date('jatuh_tempo');
            $table->decimal('jumlah_tagihan', 12, 0);
            $table->decimal('jumlah_dibayar', 12, 0)->default(0);
            $table->enum('status', ['DUE', 'PAID', 'LATE', 'VOID'])->default('DUE');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['id_kontrak', 'periode_ke']);
            $table->index(['jatuh_tempo', 'status']);

            $table->foreign('id_kontrak')->references('id_kontrak')->on('kontrak_kredit')
                ->onUpdate('cascade')->onDelete('cascade');
        });

        // Add check constraints
        DB::statement('ALTER TABLE jadwal_angsuran ADD CONSTRAINT jadwal_periode_chk CHECK (periode_ke >= 1)');
        DB::statement('ALTER TABLE jadwal_angsuran ADD CONSTRAINT jadwal_tagihan_chk CHECK (jumlah_tagihan >= 0)');
        DB::statement('ALTER TABLE jadwal_angsuran ADD CONSTRAINT jadwal_dibayar_chk CHECK (jumlah_dibayar >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_angsuran');
    }
};