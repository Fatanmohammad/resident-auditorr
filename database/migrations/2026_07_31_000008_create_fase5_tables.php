<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Final Audit Plan — output akhir, computed view gabungan
        Schema::create('final_audit_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->integer('period');
            $table->string('risk_category')->nullable();
            $table->foreignId('primary_ra_id')->nullable()->constrained('ras')->onDelete('set null');
            $table->foreignId('backup_ra_id')->nullable()->constrained('ras')->onDelete('set null');
            $table->boolean('daily_offsite_active')->default(false);
            $table->string('onsite_frequency_label')->nullable();
            $table->integer('visits_per_year')->default(0);
            $table->boolean('is_resident_daily_review')->default(false);
            $table->boolean('risk_trigger_visit_required')->default(false);
            $table->enum('plan_status', ['Approved', 'Draft - Lengkapi Mapping RA'])->default('Draft - Lengkapi Mapping RA');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['unit_id', 'period']);
        });

        // Change Log — audit trail semua perubahan manual
        Schema::create('change_logs', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('sheet_area'); // modul yang diubah
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->text('change_description');
            $table->text('reason')->nullable();
            $table->string('approved_by')->nullable();
            $table->enum('status', ['Draft', 'Approved', 'Rejected', 'Implemented'])->default('Draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_logs');
        Schema::dropIfExists('final_audit_plans');
    }
};
