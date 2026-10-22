<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Cek dulu apakah tabelnya sudah ada. Jika belum, baru dibuat.
        if (!Schema::hasTable('surprise_audit_assignments')) {
            Schema::create('surprise_audit_assignments', function (Blueprint $table) {
                $table->id();
                $table->string('visit_id')->unique();
                $table->string('kode_unit');
                $table->string('nama_unit');
                $table->dateTime('tanggal_rencana');
                $table->dateTime('tanggal_mulai_aktual')->nullable();
                $table->dateTime('tanggal_selesai_aktual')->nullable();
                $table->string('resident_auditor');
                $table->string('reviewer');
                $table->string('dasar_penugasan')->default('Informasi Khusus');
                $table->string('nomor_assignment');
                $table->string('pemberi_tugas')->default('Pimpinan SKAI');
                $table->text('tujuan')->nullable();
                $table->string('klasifikasi_kerahasiaan')->default('Rahasia - Need to Know');
                $table->json('ruang_lingkup_area')->nullable();
                $table->string('status_assignment')->default('SIAP DILAKSANAKAN');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('surprise_audit_assignments');
    }
};