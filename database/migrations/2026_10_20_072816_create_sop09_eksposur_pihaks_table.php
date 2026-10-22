<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop09_eksposur_pihaks', function (Blueprint $table) {
            $table->id();
            $table->string('indikasi_id');
            $table->string('unit')->nullable();
            $table->string('area')->nullable();
            $table->decimal('nominal_awal_register', 15, 2)->default(0);
            $table->decimal('total_absolut_selisih_kka', 15, 2)->default(0);
            $table->integer('jumlah_uji_anomali')->default(0);
            $table->integer('jumlah_nasabah_terdampak')->default(0);
            $table->string('dampak_nasabah')->default('RENDAH');
            $table->string('dampak_reputasi')->default('RENDAH');
            $table->string('pihak_kunci_pimpinan')->nullable();
            $table->string('kolusi_lebih_dari_satu_pihak')->default('TIDAK');
            $table->string('kegagalan_kontrol')->default('TIDAK');
            $table->string('pola_berulang')->default('TIDAK');
            $table->string('kekuatan_bukti_awal')->default('SEDANG');
            $table->string('risiko_bukti_hilang')->default('SEDANG');
            $table->string('dampak_operasional')->default('RENDAH');
            $table->integer('skor_eksposur')->default(0);
            $table->string('level_sistem')->nullable();
            $table->string('saran_sistem')->nullable();
            $table->string('penilaian_reviewer')->nullable();
            $table->text('catatan_pihak_terkait_awal')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop09_eksposur_pihaks');
    }
};