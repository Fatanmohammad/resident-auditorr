<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('physical_kkas', function (Blueprint $table) {
            // 1. EVALUATION BLOCK - OBSERVASI LINGKUNGAN
            if (!Schema::hasColumn('physical_kkas', 'obs_tgl_uji')) {
                $table->date('obs_tgl_uji')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'obs_penyebab_keterangan')) {
                $table->text('obs_penyebab_keterangan')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'obs_impact')) {
                $table->integer('obs_impact')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'obs_likelihood')) {
                $table->integer('obs_likelihood')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'obs_is_critical')) {
                $table->boolean('obs_is_critical')->default(false);
            }
            if (!Schema::hasColumn('physical_kkas', 'obs_risk_score')) {
                $table->integer('obs_risk_score')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'obs_risk_level')) {
                $table->string('obs_risk_level', 50)->nullable()->default('Low');
            }

            // 2. EVALUATION BLOCK - REKON STOK CEK / BG
            if (!Schema::hasColumn('physical_kkas', 'stok_tgl_uji')) {
                $table->date('stok_tgl_uji')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'stok_penyebab_keterangan')) {
                $table->text('stok_penyebab_keterangan')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'stok_impact')) {
                $table->integer('stok_impact')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'stok_likelihood')) {
                $table->integer('stok_likelihood')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'stok_is_critical')) {
                $table->boolean('stok_is_critical')->default(false);
            }
            if (!Schema::hasColumn('physical_kkas', 'stok_risk_score')) {
                $table->integer('stok_risk_score')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'stok_risk_level')) {
                $table->string('stok_risk_level', 50)->nullable()->default('Low');
            }

            // 3. EVALUATION BLOCK - REKON BARANG CETAKAN
            if (!Schema::hasColumn('physical_kkas', 'cetakan_tgl_uji')) {
                $table->date('cetakan_tgl_uji')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'cetakan_penyebab_keterangan')) {
                $table->text('cetakan_penyebab_keterangan')->nullable();
            }
            if (!Schema::hasColumn('physical_kkas', 'cetakan_impact')) {
                $table->integer('cetakan_impact')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'cetakan_likelihood')) {
                $table->integer('cetakan_likelihood')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'cetakan_is_critical')) {
                $table->boolean('cetakan_is_critical')->default(false);
            }
            if (!Schema::hasColumn('physical_kkas', 'cetakan_risk_score')) {
                $table->integer('cetakan_risk_score')->nullable()->default(1);
            }
            if (!Schema::hasColumn('physical_kkas', 'cetakan_risk_level')) {
                $table->string('cetakan_risk_level', 50)->nullable()->default('Low');
            }
        });
    }

    public function down(): void
    {
        Schema::table('physical_kkas', function (Blueprint $table) {
            $cols = [
                'obs_tgl_uji', 'obs_penyebab_keterangan', 'obs_impact', 'obs_likelihood', 'obs_is_critical', 'obs_risk_score', 'obs_risk_level',
                'stok_tgl_uji', 'stok_penyebab_keterangan', 'stok_impact', 'stok_likelihood', 'stok_is_critical', 'stok_risk_score', 'stok_risk_level',
                'cetakan_tgl_uji', 'cetakan_penyebab_keterangan', 'cetakan_impact', 'cetakan_likelihood', 'cetakan_is_critical', 'cetakan_risk_score', 'cetakan_risk_level',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('physical_kkas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
