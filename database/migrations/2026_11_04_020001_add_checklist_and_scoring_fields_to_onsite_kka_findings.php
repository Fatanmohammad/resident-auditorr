<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('onsite_kka_findings', function (Blueprint $table) {
            if (!Schema::hasColumn('onsite_kka_findings', 'critical_trigger')) {
                $table->enum('critical_trigger', ['Tidak', 'Ya'])->default('Tidak')->after('skor_kemungkinan');
            }
            if (!Schema::hasColumn('onsite_kka_findings', 'skor_final')) {
                $table->integer('skor_final')->default(1)->after('critical_trigger');
            }
            if (!Schema::hasColumn('onsite_kka_findings', 'risk_final')) {
                $table->string('risk_final', 50)->default('Low')->after('skor_final');
            }
            if (!Schema::hasColumn('onsite_kka_findings', 'jenis_exception_ra')) {
                $table->string('jenis_exception_ra')->nullable()->after('bukti_referensi');
            }
            if (!Schema::hasColumn('onsite_kka_findings', 'klarifikasi_unit')) {
                $table->text('klarifikasi_unit')->nullable()->after('hasil_uji');
            }
            if (!Schema::hasColumn('onsite_kka_findings', 'status_klarifikasi')) {
                $table->string('status_klarifikasi', 100)->nullable()->after('status_review');
            }
            if (!Schema::hasColumn('onsite_kka_findings', 'keputusan_eskalasi')) {
                $table->string('keputusan_eskalasi', 100)->nullable()->after('status_klarifikasi');
            }
            if (!Schema::hasColumn('onsite_kka_findings', 'file_bukti')) {
                $table->string('file_bukti')->nullable()->after('tanggal_ditemukan');
            }
        });

        // Ubah kolom risk_level menjadi VARCHAR(50) agar bisa menampung 'Critical', 'Moderate to High', dll
        try {
            DB::statement("ALTER TABLE onsite_kka_findings MODIFY risk_level VARCHAR(50) DEFAULT 'Low'");
        } catch (\Throwable $e) {
            // Ignore if already modified or sqlite
        }

        // Tabel Checklist Answers untuk KKA Onsite
        if (!Schema::hasTable('onsite_kka_checklist_answers')) {
            Schema::create('onsite_kka_checklist_answers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('onsite_kka_finding_id');
                $table->unsignedBigInteger('checklist_id');
                $table->enum('jawaban', ['Ya', 'Tidak'])->default('Ya');
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->foreign('onsite_kka_finding_id')
                    ->references('id')
                    ->on('onsite_kka_findings')
                    ->onDelete('cascade');

                $table->foreign('checklist_id')
                    ->references('id')
                    ->on('daily_register_checklists')
                    ->onDelete('cascade');

                $table->unique(['onsite_kka_finding_id', 'checklist_id'], 'onsite_kka_chk_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onsite_kka_checklist_answers');

        Schema::table('onsite_kka_findings', function (Blueprint $table) {
            $cols = [
                'critical_trigger', 'skor_final', 'risk_final',
                'jenis_exception_ra', 'klarifikasi_unit', 'status_klarifikasi',
                'keputusan_eskalasi', 'file_bukti'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('onsite_kka_findings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

