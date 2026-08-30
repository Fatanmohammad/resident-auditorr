<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'kka_teller_kas', 'kka_kredit', 'kka_biaya_beban',
            'kka_biaya_internal', 'kka_pengaduan', 'kka_transaksi_umum', 'kka_transfer_ku'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->string('status_review', 255)->nullable()->change();
                    $table->string('dampak', 255)->nullable()->change();
                    $table->string('kemungkinan', 255)->nullable()->change();
                    $table->text('hasil_uji')->nullable()->change();
                });
            }
        }
    }

    public function down(): void {}
};