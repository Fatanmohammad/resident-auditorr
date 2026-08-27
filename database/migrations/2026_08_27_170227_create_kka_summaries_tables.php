<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel History Rangkuman Harian
        Schema::create('kka_daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('sheet_name');
            $table->integer('total_exception')->default(0);
            $table->decimal('total_nominal', 18, 2)->default(0);
            $table->integer('high_risk_count')->default(0);
            $table->integer('moderate_risk_count')->default(0);
            $table->integer('low_risk_count')->default(0);
            $table->timestamps();

            $table->unique(['tanggal', 'sheet_name']);
        });

        // Tabel History Rangkuman Bulanan
        Schema::create('kka_monthly_summaries', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun');
            $table->integer('bulan');
            $table->string('sheet_name');
            $table->integer('total_exception')->default(0);
            $table->decimal('total_nominal', 18, 2)->default(0);
            $table->integer('high_risk_count')->default(0);
            $table->timestamps();

            $table->unique(['tahun', 'bulan', 'sheet_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kka_monthly_summaries');
        Schema::dropIfExists('kka_daily_summaries');
    }
};