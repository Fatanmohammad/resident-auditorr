<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('daily_register_findings')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE `daily_register_findings` MODIFY `risk_final` VARCHAR(50) NULL DEFAULT NULL");
            } else {
                Schema::table('daily_register_findings', function (Blueprint $table) {
                    $table->string('risk_final', 50)->nullable()->default(null)->change();
                });
            }

            // Bersihkan data yang belum diuji: jika status masih BELUM_DIUJI dan belum ada hasil uji,
            // maka risk_final harus null (sehingga menggunakan risk_awal dari engine)
            DB::table('daily_register_findings')
                ->where(function($q) {
                    $q->where('review_status', 'BELUM_DIUJI')
                      ->orWhereNull('review_status');
                })
                ->whereNull('hasil_uji')
                ->where('risk_final', 'Low')
                ->update(['risk_final' => null]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('daily_register_findings')) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE `daily_register_findings` MODIFY `risk_final` VARCHAR(50) NOT NULL DEFAULT 'Low'");
            } else {
                Schema::table('daily_register_findings', function (Blueprint $table) {
                    $table->string('risk_final', 50)->default('Low')->change();
                });
            }
        }
    }
};

