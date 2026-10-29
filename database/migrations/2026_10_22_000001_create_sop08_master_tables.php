<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sop08_master_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('kode_sop', 30)->default('SOP-RA-08');
            $table->string('versi_wp', 20)->default('v1');
            $table->date('tanggal_cutoff')->default('2026-10-14');
            $table->date('periode_dashboard')->default('2026-09-01');
            $table->integer('hari_kerja_jatuh_tempo')->default(5);
            $table->string('reviewer_default', 100)->default('Unit RA/Bagian RA');
            $table->string('unit_ra_default', 100)->default('Bagian Resident Auditor');
            $table->decimal('target_quality_score', 5, 2)->default(80.00);
            
            // Bobot Quality Score (Total 100)
            $table->integer('bobot_tepat_waktu')->default(20);
            $table->integer('bobot_kelengkapan')->default(20);
            $table->integer('bobot_akurasi')->default(15);
            $table->integer('bobot_analisis')->default(15);
            $table->integer('bobot_bukti')->default(10);
            $table->integer('bobot_tindak_lanjut')->default(10);
            $table->integer('bobot_eskalasi')->default(10);
            $table->decimal('ambang_high_attention', 5, 2)->default(60.00);

            // Parameter Kinerja RA
            $table->integer('bobot_konsistensi')->default(40);
            $table->integer('bobot_penyelesaian')->default(30);
            $table->integer('bobot_kualitas')->default(30);
            $table->decimal('ambang_baik_konsisten', 5, 2)->default(90.00);
            $table->decimal('ambang_memadai', 5, 2)->default(80.00);
            $table->decimal('ambang_perlu_peningkatan', 5, 2)->default(60.00);

            $table->timestamps();
        });

        Schema::create('sop08_daftar_hari_liburs', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('keterangan', 200);
            $table->integer('tahun')->default(2026);
            $table->timestamps();
        });

        Schema::create('sop08_master_units', function (Blueprint $table) {
            $table->id();
            $table->string('kode_unit', 10)->unique(); // 001, 002, dst
            $table->string('nama_unit', 150);
            $table->string('jenis_unit', 20)->default('KC'); // KC, KCP, KCPLK, KCU, KP
            $table->string('kantor_induk', 150)->nullable();
            $table->string('kode_kc_induk', 10)->nullable();
            $table->string('assigned_ra', 150)->nullable();
            $table->string('unit_ra', 150)->default('Bagian Resident Auditor');
            $table->foreignId('cabang_id')->nullable()->constrained('cabangs')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('user_ra_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop08_master_units');
        Schema::dropIfExists('sop08_daftar_hari_liburs');
        Schema::dropIfExists('sop08_master_parameters');
    }
};
