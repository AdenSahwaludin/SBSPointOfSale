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
        Schema::create('kontrak_kredit', function (Blueprint $table) {
            $table->id('id_kontrak');
            $table->string('nomor_kontrak', 30)->unique();
            $table->string('id_pelanggan', 7);
            $table->string('nomor_transaksi', 40);
            $table->date('mulai_kontrak');
            $table->unsignedTinyInteger('tenor_bulan');
            $table->decimal('pokok_pinjaman', 12, 0);
            $table->decimal('dp', 12, 0)->default(0);
            $table->decimal('bunga_persen', 5, 0)->default(0);
            $table->decimal('cicilan_bulanan', 12, 0);
            $table->enum('status', ['AKTIF', 'LUNAS', 'TUNDA', 'GAGAL'])->default('AKTIF');
            $table->unsignedTinyInteger('score_snapshot')->default(50);
            $table->string('alasan_eligibilitas', 200)->nullable();
            $table->timestamps();

            // Add indexes
            $table->index('status');
            $table->index('mulai_kontrak');
        });

        // Add check constraints (MySQL specific)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE kontrak_kredit ADD CONSTRAINT kontrak_tenor_chk CHECK (tenor_bulan BETWEEN 1 AND 24)");
            DB::statement("ALTER TABLE kontrak_kredit ADD CONSTRAINT kontrak_pokok_chk CHECK (pokok_pinjaman >= 0)");
            DB::statement("ALTER TABLE kontrak_kredit ADD CONSTRAINT kontrak_dp_chk CHECK (dp >= 0)");
            DB::statement("ALTER TABLE kontrak_kredit ADD CONSTRAINT kontrak_bunga_chk CHECK (bunga_persen >= 0)");
            DB::statement("ALTER TABLE kontrak_kredit ADD CONSTRAINT kontrak_cicilan_chk CHECK (cicilan_bulanan >= 0)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontrak_kredit');
    }
};
