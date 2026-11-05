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
        // 1. surprise_audit_assignments
        Schema::table('surprise_audit_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('surprise_audit_assignments', 'status_review_admin')) {
                $table->string('status_review_admin')->nullable()->after('status_assignment'); // DISETUJUI, PERLU PERBAIKAN
            }
            if (!Schema::hasColumn('surprise_audit_assignments', 'catatan_review_admin')) {
                $table->text('catatan_review_admin')->nullable()->after('status_review_admin');
            }
            if (!Schema::hasColumn('surprise_audit_assignments', 'sample_data_file')) {
                $table->string('sample_data_file')->nullable()->after('catatan_review_admin');
            }
            if (!Schema::hasColumn('surprise_audit_assignments', 'nomor_surat_tugas')) {
                $table->string('nomor_surat_tugas')->nullable()->after('nomor_assignment');
            }
            if (!Schema::hasColumn('surprise_audit_assignments', 'tanggal_hari')) {
                $table->string('tanggal_hari')->nullable()->after('tanggal_rencana'); // e.g. Selasa
            }
            if (!Schema::hasColumn('surprise_audit_assignments', 'jam_mulai_opname')) {
                $table->string('jam_mulai_opname')->nullable()->default('16.30 WITA');
            }
            if (!Schema::hasColumn('surprise_audit_assignments', 'jam_selesai_opname')) {
                $table->string('jam_selesai_opname')->nullable()->default('18.45 WITA');
            }
            if (!Schema::hasColumn('surprise_audit_assignments', 'kondisi_opname')) {
                $table->string('kondisi_opname')->nullable()->default('sebelum Kas Harian dilimpahkan Kepada Teller');
            }
            if (!Schema::hasColumn('surprise_audit_assignments', 'pemeriksa_1')) {
                $table->string('pemeriksa_1')->nullable();
            }
            if (!Schema::hasColumn('surprise_audit_assignments', 'pemeriksa_2')) {
                $table->string('pemeriksa_2')->nullable();
            }
            if (!Schema::hasColumn('surprise_audit_assignments', 'saksi_1')) {
                $table->string('saksi_1')->nullable();
            }
            if (!Schema::hasColumn('surprise_audit_assignments', 'saksi_2')) {
                $table->string('saksi_2')->nullable();
            }
            if (!Schema::hasColumn('surprise_audit_assignments', 'saksi_list')) {
                $table->text('saksi_list')->nullable();
            }
            if (!Schema::hasColumn('surprise_audit_assignments', 'pejabat_mengetahui')) {
                $table->string('pejabat_mengetahui')->nullable();
            }
            if (!Schema::hasColumn('surprise_audit_assignments', 'keterangan_selisih_kas')) {
                $table->text('keterangan_selisih_kas')->nullable();
            }
        });

        // 2. sop05_kka_kas_details
        Schema::table('sop05_kka_kas_details', function (Blueprint $table) {
            if (!Schema::hasColumn('sop05_kka_kas_details', 'ule_75k')) {
                $table->integer('ule_75k')->default(0)->after('ule_100k');
            }
            if (!Schema::hasColumn('sop05_kka_kas_details', 'utle_75k')) {
                $table->integer('utle_75k')->default(0)->after('utle_100k');
            }
            if (!Schema::hasColumn('sop05_kka_kas_details', 'logam_50')) {
                $table->integer('logam_50')->default(0)->after('logam_100');
            }
            if (!Schema::hasColumn('sop05_kka_kas_details', 'logam_25')) {
                $table->integer('logam_25')->default(0)->after('logam_50');
            }
            if (!Schema::hasColumn('sop05_kka_kas_details', 'status_tindak_lanjut')) {
                $table->string('status_tindak_lanjut')->default('SUDAH SELESAI'); // SUDAH SELESAI, DALAM PROSES, TIDAK BISA
            }
            if (!Schema::hasColumn('sop05_kka_kas_details', 'komitmen_tanggal')) {
                $table->date('komitmen_tanggal')->nullable();
            }
            if (!Schema::hasColumn('sop05_kka_kas_details', 'file_bukti')) {
                $table->string('file_bukti')->nullable();
            }
            if (!Schema::hasColumn('sop05_kka_kas_details', 'klarifikasi')) {
                $table->text('klarifikasi')->nullable();
            }
            if (!Schema::hasColumn('sop05_kka_kas_details', 'minta_bukti_cabang')) {
                $table->boolean('minta_bukti_cabang')->default(false);
            }
        });

        // 3. sop05_kka_kartu_details
        Schema::table('sop05_kka_kartu_details', function (Blueprint $table) {
            if (!Schema::hasColumn('sop05_kka_kartu_details', 'cardman_qty')) {
                $table->integer('cardman_qty')->default(0);
            }
            if (!Schema::hasColumn('sop05_kka_kartu_details', 'cardman_nominal')) {
                $table->decimal('cardman_nominal', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('sop05_kka_kartu_details', 'saldo_persediaan_gl')) {
                $table->decimal('saldo_persediaan_gl', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('sop05_kka_kartu_details', 'posisi_rekonsiliasi')) {
                $table->date('posisi_rekonsiliasi')->nullable();
            }
            if (!Schema::hasColumn('sop05_kka_kartu_details', 'periode_audit')) {
                $table->string('periode_audit')->nullable();
            }
            if (!Schema::hasColumn('sop05_kka_kartu_details', 'periode_data')) {
                $table->string('periode_data')->nullable();
            }
            if (!Schema::hasColumn('sop05_kka_kartu_details', 'findings_tabel1')) {
                $table->text('findings_tabel1')->nullable();
            }
            if (!Schema::hasColumn('sop05_kka_kartu_details', 'findings_tabel2')) {
                $table->text('findings_tabel2')->nullable();
            }
            if (!Schema::hasColumn('sop05_kka_kartu_details', 'status_tindak_lanjut')) {
                $table->string('status_tindak_lanjut')->default('SUDAH SELESAI'); // SUDAH SELESAI, DALAM PROSES, TIDAK BISA
            }
            if (!Schema::hasColumn('sop05_kka_kartu_details', 'komitmen_tanggal')) {
                $table->date('komitmen_tanggal')->nullable();
            }
            if (!Schema::hasColumn('sop05_kka_kartu_details', 'file_bukti')) {
                $table->string('file_bukti')->nullable();
            }
            if (!Schema::hasColumn('sop05_kka_kartu_details', 'klarifikasi')) {
                $table->text('klarifikasi')->nullable();
            }
            if (!Schema::hasColumn('sop05_kka_kartu_details', 'minta_bukti_cabang')) {
                $table->boolean('minta_bukti_cabang')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // down logic if needed
    }
};
