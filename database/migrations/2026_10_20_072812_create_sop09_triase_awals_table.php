<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop09_triase_awals', function (Blueprint $table) {
            $table->id();
            $table->string('indikasi_id');
            $table->date('tanggal')->nullable();
            $table->string('unit')->nullable();
            $table->string('sumber')->nullable();
            $table->string('data_dasar_tersedia')->default('YA');
            $table->string('anomali_dapat_dijelaskan')->default('TIDAK');
            $table->string('red_flag_pendukung')->default('ADA');
            $table->string('pola_berulang')->default('TIDAK');
            $table->decimal('nominal_awal', 15, 2)->default(0);
            $table->string('nasabah')->default('TIDAK');
            $table->string('pihak_kunci_pimpinan')->default('TIDAK');
            $table->string('risiko_bukti_hilang')->default('SEDANG');
            $table->string('reputasi')->default('SEDANG');
            $table->string('konflik_kepentingan')->default('TIDAK');
            $table->integer('skor_prioritas')->default(0);
            $table->string('saran_sistem')->nullable();
            $table->string('urgent')->default('TIDAK');
            $table->date('batas_triase')->nullable();
            $table->string('keputusan_triase')->nullable();
            $table->string('reviewer_unit_ra')->nullable();
            $table->date('tanggal_review')->nullable();
            $table->text('dasar_keputusan_catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop09_triase_awals');
    }
};