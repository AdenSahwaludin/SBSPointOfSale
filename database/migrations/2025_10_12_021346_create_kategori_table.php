<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kategori', function (Blueprint $table) {
            $table->string('id_kategori', 4)->primary();
            $table->string('nama', 50)->unique();
            $table->timestamps();

            // Add index for nama
            $table->index('nama');
        });

        // Add check constraints (MySQL specific)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE kategori ADD CONSTRAINT kategori_id_chk CHECK (id_kategori REGEXP '^[A-Z0-9]{2,4}$')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori');
    }
};
