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
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->string('id_pelanggan', 7)->primary();
            $table->string('nama', 100);
            $table->string('email', 100)->nullable()->unique();
            $table->string('telepon', 15)->nullable();
            $table->string('kota', 50)->nullable();
            $table->text('alamat')->nullable();
            $table->boolean('aktif')->default(1);
            $table->unsignedTinyInteger('trust_score')->default(50);
            $table->decimal('credit_limit', 12, 0)->default(0);
            $table->enum('status_kredit', ['aktif', 'nonaktif'])->default('aktif');
            $table->decimal('saldo_kredit', 12, 0)->default(0);
            $table->timestamps();
            // Add indexes
            $table->index('nama');
            $table->index('status_kredit');
            $table->index('saldo_kredit');
        });

        // Add check constraints (MySQL specific)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pelanggan ADD CONSTRAINT pelanggan_id_chk CHECK (id_pelanggan REGEXP '^P[0-9]{3,6}$')");
            DB::statement("ALTER TABLE pelanggan ADD CONSTRAINT pelanggan_trust_score_chk CHECK (trust_score BETWEEN 0 AND 100)");
            DB::statement("ALTER TABLE pelanggan ADD CONSTRAINT pelanggan_credit_limit_chk CHECK (credit_limit >= 0)");
            DB::statement("ALTER TABLE pelanggan ADD CONSTRAINT pelanggan_saldo_kredit_chk CHECK (saldo_kredit >= 0)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};
