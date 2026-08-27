<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['kka_transfer_ku', 'kka_transaksi_umum'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'no_referensi')) {
                        $table->string('no_referensi')->nullable()->after('case_id');
                    }
                    if (!Schema::hasColumn($tableName, 'user_maker')) {
                        $table->string('user_maker')->nullable()->after('kode_unit');
                    }
                    if (!Schema::hasColumn($tableName, 'kode_transaksi')) {
                        $table->string('kode_transaksi')->nullable()->after('user_maker');
                    }
                    if (!Schema::hasColumn($tableName, 'data_source')) {
                        $table->string('data_source')->default('cbs_transactions')->after('deskripsi_narasi');
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['kka_transfer_ku', 'kka_transaksi_umum'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropColumn(['no_referensi', 'user_maker', 'kode_transaksi', 'data_source']);
                });
            }
        }
    }
};