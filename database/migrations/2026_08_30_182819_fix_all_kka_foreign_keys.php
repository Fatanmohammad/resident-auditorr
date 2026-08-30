<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tables = [
        'kka_biaya_beban',
        'kka_biaya_internal',
        'kka_teller_kas',
        'kka_kredit',
        'kka_pengaduan',
        'kka_transaksi_umum',
        'kka_transfer_ku',
    ];

    public function up(): void
    {
        $dbName = DB::getDatabaseName();

        foreach ($this->tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            // Cari semua nama foreign key pada kolom staging_id di tabel ini
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = ? 
                  AND TABLE_NAME = ? 
                  AND COLUMN_NAME = 'staging_id' 
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$dbName, $tableName]);

            Schema::table($tableName, function (Blueprint $table) use ($foreignKeys, $tableName) {
                // Hapus foreign key yang ditemukan
                foreach ($foreignKeys as $fk) {
                    $table->dropForeign($fk->CONSTRAINT_NAME);
                }

                // Tambahkan foreign key baru yang mengarah ke staging_offsite
                $table->foreign('staging_id')
                      ->references('id')
                      ->on('staging_offsite')
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    try {
                        $table->dropForeign(['staging_id']);
                    } catch (\Exception $e) {}
                });
            }
        }
    }
};