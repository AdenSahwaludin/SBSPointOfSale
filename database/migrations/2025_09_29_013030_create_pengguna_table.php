<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengguna', function (Blueprint $table) {
            $table->char('id_pengguna', 8)->primary();
            $table->string('nama', 100);
            $table->string('email', 100)->nullable()->unique();
            $table->string('telepon', 15)->nullable();
            $table->string('password', 255)->nullable();
            $table->enum('role', ['kasir', 'admin'])->default('kasir');
            $table->timestamp('terakhir_login')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->index('nama');
        });

        if ($this->isMySql()) {
            DB::statement("
                ALTER TABLE pengguna
                ADD CONSTRAINT chk_pengguna_id_format
                CHECK (REGEXP_LIKE(id_pengguna, '^[0-9]{3}-[A-Z]{2,4}$'))
            ");
        }
    }

    public function down(): void
    {
        if ($this->isMySql()) {
            try {
                DB::statement("ALTER TABLE pengguna DROP CHECK chk_pengguna_id_format");
            } catch (\Throwable $e) {
            }
        }

        Schema::dropIfExists('pengguna');
    }

    private function isMySql(): bool
    {
        return Schema::getConnection()->getDriverName() === 'mysql';
    }
};