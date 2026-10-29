<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master RA (WP-RA-08-N | 9 Kolom: A s.d. I)
        Schema::create('sop08_master_ras', function (Blueprint $table) {
            $table->id();
            $table->string('ra_id', 30)->unique(); // Kolom A: Kode unik RA (misal RA-001)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_ra', 150); // Kolom B: Nama lengkap RA
            $table->string('kode_kc_penempatan', 10); // Kolom C: Kode KC 3 digit
            $table->string('nama_kc', 150)->nullable(); // Kolom D: Otomatis dari Master_Unit
            $table->string('unit_ra', 150)->default('Bagian Resident Auditor'); // Kolom E
            $table->string('status', 20)->default('Aktif'); // Kolom F: Aktif / Non-Aktif
            $table->date('tanggal_mulai')->nullable(); // Kolom G: Tanggal Mulai Penugasan
            $table->date('tanggal_selesai')->nullable(); // Kolom H: Tanggal Selesai
            $table->text('catatan')->nullable(); // Kolom I: Catatan
            $table->timestamps();

            $table->index('kode_kc_penempatan');
            $table->index('status');
        });

        // 2. Log Harian RA (WP-RA-08-O | 17 Kolom: A s.d. Q)
        Schema::create('sop08_log_harian_ras', function (Blueprint $table) {
            $table->id();
            $table->string('log_id', 40)->unique(); // Kolom A: LOG-YYYYMMDD-XXX
            $table->date('tanggal'); // Kolom B: Tanggal pelaksanaan aktivitas
            $table->string('ra_id', 30); // Kolom C: Referensi ke Master_RA
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_ra', 150); // Kolom D: Nama RA
            $table->string('kode_unit', 10); // Kolom E: Kode Unit
            $table->string('nama_unit', 150); // Kolom F: Nama Unit
            $table->string('status_hari', 30); // Kolom G: KERJA, CUTI, SAKIT, DINAS LUAR, PELATIHAN
            $table->string('sumber_sop', 50)->nullable(); // Kolom H: SOP 03, SOP 04, SOP 05, SOP 06, SOP 07, SOP 08, Non-Audit
            $table->string('jenis_aktivitas', 255)->nullable(); // Kolom I: Uraian aktivitas
            $table->string('area', 100)->nullable(); // Kolom J: 13 Area Risiko Standar / Lainnya
            $table->string('output_referensi_wp', 255)->nullable(); // Kolom K: KKA / Visit ID / Laporan
            $table->string('status_penyelesaian', 30)->nullable(); // Kolom L: COMPLETED, IN PROGRESS, PENDING
            $table->string('hasil_review', 30)->default('BELUM DIREVIEW'); // Kolom M: BELUM DIREVIEW, MEMADAI, PERLU PERBAIKAN, TIDAK MEMADAI
            $table->string('reviewer', 150)->nullable(); // Kolom N: Nama Reviewer
            $table->date('tanggal_review')->nullable(); // Kolom O: Tanggal Review
            $table->integer('jumlah_item')->default(0); // Kolom P: Jumlah item / sampel diuji
            $table->text('catatan')->nullable(); // Kolom Q: Catatan tambahan
            $table->timestamps();

            $table->index(['tanggal', 'ra_id']);
            $table->index('kode_unit');
            $table->index('status_hari');
            $table->index('hasil_review');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop08_log_harian_ras');
        Schema::dropIfExists('sop08_master_ras');
    }
};
