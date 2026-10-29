<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rekap Kegiatan (WP-RA-08-D | 20 Kolom: A s.d. T - Multi-Row per Report ID)
        Schema::create('sop08_rekap_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string('source_export_id', 60); // Kolom A: ID unik sumber/baris
            $table->string('report_id', 50); // Kolom B: Relasi ke Kalender Laporan
            $table->date('periode'); // Kolom C (Otomatis dari Kalender Laporan)
            $table->string('kode_unit', 10); // Kolom D
            $table->string('nama_unit', 150); // Kolom E
            $table->string('resident_auditor', 150)->nullable(); // Kolom F
            $table->string('sumber_sop', 30); // Kolom G: SOP 03, SOP 04, SOP 05, SOP 06, SOP 07
            $table->string('jenis_kegiatan', 60); // Kolom H: Daily Review, Offsite Review, Onsite Visit, Surprise Audit, Risk Trigger Visit, Monitoring Tindak Lanjut
            $table->string('area', 100); // Kolom I: 13 Area Risiko standar
            $table->integer('rencana')->default(0); // Kolom J
            $table->integer('realisasi')->default(0); // Kolom K
            $table->integer('populasi')->default(0); // Kolom L
            $table->integer('sampel_terpilih')->default(0); // Kolom M
            $table->integer('sudah_diuji')->default(0); // Kolom N
            $table->integer('exception')->default(0); // Kolom O
            $table->integer('finding')->default(0); // Kolom P
            $table->integer('high_risk')->default(0); // Kolom Q
            $table->string('referensi_wp_kka', 100)->nullable(); // Kolom R
            $table->string('status_kegiatan', 50)->default('SELESAI'); // Kolom S
            $table->text('catatan')->nullable(); // Kolom T
            $table->timestamps();

            $table->foreign('report_id')->references('report_id')->on('sop08_kalender_laporans')->cascadeOnDelete();
            $table->index(['report_id', 'jenis_kegiatan']);
        });

        // 2. Rekap Isu & Tindak Lanjut (WP-RA-08-E | 23 Kolom: A s.d. W - Multi-Row per Report ID)
        Schema::create('sop08_rekap_isu_tls', function (Blueprint $table) {
            $table->id();
            $table->string('issue_id', 60); // Kolom A: Auto/Unik misal ISU-202609-001
            $table->string('report_id', 50); // Kolom B: Relasi ke Kalender Laporan
            $table->date('periode'); // Kolom C
            $table->string('kode_unit', 10); // Kolom D
            $table->string('nama_unit', 150); // Kolom E
            $table->string('resident_auditor', 150)->nullable(); // Kolom F
            $table->string('sumber_sop', 30); // Kolom G: SOP 03, SOP 04, SOP 05, SOP 06, SOP 07
            $table->string('referensi_sumber', 100)->nullable(); // Kolom H: No KKA/Visit ID/WP ID
            $table->string('area', 100); // Kolom I: 13 Area Risiko standar
            $table->enum('jenis_isu', ['EXCEPTION', 'FINDING', 'INDIKASI FRAUD'])->default('EXCEPTION'); // Kolom J
            $table->enum('risk_level', ['HIGH', 'MEDIUM', 'LOW'])->default('MEDIUM'); // Kolom K
            $table->text('uraian_isu'); // Kolom L
            $table->text('sebab_awal')->nullable(); // Kolom M
            $table->text('dampak_risiko')->nullable(); // Kolom N
            $table->text('rekomendasi')->nullable(); // Kolom O
            $table->string('pic', 150)->nullable(); // Kolom P
            $table->date('target_tl')->nullable(); // Kolom Q
            $table->enum('status_tl', ['OPEN', 'IN PROGRESS', 'OVERDUE', 'CLOSED'])->default('OPEN'); // Kolom R
            $table->integer('aging_overdue')->default(0); // Kolom S: Rumus Otomatis
            $table->enum('berulang', ['YA', 'TIDAK'])->default('TIDAK'); // Kolom T
            $table->decimal('nilai_dampak', 16, 2)->default(0.00); // Kolom U
            $table->enum('flag_signifikan', ['YA', 'TIDAK'])->default('TIDAK'); // Kolom V: Rumus Otomatis
            $table->string('jalur_tindak_lanjut', 50)->default('MONITOR BULANAN'); // Kolom W: Rumus Otomatis
            $table->timestamps();

            $table->foreign('report_id')->references('report_id')->on('sop08_kalender_laporans')->cascadeOnDelete();
            $table->index(['report_id', 'jenis_isu', 'risk_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop08_rekap_isu_tls');
        Schema::dropIfExists('sop08_rekap_kegiatans');
    }
};
