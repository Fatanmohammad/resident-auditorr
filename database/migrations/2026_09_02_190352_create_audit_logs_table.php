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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // RA yang upload
            $table->string('kode_unit');
            $table->string('jenis_file'); // Misal: DUMP_01
            $table->string('nama_file');
            $table->enum('status', ['Proses', 'Berhasil', 'Gagal'])->default('Proses');
            $table->integer('total_low')->default(0);
            $table->integer('total_moderate')->default(0);
            $table->integer('total_high')->default(0);
            $table->text('pesan_error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
