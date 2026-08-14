<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Header WP Offsite Review — satu WP per unit per periode
        Schema::create('wp_offsite', function (Blueprint $table) {
            $table->id();
            $table->string('kode_wp')->unique();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('ra_id')->nullable()->constrained('ras')->onDelete('set null');
            $table->string('periode_data');           // contoh: "Juli 2027"
            $table->integer('tahun');
            $table->integer('bulan');                 // 1-12
            $table->enum('status_wp', ['Draft', 'In Review', 'Final', 'Approved'])->default('Draft');
            $table->string('reviewer')->nullable();
            $table->boolean('validasi_unit')->default(false);
            $table->timestamps();
        });

        // Staging DUMP CBS — 1 baris per transaksi/record mentah dari 5 DUMP
        Schema::create('offsite_staging', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wp_id')->constrained('wp_offsite')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->enum('dump_source', ['DUMP_01', 'DUMP_02', 'DUMP_03', 'DUMP_04', 'DUMP_05']);
            $table->enum('area_review', [
                'Teller/Kas', 'Biaya/Internal', 'Kredit',
                'Transaksi Umum', 'Transfer/KU', 'Pengaduan'
            ]);
            $table->string('no_transaksi')->nullable();
            $table->date('tanggal_transaksi')->nullable();
            $table->decimal('nominal', 20, 2)->nullable();
            $table->string('keterangan')->nullable();
            $table->string('unit_asal')->nullable();   // untuk deteksi salah unit
            // Flag rekonsiliasi
            $table->boolean('is_normalized')->default(false);
            $table->boolean('is_eligible')->default(false);
            $table->boolean('is_salah_unit')->default(false);
            $table->boolean('is_luar_periode')->default(false);
            $table->timestamps();
        });

        // KKA Final — 1 baris per item yang sudah direviu
        Schema::create('offsite_kka', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wp_id')->constrained('wp_offsite')->onDelete('cascade');
            $table->foreignId('staging_id')->nullable()->constrained('offsite_staging')->onDelete('set null');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->enum('area_review', [
                'Teller/Kas', 'Biaya/Internal', 'Kredit',
                'Transaksi Umum', 'Transfer/KU', 'Pengaduan'
            ]);
            $table->enum('dump_source', ['DUMP_01', 'DUMP_02', 'DUMP_03', 'DUMP_04', 'DUMP_05']);
            // Hasil penilaian risiko per item
            $table->enum('risk_level', ['High', 'Moderate to High', 'Moderate', 'Low to Moderate', 'Low'])->nullable();
            $table->enum('status_kka', [
                'Normal', 'Exception', 'Klarifikasi', 'Eskalasi'
            ])->default('Normal');
            // Eskalasi terjadi jika risk_level berubah naik dari initial (rule engine)
            $table->enum('initial_risk_level', ['High', 'Moderate to High', 'Moderate', 'Low to Moderate', 'Low'])->nullable();
            $table->boolean('is_escalated')->default(false);  // Low→Moderate atau Moderate→High
            $table->text('catatan')->nullable();
            $table->string('kka_sheet')->nullable();          // nama sheet KKA (untuk distribusi)
            $table->enum('kka_status', ['Draft', 'Final', 'Approved'])->default('Draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offsite_kka');
        Schema::dropIfExists('offsite_staging');
        Schema::dropIfExists('wp_offsite');
    }
};
