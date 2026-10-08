<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onsite_permintaan_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('onsite_visit_id');
            $table->string('nomor_surat')->nullable(); // auto-generate
            $table->date('tanggal_surat');
            $table->string('kepada'); // nama pejabat KCP/KCPLK
            $table->string('jabatan_kepada')->nullable();
            $table->text('catatan')->nullable(); // catatan tambahan
            $table->timestamps();

            $table->foreign('onsite_visit_id')->references('id')->on('onsite_visits')->onDelete('cascade');
        });

        Schema::create('onsite_permintaan_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permintaan_id');
            $table->integer('urut');
            $table->string('jenis_dokumen'); // misal: Laporan Kas Harian
            $table->string('periode_data')->nullable(); // misal: September 2026
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('permintaan_id')->references('id')->on('onsite_permintaan_data')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onsite_permintaan_items');
        Schema::dropIfExists('onsite_permintaan_data');
    }
};
