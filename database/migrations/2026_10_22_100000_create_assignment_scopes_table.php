<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assignment_scopes', function (Blueprint $table) {
            $table->id();

            // Kolom A: Visit ID (Format: RTV-{KodeUnit3Digit}-{YYYYMMDD}-{001})
            $table->string('visit_id', 40)->unique()->index();

            // Kolom B: Trigger ID & Foreign Key ke register_triggers
            $table->foreignId('register_trigger_id')->constrained('register_triggers')->onDelete('cascade');
            $table->string('trigger_id', 35)->index();

            // Kolom C s.d. J: Kolom Otomatis dari Register_Trigger
            $table->string('kode_unit', 20)->index();
            $table->string('nama_unit', 150)->nullable();
            $table->string('area', 100)->index();
            $table->string('risk_level', 30)->nullable(); // Reassessment
            $table->string('keputusan_final', 100)->index();
            $table->string('kka_tujuan', 100);
            $table->text('scope_tujuan_awal')->nullable();
            $table->text('periode_populasi')->nullable();

            // Kolom K s.d. R: Input Tanggal, Pelaksana & Otorisasi
            $table->date('tanggal_visit')->nullable()->index();
            $table->date('mulai_aktual')->nullable();
            $table->date('selesai_aktual')->nullable();
            $table->string('assigned_ra_sumber', 150)->nullable();
            $table->string('ra_pelaksana', 150)->nullable();
            $table->string('reviewer', 150)->nullable();
            $table->string('referensi_otorisasi', 150)->nullable();
            $table->string('klasifikasi', 100)->default('Risk Trigger Visit Terarah');

            // Kolom S: Status Assignment (SIAP DILAKSANAKAN / BELUM LENGKAP / BUKAN KEPUTUSAN VISIT)
            $table->string('status_assignment', 50)->default('BELUM LENGKAP')->index();

            // Kolom T s.d. V: Batas Scope, Kriteria Selesai & Perlu Data Unit
            $table->text('batas_scope')->nullable();
            $table->text('kriteria_selesai')->nullable();
            $table->string('perlu_data_unit', 100)->default('Ya - permintaan terbatas sesuai scope');

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_scopes');
    }
};
