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
        Schema::create('sop08_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->string('feedback_id', 30)->unique(); // Kolom A: Format FB-YYYYMM-XXX
            $table->string('report_id', 50);             // Kolom B: Referensi Kalender Laporan
            $table->string('kode_unit', 20)->nullable(); // Untuk isolasi cabang RA
            $table->string('nama_unit', 150)->nullable();
            $table->date('tanggal');                     // Kolom C: Tanggal feedback
            $table->string('diberikan_kepada', 150);     // Kolom D: RA / Unit penerima
            $table->string('jenis_feedback', 100);       // Kolom E: Kategori feedback
            $table->text('uraian_feedback');             // Kolom F: Catatan/arahan manajemen
            $table->text('tindakan_perbaikan')->nullable(); // Kolom G: Tanggapan / rencana RA
            $table->string('pic', 100)->nullable();      // Kolom H: Penanggung jawab
            $table->date('target')->nullable();          // Kolom I: Tanggal target penyelesaian
            $table->string('status', 50)->default('OPEN'); // Kolom J: OPEN, IN PROGRESS, MENUNGGU VERIFIKASI, CLOSED
            $table->text('bukti')->nullable();           // Kolom K: Referensi bukti tindak lanjut
            $table->date('tanggal_selesai')->nullable(); // Kolom L: Tanggal selesai perbaikan
            $table->string('verifikasi_reviewer', 100)->default('BELUM DIVERIFIKASI'); // Kolom M: Khusus Admin/Reviewer
            $table->text('catatan')->nullable();         // Kolom N: Catatan tambahan

            $table->timestamps();

            $table->foreign('report_id')
                ->references('report_id')
                ->on('sop08_kalender_laporans')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sop08_feedbacks');
    }
};
