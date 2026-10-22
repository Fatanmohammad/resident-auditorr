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
        Schema::create('register_triggers', function (Blueprint $table) {
            $table->id();

            // 1. Trigger ID (Format: RT-{YYYYMMDD}-{0001})
            $table->string('trigger_id', 35)->unique()->index();

            // 2. Sumber (Daily Review, Offsite, Onsite, Surprise, Monitoring TL, Pengaduan, Informasi Manajemen)
            $table->string('sumber', 50)->index();

            // 3. Source WP ID (ID referensi dari modul asal)
            $table->string('source_wp_id', 100)->nullable()->index();

            // 4. Tanggal Trigger
            $table->date('tanggal_trigger')->index();

            // 5 & 6. Kode Unit & Unit/Kantor
            $table->string('kode_unit', 20)->index();
            $table->string('nama_unit', 150)->nullable();

            // 7. Area (13 Area Standar SOP 06)
            $table->string('area', 50)->index();

            // 8. Objek/Key
            $table->string('objek_key', 255)->nullable();

            // 9 & 10. Periode Mulai & Periode Selesai
            $table->date('periode_mulai')->nullable();
            $table->date('periode_selesai')->nullable();

            // 11 & 12. Uraian Trigger & Bukti Awal
            $table->text('uraian_trigger')->nullable();
            $table->text('bukti_awal')->nullable();

            // 13 & 14. Risk Score Sumber & Risk Level Sumber
            $table->decimal('risk_score_sumber', 6, 2)->default(0);
            $table->string('risk_level_sumber', 20)->nullable();

            // 15. Jumlah Kejadian
            $table->integer('jumlah_kejadian')->default(1);

            // 16. Eksposur/Nominal (dalam Rupiah)
            $table->decimal('eksposur_nominal', 28, 2)->default(0);

            // 17. Perlu Verifikasi Lapangan (Ya / Tidak)
            $table->string('perlu_verifikasi_lapangan', 10)->default('Tidak');

            // 18. Indikasi Fraud (Ya / Tidak)
            $table->string('indikasi_fraud', 10)->default('Tidak');

            // 19. Objek Surprise/Fisik (Ya / Tidak)
            $table->string('objek_surprise_fisik', 10)->default('Tidak');

            // 20. Kelengkapan Data (VALID / BELUM LENGKAP)
            $table->string('kelengkapan_data', 25)->default('BELUM LENGKAP');

            // 21. Duplicate Key ({Kode_Unit}|{Area}|{Objek_Key}|{YYYYMMDD_Mulai}|{YYYYMMDD_Selesai})
            $table->string('duplicate_key', 255)->nullable()->index();

            // 22. Status Duplikat (UNIK / DUPLIKAT - REVIEW)
            $table->string('status_duplikat', 30)->default('UNIK');

            // 23 & 24. Likelihood (1..5) & Impact (1..5)
            $table->integer('likelihood')->default(1);
            $table->integer('impact')->default(1);

            // 25 & 26. Risk Score Reassessment & Risk Level Reassessment
            $table->decimal('risk_score_reassessment', 6, 2)->default(0);
            $table->string('risk_level_reassessment', 20)->default('RENDAH');

            // 27. Keputusan Otomatis
            $table->string('keputusan_otomatis', 50)->nullable()->index();

            // 28 & 29. Override Keputusan & Alasan Override
            $table->string('override_keputusan', 50)->nullable();
            $table->text('alasan_override')->nullable();

            // 30. Keputusan Final
            $table->string('keputusan_final', 50)->nullable()->index();

            // 31. SLA / Deadline (Hari Kerja sejak Tanggal Trigger)
            $table->date('sla_deadline')->nullable();

            // 32. Status Workflow
            $table->string('status_workflow', 50)->default('MENUNGGU ASSIGNMENT')->index();

            // 33. KKA Tujuan
            $table->string('kka_tujuan', 50)->nullable();

            // 34. Prioritas Visit
            $table->string('prioritas_visit', 50)->default('n.a.');

            // 35, 36, 37. Assigned RA, Reviewer, Catatan Review
            $table->string('assigned_ra', 100)->nullable();
            $table->string('reviewer', 100)->nullable();
            $table->text('catatan_review')->nullable();

            // Audit metadata
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('register_triggers');
    }
};
