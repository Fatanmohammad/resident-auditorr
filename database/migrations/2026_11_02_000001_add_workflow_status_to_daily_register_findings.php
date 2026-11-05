<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_register_findings', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_register_findings', 'review_status')) {
                $table->string('review_status', 30)->default('BELUM_DIUJI')->after('risk_final')->index();
            }
            if (!Schema::hasColumn('daily_register_findings', 'reviewer_note')) {
                $table->text('reviewer_note')->nullable()->after('review_status');
            }
            if (!Schema::hasColumn('daily_register_findings', 'reviewed_by')) {
                $table->string('reviewed_by', 150)->nullable()->after('reviewer_note');
            }
            if (!Schema::hasColumn('daily_register_findings', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
            if (!Schema::hasColumn('daily_register_findings', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('reviewed_at');
            }
        });

        // Sinkronisasi data lama agar konsisten
        try {
            DB::statement("UPDATE daily_register_findings SET review_status = 'SELESAI', reviewer_note = catatan_reviewer WHERE status_review = 'Approved'");
            DB::statement("UPDATE daily_register_findings SET review_status = 'PERLU_REVISI', reviewer_note = catatan_reviewer WHERE status_review = 'Revisi'");
            DB::statement("UPDATE daily_register_findings SET review_status = 'BELUM_DIUJI' WHERE review_status IS NULL");
        } catch (\Throwable $e) {
            // Ignore during environments where table not yet created
        }
    }

    public function down(): void
    {
        Schema::table('daily_register_findings', function (Blueprint $table) {
            $cols = ['review_status', 'reviewer_note', 'reviewed_by', 'reviewed_at', 'submitted_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('daily_register_findings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
