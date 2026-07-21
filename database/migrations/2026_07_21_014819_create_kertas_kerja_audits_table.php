<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kertas_kerja_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_id')->constrained('audit_plans')->onDelete('cascade');
            $table->string('bidang_audit');
            $table->string('sub_bidang')->nullable();
            $table->date('tanggal_pemeriksaan');
            $table->text('sample_pemeriksaan')->nullable();
            $table->enum('status_kka', ['draft', 'reviewed_kabag', 'approved_kadiv', 'revisi'])->default('draft');
            $table->text('catatan_kabag')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kertas_kerja_audits');
    }
};