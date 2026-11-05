<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah Kolom Scoring & Checklist pada kka_findings (Offsite)
        Schema::table('kka_findings', function (Blueprint $table) {
            if (!Schema::hasColumn('kka_findings', 'critical_trigger')) {
                $table->string('critical_trigger', 10)->default('Tidak')->after('skor_kemungkinan');
            }
            if (!Schema::hasColumn('kka_findings', 'skor_final')) {
                $table->integer('skor_final')->default(1)->after('critical_trigger');
            }
            if (!Schema::hasColumn('kka_findings', 'risk_final')) {
                $table->string('risk_final', 50)->default('Low')->after('skor_final');
            }
            if (!Schema::hasColumn('kka_findings', 'klarifikasi_unit')) {
                $table->text('klarifikasi_unit')->nullable()->after('hasil_uji');
            }
            if (!Schema::hasColumn('kka_findings', 'file_bukti')) {
                $table->string('file_bukti', 255)->nullable()->after('perlu_onsite');
            }
        });

        // Ubah tipe kolom perlu_onsite agar fleksibel menyimpan 'Perlu' / 'Tidak Perlu'
        if (Schema::hasColumn('kka_findings', 'perlu_onsite')) {
            try {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE kka_findings MODIFY perlu_onsite VARCHAR(50) NULL");
            } catch (\Throwable $e) {
                // Abaikan jika driver DB tidak mendukung
            }
        }

        // Ubah tipe kolom risk_awal agar fleksibel
        if (Schema::hasColumn('kka_findings', 'risk_awal')) {
            try {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE kka_findings MODIFY risk_awal VARCHAR(50) NULL");
            } catch (\Throwable $e) {
                // Abaikan jika driver DB tidak mendukung
            }
        }

        // 2. Buat Tabel Jawaban Checklist untuk KKA Offsite
        if (!Schema::hasTable('offsite_kka_checklist_answers')) {
            Schema::create('offsite_kka_checklist_answers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('kka_finding_id');
                $table->unsignedBigInteger('checklist_id');
                $table->enum('jawaban', ['Ya', 'Tidak'])->default('Ya');
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->foreign('kka_finding_id', 'fk_offsite_chk_finding')
                      ->references('id')->on('kka_findings')->onDelete('cascade');
                $table->foreign('checklist_id', 'fk_offsite_chk_master')
                      ->references('id')->on('daily_register_checklists')->onDelete('cascade');

                $table->unique(['kka_finding_id', 'checklist_id'], 'offsite_finding_chk_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('offsite_kka_checklist_answers');

        Schema::table('kka_findings', function (Blueprint $table) {
            $cols = ['critical_trigger', 'skor_final', 'risk_final', 'klarifikasi_unit', 'file_bukti'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('kka_findings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

