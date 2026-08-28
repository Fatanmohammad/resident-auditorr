<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Daftar semua nama tabel KKA area di database.
     */
    protected array $kkaTables = [
        'kka_teller_kas',
        'kka_kredit',
        'kka_biaya_beban',
        'kka_biaya_internal',
        'kka_pengaduan',
        'kka_transaksi_umum',
        'kka_transfer_ku',
    ];

    public function up(): void
    {
        foreach ($this->kkaTables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'staging_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('staging_id')
                          ->nullable()
                          ->after('wp_offsite_id')
                          ->constrained('wp_offsite_stagings') // Sesuaikan nama tabel staging kamu jika berbeda (misal: 'staging_offsites')
                          ->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->kkaTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'staging_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['staging_id']);
                    $table->dropColumn('staging_id');
                });
            }
        }
    }
};