<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop09_register_indikasis', function (Blueprint $table) {
            $table->id();
            $table->string('indikasi_id')->unique();
            $table->date('tanggal_diterima')->nullable();
            $table->string('jam')->nullable();
            $table->string('kode_unit')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('ra_id')->nullable();
            $table->string('nama_ra')->nullable();
            $table->string('sumber_indikasi')->nullable();
            $table->string('referensi_sop_sumber')->nullable();
            $table->string('area')->nullable();
            $table->string('media_sarana')->nullable();
            $table->string('pemberi_informasi_role')->nullable();
            $table->text('fakta_awal_terverifikasi')->nullable();
            $table->text('dugaan_belum_terverifikasi')->nullable();
            $table->string('red_flag_utama')->nullable();
            $table->decimal('nominal_awal', 15, 2)->default(0);
            $table->string('nasabah_terdampak')->default('TIDAK');
            $table->string('pihak_kunci_pimpinan')->default('TIDAK');
            $table->string('risiko_bukti_hilang')->default('SEDANG');
            $table->string('sensitivitas_reputasi')->default('SEDANG');
            $table->string('konflik_kepentingan_ra')->default('TIDAK');
            $table->string('klasifikasi_kerahasiaan')->default('RAHASIA');
            $table->string('status_triase')->default('DRAFT');
            $table->string('keputusan_final')->nullable();
            $table->string('status_kasus')->default('OPEN');
            $table->text('catatan')->nullable();
            $table->string('jalur_masuk')->nullable();
            $table->string('no_tanggal_arahan_skai')->nullable();
            $table->text('ruang_lingkup_pendalaman')->nullable();
            $table->date('batas_waktu_arahan')->nullable();
            $table->string('status_kelengkapan_arahan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop09_register_indikasis');
    }
};