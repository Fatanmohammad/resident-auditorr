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
        Schema::create('sop09_triases', function (Blueprint $table) {
            $table->id();
            // Tambahkan kolom-kolom yang dibutuhkan untuk triase di sini sesuai kebutuhan aplikasi Anda
            $table->string('kode_unit')->nullable();
            $table->string('perihal')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('status')->default('Draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sop09_triases');
    }
};