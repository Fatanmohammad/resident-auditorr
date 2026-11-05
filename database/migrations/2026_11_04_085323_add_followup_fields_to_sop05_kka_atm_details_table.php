<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sop05_kka_atm_details', function (Blueprint $table) {
            if (!Schema::hasColumn('sop05_kka_atm_details', 'status_tindak_lanjut')) {
                $table->string('status_tindak_lanjut')->default('SUDAH SELESAI')->after('exception_aktif');
            }
            if (!Schema::hasColumn('sop05_kka_atm_details', 'komitmen_tanggal')) {
                $table->date('komitmen_tanggal')->nullable()->after('status_tindak_lanjut');
            }
            if (!Schema::hasColumn('sop05_kka_atm_details', 'file_bukti')) {
                $table->string('file_bukti')->nullable()->after('komitmen_tanggal');
            }
            if (!Schema::hasColumn('sop05_kka_atm_details', 'klarifikasi')) {
                $table->text('klarifikasi')->nullable()->after('file_bukti');
            }
            if (!Schema::hasColumn('sop05_kka_atm_details', 'minta_bukti_cabang')) {
                $table->boolean('minta_bukti_cabang')->default(false)->after('klarifikasi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sop05_kka_atm_details', function (Blueprint $table) {
            $table->dropColumn([
                'status_tindak_lanjut',
                'komitmen_tanggal',
                'file_bukti',
                'klarifikasi',
                'minta_bukti_cabang'
            ]);
        });
    }
};
