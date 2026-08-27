<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scoring_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_plan_id')->constrained('audit_plans')->onDelete('cascade');
            $table->decimal('skor_parameter_kat', 5, 2)->default(0);
            $table->decimal('skor_tindak_lanjut', 5, 2)->default(0);
            $table->decimal('skor_akhir', 5, 2)->default(0);
            $table->string('peringkat_ra');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scoring_audits');
    }
};