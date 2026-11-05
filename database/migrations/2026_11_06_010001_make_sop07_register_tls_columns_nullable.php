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
        Schema::table('sop07_register_tls', function (Blueprint $table) {
            $table->string('kode_unit')->nullable()->default('000')->change();
            $table->string('nama_unit')->nullable()->change();
            $table->string('nomor_laporan')->nullable()->change();
            $table->date('tanggal_laporan')->nullable()->change();
            $table->text('rekomendasi_temuan')->nullable()->change();
            $table->text('komitmen_auditee')->nullable()->change();
            $table->date('target_penyelesaian')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversible if needed
    }
};

