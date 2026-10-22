<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop09_kka_pemeriksaan_awals', function (Blueprint $table) {
            $table->id();
            $table->string('test_id')->unique();
            $table->string('indikasi_id');
            $table->string('unit')->nullable();
            $table->string('area')->nullable();
            $table->string('area_uji_awal')->nullable();
            $table->text('tujuan_uji')->nullable();
            $table->text('kriteria_seharusnya')->nullable();
            $table->string('referensi_bukti')->nullable();
            $table->integer('populasi_universe')->default(0);
            $table->integer('jumlah_diuji')->default(0);
            $table->integer('jumlah_anomali')->default(0);
            $table->decimal('rasio_anomali', 5, 2)->default(0);
            $table->decimal('nominal_sistem', 15, 2)->default(0);
            $table->decimal('nominal_dokumen_bukti', 15, 2)->default(0);
            $table->decimal('selisih', 15, 2)->default(0);
            $table->decimal('absolut_selisih', 15, 2)->default(0);
            $table->string('frekuensi_pola')->nullable();
            $table->string('pihak_user_terkait_awal')->nullable();
            $table->text('prosedur_dan_analisis')->nullable();
            $table->text('fakta_hasil_uji')->nullable();
            $table->text('klarifikasi_terbatas')->nullable();
            $table->string('bukti_klarifikasi')->nullable();
            $table->text('keterbatasan')->nullable();
            $table->text('simpulan_uji')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop09_kka_pemeriksaan_awals');
    }
};