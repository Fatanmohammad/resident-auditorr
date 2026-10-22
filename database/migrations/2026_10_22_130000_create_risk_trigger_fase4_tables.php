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
        // 1. REGISTER KLARIFIKASI & BUKTI (16 Kolom Excel A s.d. P - Multi-Row per Visit ID)
        Schema::create('risk_trigger_klarifikasi_buktis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_scope_id')->constrained('assignment_scopes')->onDelete('cascade');
            $table->foreignId('register_trigger_id')->nullable()->constrained('register_triggers')->onDelete('set null');
            $table->string('kode_unit', 50)->index();
            $table->string('unit_kantor', 200);
            $table->string('visit_id', 100)->index(); // Kolom A
            $table->string('trigger_id', 100)->index(); // Kolom B
            $table->string('area', 100); // Kolom C

            // Kolom Input Klarifikasi & Bukti oleh RA (Kolom D s.d. N)
            $table->text('isu_exception'); // Kolom D
            $table->string('pihak_diklarifikasi', 150)->nullable(); // Kolom E
            $table->date('tanggal_permintaan')->nullable(); // Kolom F
            $table->text('pertanyaan_klarifikasi')->nullable(); // Kolom G
            $table->text('jawaban_auditee')->nullable(); // Kolom H
            $table->text('bukti_diminta')->nullable(); // Kolom I
            $table->text('bukti_diterima')->nullable(); // Kolom J (Uraian bukti)
            $table->string('lampiran_file', 255)->nullable(); // Kolom J (Upload path)
            $table->date('tanggal_diterima')->nullable(); // Kolom K
            $table->string('kecukupan_bukti', 50)->nullable(); // Kolom L (Memadai / Kurang Memadai / Tidak Ada Bukti)
            $table->text('analisis_ra')->nullable(); // Kolom M
            $table->string('status_klarifikasi', 50)->default('Open'); // Kolom N (Open / Dalam Proses / Closed)

            // Kolom Review Admin (Kolom O & P)
            $table->string('reviewer', 150)->nullable(); // Kolom O
            $table->text('catatan_review')->nullable(); // Kolom P

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 2. SIMPULAN, TINDAK LANJUT & CLOSURE (23 Kolom Excel A s.d. W - 1 Baris per Visit ID)
        Schema::create('risk_trigger_simpulan_tls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_scope_id')->constrained('assignment_scopes')->onDelete('cascade');
            $table->foreignId('register_trigger_id')->nullable()->constrained('register_triggers')->onDelete('set null');
            $table->string('kode_unit', 50)->index();
            $table->string('unit_kantor', 200);
            $table->string('visit_id', 100)->unique()->index(); // Kolom A
            $table->string('trigger_id', 100)->index(); // Kolom B
            $table->string('area', 100); // Kolom D (Kolom C is Unit/Kantor)
            $table->string('risk_level_awal', 50); // Kolom E

            // Kolom Otomatis dari KKA & Klarifikasi (F, G, H, I, K, L, S, T, U)
            $table->string('trigger_terbukti', 30)->nullable(); // Kolom F (dari KKA Induk Kolom L)
            $table->integer('jumlah_exception')->default(0); // Kolom G (COUNTIFS 8 KKA Area)
            $table->string('status_klarifikasi', 50)->default('TIDAK ADA'); // Kolom H (MASIH OPEN / TIDAK ADA / SELESAI)
            $table->text('simpulan_akhir')->nullable(); // Kolom I (dari KKA Induk Kolom Q, disesuaikan)
            $table->string('perlu_perluasan', 20)->default('Tidak'); // Kolom K (dari KKA Induk Kolom M)
            $table->text('scope_perluasan')->nullable(); // Kolom L (dari KKA Induk Kolom N)
            $table->string('referensi_kka_bukti', 200)->nullable(); // Kolom S ("{KKA Tujuan} | Klarifikasi_Bukti")
            $table->string('ra_pelaksana', 150)->nullable(); // Kolom T
            $table->string('reviewer', 150)->nullable(); // Kolom U

            // Kolom Input Tindak Lanjut, Eskalasi, & Pelaporan (J, M s.d. R)
            $table->string('klasifikasi_hasil', 50)->nullable(); // Kolom J (Temuan / Perlu Perluasan / Eskalasi / Tidak Terbukti / Selesai)
            $table->text('tindak_lanjut_awal')->nullable(); // Kolom M
            $table->string('pic_unit', 150)->nullable(); // Kolom N
            $table->date('due_date')->nullable(); // Kolom O
            $table->string('level_eskalasi', 50)->default('Tidak Perlu'); // Kolom P (Tidak Perlu / Pemimpin Cabang / Kadiv SKAI / Direksi / SOP 09)
            $table->string('tujuan_eskalasi', 150)->nullable(); // Kolom Q
            $table->string('laporan', 150)->nullable(); // Kolom R

            // Kolom Review Final & Status Closure (V & W)
            $table->string('status_review', 50)->default('DRAFT'); // Kolom V (DRAFT / REVISI / FINAL)
            $table->string('status_closure', 50)->default('BELUM DITUTUP'); // Kolom W (BELUM DITUTUP / TL BELUM LENGKAP / KLARIFIKASI BELUM SELESAI / CLOSED/DITERUSKAN)

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_trigger_simpulan_tls');
        Schema::dropIfExists('risk_trigger_klarifikasi_buktis');
    }
};
