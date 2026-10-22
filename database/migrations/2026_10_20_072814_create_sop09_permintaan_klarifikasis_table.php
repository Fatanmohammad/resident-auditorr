<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop09_permintaan_klarifikasis', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->unique();
            $table->string('indikasi_id');
            $table->string('unit')->nullable();
            $table->string('jenis')->nullable();
            $table->string('diminta_kepada')->nullable();
            $table->string('jabatan_role')->nullable();
            $table->date('tanggal_permintaan')->nullable();
            $table->text('objek_data_pertanyaan')->nullable();
            $table->text('tujuan_dan_batas_ruang_lingkup')->nullable();
            $table->string('pembatasan_kerahasiaan')->default('YA');
            $table->date('due_date')->nullable();
            $table->date('tanggal_respons')->nullable();
            $table->text('ringkasan_respons')->nullable();
            $table->string('referensi_bukti')->nullable();
            $table->string('kelengkapan')->default('LENGKAP');
            $table->string('overdue')->default('TIDAK');
            $table->string('tindak_lanjut')->nullable();
            $table->string('status')->default('OPEN');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop09_permintaan_klarifikasis');
    }
};