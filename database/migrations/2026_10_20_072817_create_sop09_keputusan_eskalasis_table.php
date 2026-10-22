<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop09_keputusan_eskalasis', function (Blueprint $table) {
            $table->id();
            $table->string('decision_id')->unique();
            $table->string('indikasi_id');
            $table->string('unit')->nullable();
            $table->string('saran_sistem')->nullable();
            $table->string('keputusan_final')->nullable();
            $table->date('tanggal_keputusan')->nullable();
            $table->string('diputuskan_oleh')->nullable();
            $table->string('jabatan')->nullable();
            $table->text('arahan_tindakan_segera')->nullable();
            $table->string('jalur_penerima_eskalasi')->nullable();
            $table->date('due_date')->nullable();
            $table->string('referensi_sop_lanjut')->nullable();
            $table->string('laporan_insidentil_id')->nullable();
            $table->string('serah_terima_wajib')->default('TIDAK');
            $table->string('status_serah_terima')->default('BELUM');
            $table->string('status_keputusan')->default('DRAFT');
            $table->text('alasan_override_simpulan')->nullable();
            $table->text('catatan_review')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop09_keputusan_eskalasis');
    }
};