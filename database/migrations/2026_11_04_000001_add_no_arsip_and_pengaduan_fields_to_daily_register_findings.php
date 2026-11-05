<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_register_findings', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_register_findings', 'no_arsip')) {
                $table->string('no_arsip', 100)->nullable()->after('staging_id')->index();
            }
            if (!Schema::hasColumn('daily_register_findings', 'tgl_keluhan')) {
                $table->date('tgl_keluhan')->nullable()->after('tanggal_ditemukan');
            }
            if (!Schema::hasColumn('daily_register_findings', 'tgl_buku')) {
                $table->date('tgl_buku')->nullable()->after('tgl_keluhan');
            }
            if (!Schema::hasColumn('daily_register_findings', 'status_keluhan')) {
                $table->string('status_keluhan', 50)->nullable()->after('tgl_buku');
            }
            if (!Schema::hasColumn('daily_register_findings', 'lama_hari_kerja')) {
                $table->string('lama_hari_kerja', 50)->nullable()->after('status_keluhan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_register_findings', function (Blueprint $table) {
            $cols = ['no_arsip', 'tgl_keluhan', 'tgl_buku', 'status_keluhan', 'lama_hari_kerja'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('daily_register_findings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
