<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->string('id_pelanggan', 7)->primary();
            $table->string('nama', 100);
            $table->string('email', 100)->unique()->nullable();
            $table->string('telepon', 15)->nullable();
            $table->string('kota', 50)->nullable();
            $table->text('alamat')->nullable();
            $table->boolean('aktif')->default(true);
            $table->date('tanggal_daftar')->default(now());
            $table->timestamps();
            
            $table->index('nama');
        });
        
        // Add check constraint for id_pelanggan format
        DB::statement('ALTER TABLE pelanggan ADD CONSTRAINT pelanggan_id_chk CHECK (id_pelanggan REGEXP "^P[0-9]{3,6}$")');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};
