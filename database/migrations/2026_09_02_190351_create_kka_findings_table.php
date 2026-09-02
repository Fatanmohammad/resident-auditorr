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
        Schema::create('kka_findings', function (Blueprint $table) {
            $table->id();
            
            // --- 1. SYSTEM GENERATED (Dari Eksekusi File CSV) ---
            $table->string('staging_id')->nullable();
            $table->date('tanggal_data');
            $table->string('kode_unit');
            $table->string('nama_unit')->nullable();
            $table->string('source_sheet'); // Misal: KKA_Teller_Kas, KKA_Kredit
            $table->decimal('nominal_terkait', 20, 2)->nullable();
            $table->enum('risk_awal', ['Moderate', 'High']);
            $table->string('jenis_exception_awal')->nullable();
            
            // --- 2. INPUT RA (Dropdown & Text) ---
            $table->text('bukti_referensi')->nullable();
            $table->text('hasil_uji')->nullable();
            $table->string('jenis_exception_ra')->nullable();
            $table->integer('skor_dampak')->nullable(); // Validasinya 1-5 di FormRequest
            $table->integer('skor_kemungkinan')->nullable(); // Validasinya 1-5 di FormRequest
            $table->enum('perlu_onsite', ['Ya', 'Tidak'])->nullable(); // Dropdown Excel
            $table->text('simpulan_ra')->nullable();
            $table->date('tanggal_ditemukan')->nullable();
            
            // --- 3. INPUT ADMIN/PIMSIE (Dropdown Kaku & Text) ---
            $table->enum('status_klarifikasi', ['Belum Selesai', 'Selesai'])->default('Belum Selesai');
            $table->enum('perluasan_sampel', ['Ya', 'Tidak'])->default('Tidak');
            $table->enum('keputusan_onsite', ['Ya', 'Tidak'])->nullable();
            $table->enum('keputusan_eskalasi', ['Ya', 'Tidak'])->nullable();
            $table->enum('status_review', ['Belum Direview', 'Revisi', 'Approved'])->default('Belum Direview');
            $table->text('catatan_reviewer')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kka_findings');
    }
};
