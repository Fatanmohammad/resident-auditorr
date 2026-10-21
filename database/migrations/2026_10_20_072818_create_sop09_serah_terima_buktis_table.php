<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop09_serah_terima_buktis', function (Blueprint $table) {
            $table->id();
            $table->string('handover_id')->unique();
            $table->string('indikasi_id');
            $table->string('kode_bukti');
            $table->text('deskripsi_bukti')->nullable();
            $table->string('asli_salinan')->default('ASLI');
            $table->string('diserahkan_oleh')->nullable();
            $table->string('diterima_oleh')->nullable();
            $table->string('jabatan_unit_penerima')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('waktu')->nullable();
            $table->string('metode')->nullable();
            $table->string('kondisi_integritas')->default('BAIK');
            $table->string('pembatasan_akses')->default('YA');
            $table->string('konfirmasi_penerimaan')->default('YA');
            $table->text('arahan_tindak_lanjut')->nullable();
            $table->string('status')->default('SELESAI');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop09_serah_terima_buktis');
    }
};