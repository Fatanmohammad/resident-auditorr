<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onsite_kka_findings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('onsite_visit_id');
            $table->unsignedBigInteger('onsite_population_id'); // baris D (debit) yang dipilih

            $table->string('kode_unit');
            $table->string('sample_id')->nullable(); // SMP-ONS-TLR-YYYYMMDD-{tgl}|{arsip}

            // Data transaksi (copy dari populasi untuk kemudahan tampil)
            $table->date('tgl_tx')->nullable();
            $table->string('no_arsip')->nullable();
            $table->text('ket_tx')->nullable();
            $table->decimal('jumlah_tx', 20, 2)->default(0);
            $table->string('kd_user')->nullable();
            $table->integer('stable_score')->default(0);

            // Jenis & prioritas sampling
            $table->enum('jenis_sampling', ['Mandatory', 'Certainty', 'Targeted', 'Initial']);
            $table->tinyInteger('prioritas')->default(5); // 1=Mandatory, 2=Certainty, 3=Targeted, 5=Initial
            $table->string('alasan_sampling')->nullable(); // misal: "Tunai Besar", "Reversal", dll

            // --- INPUT RA ---
            $table->text('hasil_uji')->nullable();
            $table->text('bukti_referensi')->nullable();
            $table->integer('skor_dampak')->nullable();
            $table->integer('skor_kemungkinan')->nullable();
            $table->enum('risk_level', ['Low', 'Moderate', 'High'])->nullable();
            $table->text('simpulan_ra')->nullable();
            $table->date('tanggal_ditemukan')->nullable();

            // --- INPUT ADMIN ---
            $table->enum('status_review', ['Belum Direview', 'Revisi', 'Approved'])->default('Belum Direview');
            $table->text('catatan_reviewer')->nullable();

            $table->timestamps();

            $table->foreign('onsite_visit_id')->references('id')->on('onsite_visits')->onDelete('cascade');
            $table->foreign('onsite_population_id')->references('id')->on('onsite_populations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onsite_kka_findings');
    }
};
