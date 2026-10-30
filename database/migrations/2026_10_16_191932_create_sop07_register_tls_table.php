<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop07_register_tls', function (Blueprint $table) {
            $table->id();
            $table->string('tl_id')->unique(); // Contoh: TL-2026-001
            $table->string('sumber_tl')->default('SKAI'); // Regulator, SKAI, Auditor Eksternal, Resident Auditor, Manajemen/Komite
            $table->string('kode_unit')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('nomor_laporan')->nullable();
            $table->date('tanggal_laporan')->nullable();
            $table->text('rekomendasi_temuan')->nullable();
            $table->text('komitmen_auditee')->nullable();
            $table->date('target_penyelesaian')->nullable();
            $table->string('prioritas')->default('MEDIUM'); // LOW, MEDIUM, HIGH
            $table->string('pic_auditee')->nullable();
            
            // Kolom Status & Workflow sesuai Master Excel SOP 07
            $table->string('kelengkapan')->default('VALID'); // VALID / PERBAIKI DATA
            $table->string('status_reminder')->default('NORMAL'); // NORMAL / DUE / OVERDUE
            $table->string('status_final')->default('OPEN'); // OPEN, UNDER REVIEW, INADEQUATE, OVERDUE, PROPOSED CLOSED, CLOSED, REOPENED
            $table->string('aging_bucket')->default('LANCAR'); // LANCAR, OVERDUE >30, dll
            $table->string('status_workflow')->default('MONITOR/REMINDER'); // PERBAIKI DATA, REVIEW DUPLIKAT, MONITOR/REMINDER, VERIFIKASI BUKTI, AJUKAN CLOSURE, PERLU ESKALASI, SELESAI
            $table->string('closure_authority')->default('SKAI/Owner Temuan');
            
            $table->date('tanggal_closed_final')->nullable();
            $table->text('catatan_reviewer')->nullable();
            $table->string('exception_aktif')->default('Tidak');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop07_register_tls');
    }
};