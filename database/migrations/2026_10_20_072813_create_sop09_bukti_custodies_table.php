<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop09_bukti_custodies', function (Blueprint $table) {
            $table->id();
            $table->string('kode_bukti')->unique();
            $table->string('indikasi_id');
            $table->string('unit')->nullable();
            $table->string('jenis_bukti')->nullable();
            $table->text('deskripsi_relevansi')->nullable();
            $table->string('sumber_perolehan')->nullable();
            $table->string('periode_no_referensi')->nullable();
            $table->date('tanggal_diperoleh')->nullable();
            $table->string('waktu')->nullable();
            $table->string('asli_salinan')->default('ASLI');
            $table->string('lokasi_penyimpanan')->nullable();
            $table->string('klasifikasi_akses')->default('TERBATAS');
            $table->string('diperoleh_oleh')->nullable();
            $table->text('catatan_integritas')->nullable();
            $table->string('hash_checksum_identitas_file')->nullable();
            $table->string('pemegang_saat_ini')->nullable();
            $table->string('tindakan_terakhir')->nullable();
            $table->date('tanggal_tindakan')->nullable();
            $table->string('tujuan_serah_terima')->nullable();
            $table->string('akses_disetujui_oleh')->nullable();
            $table->string('status_integritas')->default('TERJAGA');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop09_bukti_custodies');
    }
};