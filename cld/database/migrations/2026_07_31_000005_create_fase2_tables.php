<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Raw Metrics — 26 indikator mentah per unit per periode
        Schema::create('raw_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->integer('period'); // tahun penilaian

            // Bidang A - Riwayat RA
            $table->integer('prior_onsite_findings')->default(0);
            $table->integer('significant_findings')->default(0);
            $table->integer('repeat_findings')->default(0);
            $table->integer('offsite_deviation')->default(0);
            $table->integer('offsite_deviation_significant')->default(0);
            $table->integer('offsite_deviation_repeat')->default(0);
            $table->integer('months_since_last_onsite')->default(0);

            // Bidang B - Kas/Teller
            $table->integer('reversal_correction_txn')->default(0);
            $table->integer('cash_discrepancy')->default(0);
            $table->integer('unusual_cost_journal')->default(0);
            $table->integer('large_risky_cash_txn')->default(0);

            // Bidang C - CS/DPK
            $table->integer('dpk_anomaly')->default(0);
            $table->integer('overdue_complaints')->default(0);
            $table->integer('incomplete_cdd_edd')->default(0);

            // Bidang D - Kredit
            $table->integer('debtors_col_3_5')->default(0);
            $table->decimal('npl_ratio', 5, 4)->default(0); // 0.0000 - 1.0000
            $table->integer('credit_deviation')->default(0);

            // Bidang E - TI/ATM
            $table->integer('atm_dispute')->default(0);
            $table->decimal('atm_downtime_hours', 8, 2)->default(0);
            $table->integer('critical_ti_incident')->default(0);
            $table->integer('unusual_user_reset')->default(0);

            // Bidang F - Monitoring TL
            $table->integer('ra_onsite_tl_overdue')->default(0);
            $table->integer('ra_offsite_tl_overdue')->default(0);
            $table->integer('skai_tl_overdue')->default(0);
            $table->integer('regulator_tl_overdue')->default(0);
            $table->integer('kap_tl_overdue')->default(0);
            $table->decimal('avg_response_days', 8, 2)->default(0);
            $table->integer('tl_response_quality')->default(0); // 0-4 checklist poin

            $table->foreignId('input_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['unit_id', 'period']);
        });

        // Risk Component Scores — 6 skor bidang hasil computed
        Schema::create('risk_component_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->integer('period');

            $table->decimal('skor_riwayat_ra', 6, 2)->default(0);
            $table->decimal('skor_kas_teller', 6, 2)->default(0);
            $table->decimal('skor_cs_dpk', 6, 2)->default(0);
            $table->decimal('skor_kredit', 6, 2)->default(0);
            $table->decimal('skor_ti_atm', 6, 2)->default(0);
            $table->decimal('skor_monitoring_tl', 6, 2)->default(0);

            $table->timestamps();
            $table->unique(['unit_id', 'period']);
        });

        // Risk Scoring — skor final + kategori
        Schema::create('risk_scorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->integer('period');

            $table->decimal('weighted_score', 6, 2)->default(0);
            $table->enum('initial_category', ['Low', 'Low to Moderate', 'Moderate', 'Moderate to High', 'High'])->nullable();
            $table->boolean('has_active_override')->default(false);
            $table->enum('final_category', ['Low', 'Low to Moderate', 'Moderate', 'Moderate to High', 'High'])->nullable();
            $table->text('override_reason')->nullable();
            $table->integer('priority_rank')->nullable(); // 1=High ... 5=Low

            $table->timestamps();
            $table->unique(['unit_id', 'period']);
        });

        // Critical Overrides — override darurat paksa High
        Schema::create('critical_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->date('trigger_date');
            $table->enum('trigger_type', [
                'Fraud Indicator',
                'Selisih Kas Material',
                'Dokumen/Agunan Hilang',
                'User Sistem Tidak Sah',
                'Transaksi Tanpa Otorisasi',
                'TL High/Critical Overdue',
                'Penolakan Data RA',
                'Repeat Finding Critical',
            ]);
            $table->text('trigger_description')->nullable();
            $table->enum('status', ['Aktif', 'Tidak Aktif', 'Selesai'])->default('Aktif');
            $table->string('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('critical_overrides');
        Schema::dropIfExists('risk_scorings');
        Schema::dropIfExists('risk_component_scores');
        Schema::dropIfExists('raw_metrics');
    }
};
