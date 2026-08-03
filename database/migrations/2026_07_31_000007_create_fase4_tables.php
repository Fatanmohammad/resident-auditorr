<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Onsite Frequency — computed + override manual
        Schema::create('onsite_frequencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->integer('period');
            $table->string('auto_frequency_label')->nullable();
            $table->integer('auto_visits_per_year')->default(0);
            $table->enum('manual_override_frequency', ['Bulanan','Triwulanan','Semesteran','Tahunan','Tidak Terjadwal'])->nullable();
            $table->string('final_frequency_label')->nullable();
            $table->integer('final_visits_per_year')->default(0);
            $table->boolean('is_resident_daily_review')->default(false);
            $table->string('basis_note')->nullable();
            $table->integer('cumulative_visits_running_total')->default(0);
            $table->integer('visit_sequence_start')->default(0);
            $table->timestamps();
            $table->unique(['unit_id', 'period']);
        });

        // Scheduled Visits — 1 baris per kunjungan (unrolled dari onsite_frequency)
        Schema::create('scheduled_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->integer('period');
            $table->integer('visit_number'); // ke-1, ke-2, dst
            $table->integer('recommended_month')->nullable(); // 1-12
            $table->integer('default_duration_days')->default(0);
            $table->date('auto_start_date')->nullable();
            $table->date('auto_end_date')->nullable();
            $table->date('manual_override_start')->nullable();
            $table->date('manual_override_end')->nullable();
            $table->date('final_start_date')->nullable();
            $table->date('final_end_date')->nullable();
            $table->integer('final_duration_days')->default(0);
            $table->enum('status', ['Planned','In Progress','Completed','Postponed','Cancelled'])->default('Planned');
            $table->string('basis_note')->nullable();
            $table->text('manual_notes')->nullable();
            $table->timestamps();
            $table->unique(['unit_id', 'period', 'visit_number']);
        });

        // RA Capacity — computed, 1 baris per RA per bulan per tahun
        Schema::create('ra_capacities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ra_id')->constrained('ras')->onDelete('cascade');
            $table->integer('period');
            $table->integer('month'); // 1-12
            $table->integer('effective_working_days')->default(20);
            $table->integer('daily_offsite_unit_count')->default(0);
            $table->decimal('estimated_offsite_days', 6, 2)->default(0);
            $table->integer('scheduled_visit_count')->default(0);
            $table->decimal('scheduled_visit_days', 6, 2)->default(0);
            $table->decimal('total_workload_days', 6, 2)->default(0);
            $table->decimal('utilization', 5, 4)->default(0); // 0.0000 - 9.9999
            $table->enum('capacity_status', ['OK', 'Warning', 'Over Capacity'])->default('OK');
            $table->text('recommendation_note')->nullable();
            $table->timestamps();
            $table->unique(['ra_id', 'period', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ra_capacities');
        Schema::dropIfExists('scheduled_visits');
        Schema::dropIfExists('onsite_frequencies');
    }
};
