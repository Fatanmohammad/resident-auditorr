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
        Schema::create('program_kerja_data', function (Blueprint $table) {
            $table->id();

            // Relasi ke Assignment Scope & Register Trigger
            $table->foreignId('assignment_scope_id')->constrained('assignment_scopes')->onDelete('cascade');
            $table->foreignId('register_trigger_id')->constrained('register_triggers')->onDelete('cascade');
            $table->string('kode_unit', 20)->index(); // Scoping cepat

            // Kolom A s.d. C: Identitas Penugasan
            $table->string('visit_id', 40)->index();
            $table->string('trigger_id', 35)->index();
            $table->string('area', 100)->index();

            // Kolom D s.d. I: Standar Audit dari Master_Parameter
            $table->string('kka_tujuan', 100);
            $table->text('tujuan_uji');
            $table->text('kriteria_utama');
            $table->text('data_bukti_minimum');
            $table->text('metode_uji');
            $table->text('metode_sampling');

            // Kolom J & K: Formula Sampling Terarah
            $table->integer('sampel_pemicu')->default(1);
            $table->integer('sampel_pembanding')->default(1);

            // Kolom L s.d. P: Pelaksanaan Kerja Lapangan & Bukti
            $table->text('langkah_tambahan_ra')->nullable();
            $table->date('cutoff_data')->nullable();
            $table->date('tanggal_permintaan')->nullable();
            $table->date('tanggal_diterima')->nullable();
            $table->text('referensi_bukti_awal')->nullable();

            // Kolom Q: Status Kesiapan (BELUM SIAP / BELUM DIMINTA / MENUNGGU DATA / SIAP UJI)
            $table->string('status_kesiapan', 50)->default('BELUM SIAP')->index();

            // Kolom R: Catatan Akses/Data
            $table->text('catatan_akses_data')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_kerja_data');
    }
};
