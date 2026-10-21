<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onsite_kka_teller_kas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onsite_visit_id')->constrained('onsite_visits')->onDelete('cascade');
            $table->foreignId('onsite_population_id')->nullable()->constrained('onsite_populations')->onDelete('cascade');
            
            // Identitas Sampel
            $table->string('sample_id')->unique();
            $table->date('tgl_tx');
            $table->string('no_arsip');
            $table->text('ket_tx')->nullable();
            $table->decimal('jumlah_tx', 18, 2)->default(0);
            $table->string('kd_user')->nullable();
            $table->string('jenis_sampling'); // Mandatory, Certainty, Targeted, Initial
            $table->integer('prioritas')->default(5);
            $table->string('alasan_sampling')->nullable();

            // 10 Atribut Pengujian Teller/Kas (Nilai: Sesuai, Tidak Sesuai, Tidak Berlaku)
            $table->string('attr_bukti_asli')->default('Sesuai');
            $table->string('attr_sesuai_cbs')->default('Sesuai');
            $table->string('attr_otorisasi')->default('Sesuai');
            $table->string('attr_identitas')->default('Sesuai');
            $table->string('attr_slip_denominasi')->default('Sesuai');
            $table->string('attr_cutoff')->default('Sesuai');
            $table->string('attr_override_reversal')->default('Sesuai');
            $table->string('attr_tunai_besar')->default('Sesuai');
            $table->string('attr_posting_gl')->default('Sesuai');
            $table->string('attr_dokumentasi')->default('Sesuai');

            // Exception & Penilaian Risiko
            $table->string('status_uji')->default('Belum Diuji'); // Sesuai / Ada Exception
            $table->string('jenis_exception')->nullable();
            $table->text('kondisi')->nullable();
            $table->text('kriteria')->nullable();
            $table->text('sebab')->nullable();
            $table->text('dampak')->nullable();
            $table->integer('skor_dampak')->default(1);
            $table->integer('skor_kemungkinan')->default(1);
            $table->integer('skor_risiko')->default(1); // dampak x kemungkinan
            $table->string('risk_level')->default('Low'); // Low, Moderate, High, Kritis
            $table->string('critical_trigger')->default('Tidak'); // Ya / Tidak

            // Klarifikasi & Review
            $table->string('perlu_klarifikasi')->default('Ya');
            $table->string('status_klarifikasi')->default('Draft');
            $table->text('simpulan_ra')->nullable();
            $table->text('catatan_reviewer')->nullable();
            $table->string('status_review')->default('Belum Direview'); // Belum Direview, Revisi, Approved

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onsite_kka_teller_kas');
    }
};