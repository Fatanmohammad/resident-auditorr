<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Review Laporan (WP-RA-08-F | 19 Kolom: A s.d. S)
        Schema::create('sop08_review_laporans', function (Blueprint $table) {
            $table->id();
            $table->string('report_id', 50)->unique(); // Kolom A
            $table->string('reviewer', 150)->nullable(); // Kolom B
            $table->date('tanggal_review')->nullable(); // Kolom C

            // 5 Kolom Angka Pokok Otomatis (Kolom D s.d. H)
            $table->integer('jumlah_kegiatan')->default(0); // Kolom D
            $table->integer('jumlah_isu')->default(0); // Kolom E
            $table->integer('high_risk')->default(0); // Kolom F
            $table->integer('tl_overdue')->default(0); // Kolom G
            $table->decimal('persen_kelengkapan', 5, 2)->default(0.00); // Kolom H (0 s.d. 100%)

            // 6 Kolom Penilaian Substansi oleh Reviewer (Kolom I s.d. N)
            // Nilai standar: 'MEMADAI', 'PERLU PERBAIKAN', 'TIDAK MEMADAI'
            $table->string('akurasi_data', 30)->default('MEMADAI'); // Kolom I
            $table->string('konsistensi', 30)->default('MEMADAI'); // Kolom J
            $table->string('kualitas_analisis', 30)->default('MEMADAI'); // Kolom K
            $table->string('bukti_pendukung', 30)->default('MEMADAI'); // Kolom L
            $table->string('tindak_lanjut', 30)->default('MEMADAI'); // Kolom M
            $table->string('eskalasi', 30)->default('MEMADAI'); // Kolom N

            // Rumus Otomatis Skor Review, Hasil Otomatis, Override & Status Review Final (Kolom O s.d. S)
            $table->integer('skor_review')->default(0); // Kolom O
            $table->string('hasil_otomatis', 50)->default('KOREKSI SUBSTANSI'); // Kolom P
            $table->string('override', 50)->nullable(); // Kolom Q
            $table->text('alasan_override')->nullable(); // Kolom R
            $table->string('status_review_final', 50)->default('KOREKSI SUBSTANSI'); // Kolom S

            $table->timestamps();

            $table->foreign('report_id')->references('report_id')->on('sop08_kalender_laporans')->cascadeOnDelete();
        });

        // 2. Koreksi & Approval (WP-RA-08-G | 15 Kolom: A s.d. O)
        Schema::create('sop08_koreksi_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('report_id', 50)->unique(); // Kolom A
            $table->string('hasil_review', 50)->default('BELUM DIREVIEW'); // Kolom B
            $table->string('jenis_koreksi', 50)->default('TIDAK ADA'); // Kolom C: TIDAK ADA, ADMINISTRATIF/MINOR, ESKALASI, SUBSTANSI
            $table->text('catatan_reviewer')->nullable(); // Kolom D
            $table->date('tanggal_permintaan')->nullable(); // Kolom E
            $table->date('batas_koreksi')->nullable(); // Kolom F (WORKDAY)
            $table->date('tanggal_resubmit')->nullable(); // Kolom G (Diisi RA)
            $table->text('perbaikan_ra')->nullable(); // Kolom H (Diisi RA)
            $table->date('tanggal_review_ulang')->nullable(); // Kolom I
            $table->string('keputusan_approval', 50)->nullable(); // Kolom J: APPROVED/FINAL, REVISION REQUIRED, REJECTED FOR RESUBMISSION
            $table->string('approved_by', 150)->nullable(); // Kolom K
            $table->date('tanggal_approval')->nullable(); // Kolom L
            $table->string('versi_final', 50)->default('v1.0 Final'); // Kolom M
            $table->string('status_final', 50)->default('UNDER REVIEW'); // Kolom N
            $table->integer('hari_terlambat_koreksi')->default(0); // Kolom O

            $table->timestamps();

            $table->foreign('report_id')->references('report_id')->on('sop08_kalender_laporans')->cascadeOnDelete();
        });

        // 3. Matriks Isu Signifikan & Rekomendasi Eskalasi (WP-RA-08-H | 17 Kolom: A s.d. Q)
        Schema::create('sop08_isu_signifikans', function (Blueprint $table) {
            $table->id();
            $table->string('issue_id', 60)->unique(); // Kolom A
            
            // 11 Kolom Otomatis dari Rekap Isu TL (Kolom B s.d. L)
            $table->string('report_id', 50); // Kolom B
            $table->date('periode'); // Kolom C
            $table->string('nama_unit', 150); // Kolom D
            $table->string('area', 100); // Kolom E
            $table->string('jenis_isu', 50); // Kolom F
            $table->string('risk_level', 30); // Kolom G
            $table->text('uraian_isu'); // Kolom H
            $table->string('status_tl', 30); // Kolom I
            $table->integer('aging_overdue')->default(0); // Kolom J
            $table->string('berulang', 10)->default('TIDAK'); // Kolom K
            $table->string('jalur_sistem', 50)->default('MONITOR BULANAN'); // Kolom L

            // 5 Kolom Keputusan Eskalasi oleh Reviewer / Admin (Kolom M s.d. Q)
            $table->string('keputusan_reviewer', 100)->nullable(); // Kolom M
            $table->string('tujuan_eskalasi', 150)->nullable(); // Kolom N
            $table->date('tanggal_eskalasi')->nullable(); // Kolom O
            $table->string('referensi_bukti', 150)->nullable(); // Kolom P
            $table->text('arahan_tindak_lanjut')->nullable(); // Kolom Q

            $table->timestamps();

            $table->foreign('report_id')->references('report_id')->on('sop08_kalender_laporans')->cascadeOnDelete();
            $table->index(['report_id', 'risk_level']);
        });

        // 4. Distribusi, Arsip & Quality Score (WP-RA-08-I | 18 Kolom: A s.d. R)
        Schema::create('sop08_distribusi_qualities', function (Blueprint $table) {
            $table->id();
            $table->string('report_id', 50)->unique(); // Kolom A
            $table->date('tanggal_distribusi')->nullable(); // Kolom B
            $table->text('penerima')->nullable(); // Kolom C
            $table->string('media_arsip', 150)->nullable(); // Kolom D
            $table->string('versi', 50)->default('v1.0 Final'); // Kolom E

            // 7 Komponen Pembentuk Skor Dasar (Kolom F s.d. L)
            $table->decimal('skor_tepat_waktu', 5, 2)->default(0.00); // Kolom F (Maks 20 Poin)
            $table->decimal('skor_kelengkapan', 5, 2)->default(0.00); // Kolom G (Maks 20 Poin)
            $table->decimal('skor_akurasi', 5, 2)->default(0.00); // Kolom H (Maks 15 Poin)
            $table->decimal('skor_analisis', 5, 2)->default(0.00); // Kolom I (Maks 15 Poin)
            $table->decimal('skor_bukti', 5, 2)->default(0.00); // Kolom J (Maks 10 Poin)
            $table->decimal('skor_tindak_lanjut', 5, 2)->default(0.00); // Kolom K (Maks 10 Poin)
            $table->decimal('skor_eskalasi', 5, 2)->default(0.00); // Kolom L (Maks 10 Poin)

            // Skor Dasar, Penyesuaian Reviewer, Quality Score & Kategori (Kolom M s.d. R)
            $table->decimal('skor_dasar', 5, 2)->default(0.00); // Kolom M (SUM F:L)
            $table->decimal('penyesuaian_reviewer', 5, 2)->default(0.00); // Kolom N
            $table->decimal('quality_score', 5, 2)->default(0.00); // Kolom O (0 s.d. 100)
            $table->string('kategori', 50)->default('TIDAK MEMADAI'); // Kolom P: SANGAT BAIK, BAIK, PERLU PERBAIKAN, TIDAK MEMADAI
            $table->text('catatan_kpi')->nullable(); // Kolom Q
            $table->string('status_arsip', 50)->default('BELUM DIARSIP'); // Kolom R: TERARSIP LENGKAP, BELUM DIARSIP, DALAM PROSES

            $table->timestamps();

            $table->foreign('report_id')->references('report_id')->on('sop08_kalender_laporans')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop08_distribusi_qualities');
        Schema::dropIfExists('sop08_isu_signifikans');
        Schema::dropIfExists('sop08_koreksi_approvals');
        Schema::dropIfExists('sop08_review_laporans');
    }
};
