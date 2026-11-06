<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('physical_kkas', function (Blueprint $table) {
            // 1. EVALUATION BLOCK - CASH OPNAME KAS KHASANAH
            if (!Schema::hasColumn('physical_kkas', 'kas_tgl_uji')) {
                $table->date('kas_tgl_uji')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'kas_penyebab_keterangan')) {
                $table->text('kas_penyebab_keterangan')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'kas_impact')) {
                $table->integer('kas_impact')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'kas_likelihood')) {
                $table->integer('kas_likelihood')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'kas_is_critical')) {
                $table->boolean('kas_is_critical')->default(false);
            }
            if (!Schema::hasColumn('physical_kkas', 'kas_risk_score')) {
                $table->integer('kas_risk_score')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'kas_risk_level')) {
                $table->string('kas_risk_level', 50)->nullable()->default('Low');
            }

            // 2. EVALUATION BLOCK - CASH OPNAME MESIN ATM
            if (!Schema::hasColumn('physical_kkas', 'atm_tgl_uji')) {
                $table->date('atm_tgl_uji')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'atm_penyebab_keterangan')) {
                $table->text('atm_penyebab_keterangan')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'atm_impact')) {
                $table->integer('atm_impact')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'atm_likelihood')) {
                $table->integer('atm_likelihood')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'atm_is_critical')) {
                $table->boolean('atm_is_critical')->default(false);
            }
            if (!Schema::hasColumn('physical_kkas', 'atm_risk_score')) {
                $table->integer('atm_risk_score')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'atm_risk_level')) {
                $table->string('atm_risk_level', 50)->nullable()->default('Low');
            }

            // 3. EVALUATION BLOCK - REKONSILIASI KARTU ATM
            if (!Schema::hasColumn('physical_kkas', 'kartu_tgl_uji')) {
                $table->date('kartu_tgl_uji')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'kartu_penyebab_keterangan')) {
                $table->text('kartu_penyebab_keterangan')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'kartu_impact')) {
                $table->integer('kartu_impact')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'kartu_likelihood')) {
                $table->integer('kartu_likelihood')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'kartu_is_critical')) {
                $table->boolean('kartu_is_critical')->default(false);
            }
            if (!Schema::hasColumn('physical_kkas', 'kartu_risk_score')) {
                $table->integer('kartu_risk_score')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'kartu_risk_level')) {
                $table->string('kartu_risk_level', 50)->nullable()->default('Low');
            }

            // 4. GENERIC / FALLBACK COLUMNS (IF NOT PRESENT)
            if (!Schema::hasColumn('physical_kkas', 'tgl_uji')) {
                $table->date('tgl_uji')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'penyebab_keterangan')) {
                $table->text('penyebab_keterangan')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'impact')) {
                $table->integer('impact')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'likelihood')) {
                $table->integer('likelihood')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'is_critical')) {
                $table->boolean('is_critical')->default(false);
            }
            if (!Schema::hasColumn('physical_kkas', 'risk_score')) {
                $table->integer('risk_score')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'risk_level')) {
                $table->string('risk_level', 50)->nullable()->default('Low');
            }
        });
    }

    public function down(): void
    {
        Schema::table('physical_kkas', function (Blueprint $table) {
            $cols = [
                'kas_tgl_uji', 'kas_penyebab_keterangan', 'kas_impact', 'kas_likelihood', 'kas_is_critical', 'kas_risk_score', 'kas_risk_level',
                'atm_tgl_uji', 'atm_penyebab_keterangan', 'atm_impact', 'atm_likelihood', 'atm_is_critical', 'atm_risk_score', 'atm_risk_level',
                'kartu_tgl_uji', 'kartu_penyebab_keterangan', 'kartu_impact', 'kartu_likelihood', 'kartu_is_critical', 'kartu_risk_score', 'kartu_risk_level',
                'tgl_uji', 'penyebab_keterangan', 'impact', 'likelihood', 'is_critical', 'risk_score', 'risk_level',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('physical_kkas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
