<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_id')->constrained('audit_plans')->onDelete('cascade');
            $table->enum('jenis_monitoring', ['terstruktur', 'kinerja_ra', 'realisasi_audit_plan']);
            $table->integer('total_temuan')->default(0);
            $table->integer('total_tl_selesai')->default(0);
            $table->integer('total_tl_pending')->default(0);
            $table->text('catatan_monitoring')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_audits');
    }
};