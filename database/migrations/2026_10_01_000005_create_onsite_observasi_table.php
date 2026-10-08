<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Header observasi per kunjungan
        Schema::create('onsite_observasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onsite_visit_id')->constrained('onsite_visits')->cascadeOnDelete();
            $table->integer('jumlah_atm')->default(0);
            $table->string('ra_pelaksana')->nullable();
            $table->date('tanggal_observasi')->nullable();
            $table->enum('status', ['Draft', 'Selesai'])->default('Draft');
            $table->timestamps();
        });

        // Item checklist per observasi
        Schema::create('onsite_observasi_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onsite_observasi_id')->constrained('onsite_observasi')->cascadeOnDelete();
            $table->string('procedure_id');
            $table->string('area');
            $table->string('objek');
            $table->text('kriteria');
            $table->enum('tipe', ['gedung', 'atm'])->default('gedung');
            $table->string('atm_slot')->nullable(); // ATM 01, ATM 02, dst
            $table->integer('urut')->default(0);

            // Input RA
            $table->text('kondisi_aktual')->nullable();
            $table->enum('hasil_observasi', ['Sesuai', 'Tidak Sesuai', 'N/A'])->nullable();
            $table->text('uraian_ketidaksesuaian')->nullable();
            $table->text('penyebab')->nullable();
            $table->text('dampak')->nullable();
            $table->tinyInteger('impact')->nullable();
            $table->tinyInteger('likelihood')->nullable();
            $table->string('referensi_bukti')->nullable();

            // Kalkulasi otomatis
            $table->tinyInteger('risk_score')->nullable();
            $table->string('risk_level')->nullable();

            // Review
            $table->enum('status_review', ['Belum Direview', 'Revisi', 'Approved'])->default('Belum Direview');
            $table->text('catatan_reviewer')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onsite_observasi_items');
        Schema::dropIfExists('onsite_observasi');
    }
};
