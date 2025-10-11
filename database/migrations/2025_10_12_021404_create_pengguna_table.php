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
        Schema::create('pengguna', function (Blueprint $table) {
            $table->string('id_pengguna', 8)->primary();
            $table->string('nama', 100);
            $table->string('email', 100)->nullable()->unique();
            $table->string('telepon', 15)->nullable();
            $table->string('password', 60)->nullable();
            $table->enum('role', ['kasir', 'admin'])->default('kasir');
            $table->timestamp('terakhir_login')->nullable();
            $table->rememberToken();
            $table->timestamps();

            // Add indexes
            $table->index('nama');
        });

        // Add check constraints (MySQL specific)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pengguna ADD CONSTRAINT pengguna_id_chk CHECK (id_pengguna REGEXP '^[0-9]{3}-[A-Z]{2,4}$')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengguna');
    }
};
