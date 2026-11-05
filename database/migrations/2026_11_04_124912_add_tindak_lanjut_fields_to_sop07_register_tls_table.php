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
        Schema::table('sop07_register_tls', function (Blueprint $table) {
            if (!Schema::hasColumn('sop07_register_tls', 'status_tindak_lanjut')) {
                $table->string('status_tindak_lanjut')->default('DALAM PROSES')->after('status_final');
            }
            if (!Schema::hasColumn('sop07_register_tls', 'bukti_terakhir')) {
                $table->string('bukti_terakhir')->nullable()->after('status_tindak_lanjut');
            }
            if (!Schema::hasColumn('sop07_register_tls', 'klarifikasi')) {
                $table->text('klarifikasi')->nullable()->after('bukti_terakhir');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sop07_register_tls', function (Blueprint $table) {
            $table->dropColumn(['status_tindak_lanjut', 'bukti_terakhir', 'klarifikasi']);
        });
    }
};
