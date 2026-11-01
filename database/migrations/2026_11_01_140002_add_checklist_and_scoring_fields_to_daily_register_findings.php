<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_register_findings', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_register_findings', 'critical_trigger')) {
                $table->string('critical_trigger', 10)->default('Tidak')->after('skor_kemungkinan');
            }
            if (!Schema::hasColumn('daily_register_findings', 'skor_final')) {
                $table->integer('skor_final')->default(1)->after('critical_trigger');
            }
            if (!Schema::hasColumn('daily_register_findings', 'risk_final')) {
                $table->string('risk_final', 50)->default('Low')->after('skor_final');
            }
            if (!Schema::hasColumn('daily_register_findings', 'klarifikasi_unit')) {
                $table->text('klarifikasi_unit')->nullable()->after('hasil_uji');
            }
            if (!Schema::hasColumn('daily_register_findings', 'file_bukti')) {
                $table->string('file_bukti', 255)->nullable()->after('perlu_onsite');
            }
        });

        // Modify perlu_onsite to string so it can store 'Perlu', 'Tidak Perlu', '0', '1', 'Ya', 'Tidak'
        if (Schema::hasColumn('daily_register_findings', 'perlu_onsite')) {
            try {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE daily_register_findings MODIFY perlu_onsite VARCHAR(50) NULL");
            } catch (\Throwable $e) {
                // Ignore if DB driver doesn't support or already string
            }
        }

        // Modify risk_awal to string so it can store 'Critical', 'Moderate to High', etc.
        if (Schema::hasColumn('daily_register_findings', 'risk_awal')) {
            try {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE daily_register_findings MODIFY risk_awal VARCHAR(50) NULL");
            } catch (\Throwable $e) {
                // Ignore if DB driver doesn't support or already string
            }
        }
    }

    public function down(): void
    {
        Schema::table('daily_register_findings', function (Blueprint $table) {
            $cols = ['critical_trigger', 'skor_final', 'risk_final', 'klarifikasi_unit', 'file_bukti'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('daily_register_findings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
