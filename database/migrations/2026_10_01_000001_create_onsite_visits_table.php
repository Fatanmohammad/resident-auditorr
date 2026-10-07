<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onsite_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheduled_visit_id')->nullable(); // link ke jadwal
            $table->string('kode_unit');
            $table->string('nama_unit')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->unsignedBigInteger('ra_id')->nullable(); // RA pelaksana
            $table->enum('status', ['Persiapan', 'Berlangsung', 'Selesai'])->default('Persiapan');
            $table->string('periode'); // format: YYYY-MM (bulan kunjungan)
            $table->integer('total_populasi')->default(0);
            $table->integer('total_sampel')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onsite_visits');
    }
};
