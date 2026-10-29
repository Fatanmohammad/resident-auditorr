<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Kalender Laporan (WP-RA-08-A | 17 Kolom: A s.d. Q)
        Schema::create('sop08_kalender_laporans', function (Blueprint $table) {
            $table->id();
            $table->string('report_id', 50)->unique(); // Kolom A: LAP-YYYYMM-001
            $table->date('periode'); // Kolom B: YYYY-MM-01
            $table->string('kode_unit', 10); // Kolom C
            $table->string('nama_unit', 150); // Kolom D
            $table->string('resident_auditor', 150)->nullable(); // Kolom E
            $table->string('unit_ra', 150)->default('Bagian Resident Auditor'); // Kolom F
            $table->enum('wajib_lapor', ['YA', 'TIDAK'])->default('YA'); // Kolom G
            $table->date('jatuh_tempo'); // Kolom H (WORKDAY hari kerja ke-5 bulan berikutnya)
            $table->date('tanggal_submit')->nullable(); // Kolom I
            $table->integer('hari_terlambat')->default(0); // Kolom J
            $table->string('ketepatan', 50)->default('BELUM JATUH TEMPO'); // Kolom K: TEPAT WAKTU, TERLAMBAT, BELUM MASUK, BELUM JATUH TEMPO, TIDAK WAJIB
            $table->decimal('kelengkapan', 5, 2)->default(0.00); // Kolom L: 0.00 s.d. 100.00%
            $table->string('status_review', 50)->default('BELUM DIREVIEW'); // Kolom M
            $table->string('status_final', 50)->default('DRAFT'); // Kolom N: DRAFT, APPROVED/FINAL, dsb
            $table->decimal('quality_score', 5, 2)->default(0.00); // Kolom O
            $table->enum('masuk_konsolidasi', ['YA', 'BELUM'])->default('BELUM'); // Kolom P
            $table->text('catatan')->nullable(); // Kolom Q
            $table->timestamps();

            $table->index(['periode', 'kode_unit']);
        });

        // 2. Checklist Laporan (WP-RA-08-B | 17 Kolom: A s.d. Q)
        Schema::create('sop08_checklist_laporans', function (Blueprint $table) {
            $table->id();
            $table->string('report_id', 50)->unique(); // Kolom A
            
            // 12 Komponen Kelengkapan (Kolom B s.d. M)
            // Nilai: 'LENGKAP', 'BELUM LENGKAP', 'TIDAK RELEVAN'
            $table->string('komp_identitas_cutoff', 30)->default('BELUM LENGKAP'); // B
            $table->string('komp_ringkasan_eksekutif', 30)->default('BELUM LENGKAP'); // C
            $table->string('komp_rekap_kegiatan', 30)->default('BELUM LENGKAP'); // D
            $table->string('komp_coverage_sampel', 30)->default('BELUM LENGKAP'); // E
            $table->string('komp_exception_temuan', 30)->default('BELUM LENGKAP'); // F
            $table->string('komp_tindak_lanjut', 30)->default('BELUM LENGKAP'); // G
            $table->string('komp_fraud_insidentil', 30)->default('BELUM LENGKAP'); // H
            $table->string('komp_kendala', 30)->default('BELUM LENGKAP'); // I
            $table->string('komp_rencana_berikut', 30)->default('BELUM LENGKAP'); // J
            $table->string('komp_referensi_wp', 30)->default('BELUM LENGKAP'); // K
            $table->string('komp_lampiran_bukti', 30)->default('BELUM LENGKAP'); // L
            $table->string('komp_konsistensi_angka', 30)->default('BELUM LENGKAP'); // M

            // Kolom Perhitungan Otomatis
            $table->decimal('persen_lengkap', 5, 2)->default(0.00); // Kolom N (0 - 100%)
            $table->integer('jumlah_gap')->default(12); // Kolom O
            $table->string('status_kesiapan', 50)->default('BELUM LENGKAP'); // Kolom P: SIAP REVIEW / BELUM LENGKAP
            $table->text('catatan')->nullable(); // Kolom Q
            $table->timestamps();

            $table->foreign('report_id')->references('report_id')->on('sop08_kalender_laporans')->cascadeOnDelete();
        });

        // 3. Narasi Laporan (7 Kolom: A s.d. G)
        Schema::create('sop08_narasi_laporans', function (Blueprint $table) {
            $table->id();
            $table->string('report_id', 50)->unique(); // Kolom A
            $table->text('ringkasan_eksekutif')->nullable(); // Kolom B
            $table->text('hasil_utama')->nullable(); // Kolom C
            $table->text('analisis_risiko_tren')->nullable(); // Kolom D
            $table->text('kendala')->nullable(); // Kolom E
            $table->text('rencana_bulan_berikut')->nullable(); // Kolom F
            $table->text('arahan_diperlukan')->nullable(); // Kolom G
            $table->timestamps();

            $table->foreign('report_id')->references('report_id')->on('sop08_kalender_laporans')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop08_narasi_laporans');
        Schema::dropIfExists('sop08_checklist_laporans');
        Schema::dropIfExists('sop08_kalender_laporans');
    }
};
