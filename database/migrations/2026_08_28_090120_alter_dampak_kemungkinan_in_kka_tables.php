<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel-tabel KKA yang perlu diubah tipe kolomnya.
     */
    protected array $tables = [
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
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                // Mengubah tipe kolom menjadi string/text (nullable)
                $table->string('dampak')->nullable()->change();
                $table->string('kemungkinan')->nullable()->change();
                $table->string('status_review', 255)->nullable()->change();
                $table->text('hasil_uji')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->integer('dampak')->nullable()->change();
                $table->integer('kemungkinan')->nullable()->change();
                $table->string('status_review', 50)->change();
            });
        }
    }
};